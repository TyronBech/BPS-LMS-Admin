<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Enum\PermissionsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\user;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    /**
     * Get all roles with their permissions.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        Log::info('Roles & Permissions: Page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $perPage                = request('perPage', 10);
        $roles_with_permissions = Role::with('permissions')
            ->whereHas('permissions', function ($query) {
                $query->where('guard_name', 'admin')
                    ->where('name', '!=', PermissionsEnum::MODIFY_ADMIN->value)
                    ->where('name', '!=', PermissionsEnum::CREATE_BACKUPS->value)
                    ->where('name', '!=', PermissionsEnum::VIEW_AUDIT_REPORTS->value)
                    ->where('name', '!=', PermissionsEnum::MODIFY_UI_SETTINGS->value)
                    ->orderBy('name', 'asc');
            })
            ->get();
        $permissions            = Permission::where('guard_name', 'admin')->orderBy('name', 'asc')->paginate($perPage)->appends(['perPage' => $perPage]);
        $admins = User::all();
        return view('roles_permissions.roles', compact('roles_with_permissions', 'permissions', 'admins', 'perPage'));
    }
    /**
     * Get all permissions excluding Modify Admins, Create Backups, and View Audit Reports,
     * then render the roles_permissions.create view with the permissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Log::info('Roles & Permissions: Create Role page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $permissions = Permission::select('name')
            ->where('guard_name', 'admin')
            ->where('name', '!=', PermissionsEnum::MODIFY_ADMIN->value)
            ->where('name', '!=', PermissionsEnum::CREATE_BACKUPS->value)
            ->where('name', '!=', PermissionsEnum::VIEW_AUDIT_REPORTS->value)
            ->where('name', '!=', PermissionsEnum::MODIFY_UI_SETTINGS->value)
            ->orderBy('name', 'asc')
            ->get();
        return view('roles_permissions.create', compact('permissions'));
    }

    /**
     * Create a new role and assign permissions to it.
     *
     * The validation rules for this function are:
     * - The 'role' field must be a required string with a maximum length of 50 characters.
     * - The 'permissions' field must be an array.
     *
     * If the user selects restricted actions without their view, the function will redirect back to the create role page with a toast warning.
     *
     * If the role already exists, the function will redirect back to the create role page with a toast warning.
     *
     * If an exception occurs during the database transaction, the function will rollback the transaction and redirect back to the management page with a toast error.
     *
     * If the transaction is successful, the function will redirect back to the management page with a toast success.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        Log::info('Roles & Permissions: Attempting to create role', [
            'user_id' => Auth::guard('admin')->id(),
            'role_name' => $request->input('role'),
            'permissions_count' => count($request->input('permissions') ?? []),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $request->validate([
            'role' => 'required|string|max:50',
            'permissions' => 'array',
        ]);
        $permissions = $request->input('permissions');
        if ($permissions == null) {
            Log::warning('Roles & Permissions: Create role failed - No permissions selected', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return redirect()->route('maintenance.roles-and-permissions.create-role')->with('toast-warning', 'Please select at least one permission');
        }
        // Define the mapping between view and action permissions
        $restrictions = [
            PermissionsEnum::VIEW_USERS_MAINTENANCE->value            => [PermissionsEnum::ADD_USERS->value, PermissionsEnum::EDIT_USERS->value, PermissionsEnum::DELETE_USERS->value],
            PermissionsEnum::VIEW_BOOKS_MAINTENANCE->value            => [PermissionsEnum::ADD_BOOKS->value, PermissionsEnum::EDIT_BOOKS->value, PermissionsEnum::DELETE_BOOKS->value],
            PermissionsEnum::VIEW_SUBJECTS_MAINTENANCE->value         => [PermissionsEnum::ADD_SUBJECTS->value, PermissionsEnum::EDIT_SUBJECTS->value, PermissionsEnum::DELETE_SUBJECTS->value],
            PermissionsEnum::VIEW_BOOK_CATEGORIES_MAINTENANCE->value  => [PermissionsEnum::ADD_CATEGORIES->value, PermissionsEnum::EDIT_CATEGORIES->value, PermissionsEnum::DELETE_CATEGORIES->value],
            PermissionsEnum::VIEW_PRIVILEGES_MAINTENANCE->value       => [PermissionsEnum::ADD_PRIVILEGES->value, PermissionsEnum::EDIT_PRIVILEGES->value, PermissionsEnum::DELETE_PRIVILEGES->value],
            PermissionsEnum::VIEW_PENALTY_RULES_MAINTENANCE->value    => [PermissionsEnum::ADD_PENALTY_RULES->value, PermissionsEnum::EDIT_PENALTY_RULES->value, PermissionsEnum::DELETE_PENALTY_RULES->value],
            PermissionsEnum::VIEW_TRANSACTIONS_MAINTENANCE->value     => [PermissionsEnum::EDIT_TRANSACTIONS->value],
            PermissionsEnum::VIEW_ANNOUNCEMENTS_MAINTENANCE->value    => [PermissionsEnum::ADD_ANNOUNCEMENTS->value, PermissionsEnum::EDIT_ANNOUNCEMENTS->value, PermissionsEnum::DELETE_ANNOUNCEMENTS->value],
            PermissionsEnum::VIEW_GALLERY_MAINTENANCE->value          => [PermissionsEnum::ADD_GALLERY->value, PermissionsEnum::EDIT_GALLERY->value, PermissionsEnum::DELETE_GALLERY->value],
        ];

        // Check if user selected restricted actions without corresponding view
        foreach ($restrictions as $view => $actions) {
            foreach ($actions as $action) {
                if (in_array($action, $permissions) && !in_array($view, $permissions)) {
                    Log::warning('Roles & Permissions: Create role failed - Restriction violation', [
                        'user_id' => Auth::guard('admin')->id(),
                        'missing_view' => $view,
                        'action' => $action,
                        'timestamp' => now(),
                    ]);
                    return redirect()->route('maintenance.roles-and-permissions.create-role')
                        ->with('toast-warning', "You must select '{$view}' before selecting '{$action}'");
                }
            }
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            if (Role::where('name', $request->input('role'))->exists()) {
                DB::rollBack();
                Log::warning('Roles & Permissions: Create role failed - Role exists', [
                    'user_id' => Auth::guard('admin')->id(),
                    'role_name' => $request->input('role'),
                    'timestamp' => now(),
                ]);
                return redirect()->route('maintenance.roles-and-permissions.create-role')->with('toast-warning', 'Role already exists');
            }
            $role = Role::create(['name' => $request->input('role')]);
            $role->syncPermissions($request->input('permissions'));
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Roles & Permissions: Create role failed - Database error', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Log::info('Roles & Permissions: Role created successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'role_name' => $request->input('role'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role created successfully');
    }
    /**
     * Edit an existing role and assign permissions to it.
     *
     * The validation rules for this function are:
     * - The 'role' field must be a required string with a maximum length of 50 characters.
     * - The 'permissions' field must be an array.
     *
     * If the user selects restricted actions without their view, the function will redirect back to the edit role page with a toast warning.
     *
     * If the role already exists, the function will redirect back to the edit role page with a toast warning.
     *
     * If an exception occurs during the database transaction, the function will rollback the transaction and redirect back to the management page with a toast error.
     *
     * If the transaction is successful, the function will redirect back to the management page with a toast success.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $role_id = array_keys($request->all())[0];
        Log::info('Roles & Permissions: Edit Role page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name ?? Auth::guard('admin')->user()->first_name,
            'role_id' => $role_id,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        try {
            $role = Role::findById($role_id);
            $permissions = Permission::with('roles')
                ->where('guard_name', 'admin')
                ->where('name', '!=', PermissionsEnum::MODIFY_ADMIN->value)
                ->where('name', '!=', PermissionsEnum::CREATE_BACKUPS->value)
                ->where('name', '!=', PermissionsEnum::VIEW_AUDIT_REPORTS->value)
                ->where('name', '!=', PermissionsEnum::MODIFY_UI_SETTINGS->value)
                ->orderBy('name', 'asc')
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Roles & Permissions: Edit role page failed - Database error', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        return view('roles_permissions.edit', compact('role', 'permissions'));
    }
    /**
     * Update an existing role and assign permissions to it.
     *
     * The validation rules for this function are:
     * - If the user is trying to update the super admin role, the function will only validate that the 'permissions' field is an array.
     * - If the user is trying to update any other role, the function will validate that the 'role' field is a required string with a maximum length of 50 characters and that the 'permissions' field is an array.
     *
     * If the user selects restricted actions without their view, the function will redirect back to the edit role page with a toast warning.
     *
     * If the role already exists, the function will redirect back to the edit role page with a toast warning.
     *
     * If an exception occurs during the database transaction, the function will rollback the transaction and redirect back to the management page with a toast error.
     *
     * If the transaction is successful, the function will redirect back to the management page with a toast success.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        Log::info('Roles & Permissions: Attempting to update role', [
            'user_id' => Auth::guard('admin')->id(),
            'role_id' => $request->input('role_id'),
            'new_name' => $request->input('role'),
            'permissions_count' => count($request->input('permissions') ?? []),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if ($request->input('role_id') == 1) {
            $request->validate([
                'permissions' => 'array',
            ]);
        } else {
            $request->validate([
                'role' => 'required|string|max:50',
                'permissions' => 'array',
            ]);
        }
        $permissions = $request->input('permissions');
        if ($permissions == null) {
            Log::warning('Roles & Permissions: Update role failed - No permissions selected', [
                'user_id' => Auth::guard('admin')->id(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', 'Please select at least one permission');
        }
        // Define the mapping between view and action permissions
        $restrictions = [
            PermissionsEnum::VIEW_USERS_MAINTENANCE->value            => [PermissionsEnum::ADD_USERS->value, PermissionsEnum::EDIT_USERS->value, PermissionsEnum::DELETE_USERS->value],
            PermissionsEnum::VIEW_BOOKS_MAINTENANCE->value            => [PermissionsEnum::ADD_BOOKS->value, PermissionsEnum::EDIT_BOOKS->value, PermissionsEnum::DELETE_BOOKS->value],
            PermissionsEnum::VIEW_SUBJECTS_MAINTENANCE->value         => [PermissionsEnum::ADD_SUBJECTS->value, PermissionsEnum::EDIT_SUBJECTS->value, PermissionsEnum::DELETE_SUBJECTS->value],
            PermissionsEnum::VIEW_BOOK_CATEGORIES_MAINTENANCE->value  => [PermissionsEnum::ADD_CATEGORIES->value, PermissionsEnum::EDIT_CATEGORIES->value, PermissionsEnum::DELETE_CATEGORIES->value],
            PermissionsEnum::VIEW_PRIVILEGES_MAINTENANCE->value       => [PermissionsEnum::ADD_PRIVILEGES->value, PermissionsEnum::EDIT_PRIVILEGES->value, PermissionsEnum::DELETE_PRIVILEGES->value],
            PermissionsEnum::VIEW_PENALTY_RULES_MAINTENANCE->value    => [PermissionsEnum::ADD_PENALTY_RULES->value, PermissionsEnum::EDIT_PENALTY_RULES->value, PermissionsEnum::DELETE_PENALTY_RULES->value],
            PermissionsEnum::VIEW_TRANSACTIONS_MAINTENANCE->value     => [PermissionsEnum::EDIT_TRANSACTIONS->value],
            PermissionsEnum::VIEW_ANNOUNCEMENTS_MAINTENANCE->value    => [PermissionsEnum::ADD_ANNOUNCEMENTS->value, PermissionsEnum::EDIT_ANNOUNCEMENTS->value, PermissionsEnum::DELETE_ANNOUNCEMENTS->value],
            PermissionsEnum::VIEW_GALLERY_MAINTENANCE->value          => [PermissionsEnum::ADD_GALLERY->value, PermissionsEnum::EDIT_GALLERY->value, PermissionsEnum::DELETE_GALLERY->value],
        ];

        // Check if user selected restricted actions without corresponding view
        foreach ($restrictions as $view => $actions) {
            foreach ($actions as $action) {
                if (in_array($action, $permissions) && !in_array($view, $permissions)) {
                    Log::warning('Roles & Permissions: Update role failed - Restriction violation', [
                        'user_id' => Auth::guard('admin')->id(),
                        'missing_view' => $view,
                        'action' => $action,
                        'timestamp' => now(),
                    ]);
                    return redirect()->back()->with('toast-warning', "You must select '{$view}' before selecting '{$action}'");
                }
            }
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $role = Role::findById($request->input('role_id'));
            if (!$request->input('role_id') == Role::findByName('Super Admin')->id) {
                $role->name = $request->input('role');
                $role->save();
            }
            if ($request->input('role_id') == Role::findByName('Super Admin')->id) {
                $permissions[] = PermissionsEnum::MODIFY_ADMIN;
                $permissions[] = PermissionsEnum::CREATE_BACKUPS;
                $permissions[] = PermissionsEnum::VIEW_AUDIT_REPORTS;
                $permissions[] = PermissionsEnum::MODIFY_UI_SETTINGS;
            }
            $role->syncPermissions($permissions);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Roles & Permissions: Update role failed - Database error', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Log::info('Roles & Permissions: Role updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'role_id' => $request->input('role_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role updated successfully');
    }
    /**
     * Delete a role and all of its permissions.
     *
     * If the role is assigned to users, the function will rollback the transaction and redirect back to the management page with a toast warning.
     *
     * If an exception occurs during the database transaction, the function will rollback the transaction and redirect back to the management page with a toast error.
     *
     * If the transaction is successful, the function will redirect back to the management page with a toast success.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Database\QueryException
     */
    public function destroy(Request $request)
    {
        Log::info('Roles & Permissions: Attempting to delete role', [
            'user_id' => Auth::guard('admin')->id(),
            'role_id' => $request->input('deleteRole'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $role = Role::findById($request->input('deleteRole'));
            if ($role->users()->count() > 0) {
                DB::rollBack();
                Log::warning('Roles & Permissions: Delete role failed - Role assigned to users', [
                    'user_id' => Auth::guard('admin')->id(),
                    'role_id' => $request->input('deleteRole'),
                    'user_count' => $role->users()->count(),
                    'timestamp' => now(),
                ]);
                return redirect()->back()->with('toast-warning', 'Role cannot be deleted because it is assigned to users');
            }
            $role->revokePermissionTo($role->permissions);
            $role->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Roles & Permissions: Delete role failed - Database error', [
                'user_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Log::info('Roles & Permissions: Role deleted successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'role_id' => $request->input('deleteRole'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role deleted successfully');
    }
}
