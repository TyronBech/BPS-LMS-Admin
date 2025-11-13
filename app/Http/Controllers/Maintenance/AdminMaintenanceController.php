<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enum\RolesEnum;
use App\Mail\RoleEmailMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;


class AdminMaintenanceController extends Controller
{   
    /**
     * Displays a list of all administrators in the system.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);
        $admins = User::join('model_has_roles', 'usr_users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('roles.guard_name', 'admin')
            ->select('usr_users.*', 'roles.name as role')
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage]);
        return view('maintenance.admins.admins', compact('admins', 'search', 'perPage'));
    }
    /**
     * Returns a view for creating a new admin user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'admin')
            ->get();
        return view('maintenance.admins.create', compact('roles'));
    }
    /**
     * Search for a user in the system.
     *
     * This function will take a search query from the request and
     * search for a user in the system. It will return a view
     * with the searched users and the available admin roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search_user(Request $request)
    {
        $search = strtolower($request->input('user-info'));
        $searched = User::join('privileges', 'usr_users.privilege_id', '=', 'privileges.id')
            ->select('usr_users.id', 'first_name', 'middle_name', 'last_name', 'email', 'rfid')
            ->where('privileges.user_type', '!=', 'visitor')
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('concat(first_name, " ", middle_name, " ", last_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('concat(middle_name, " ", last_name, ", ", first_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('concat(last_name, ", ", first_name, " ", middle_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.last_name, ", ", usr_users.first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.first_name, " ", usr_users.last_name))'), 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('rfid', 'like', '%' . $search . '%');
            })
            ->doesntHave('roles')
            ->first();
        $roles = Role::where('guard_name', 'admin')->get();
        return view('maintenance.admins.create', compact('searched', 'roles'));
    }
    /**
     * Search for an admin user in the system.
     *
     * This function will take a search query from the request and
     * search for an admin user in the system. It will return a view
     * with the searched admins and the available admin roles.
     *
     * The search query will search for the following fields:
     *      - First name
     *      - Middle name
     *      - Last name
     *      - Email
     *      - RFID
     *      - Full name (first name, middle name, last name)
     *      - Full name (middle name, last name, first name)
     *      - Full name (last name, first name, middle name)
     *      - Roles name
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search_admin(Request $request)
    {
        $users = new User();
        $search = strtolower($request->input('search'));
        $perPage = $request->input('perPage', 10);
        $admins = User::join('model_has_roles', $users->getTable() . '.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('privileges', $users->getTable() . '.privilege_id', '=', 'privileges.id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('roles.guard_name', 'admin')
            ->where('privileges.user_type', '!=', 'visitor')
            ->where(function ($query) use ($search) {
                $query->where('usr_users.first_name', 'like', '%' . $search . '%')
                    ->orWhere('usr_users.middle_name', 'like', '%' . $search . '%')
                    ->orWhere('usr_users.last_name', 'like', '%' . $search . '%')
                    ->orWhere('usr_users.email', 'like', '%' . $search . '%')
                    ->orWhere('usr_users.rfid', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.first_name, " ", usr_users.middle_name, " ", usr_users.last_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.middle_name, " ", usr_users.last_name, ", ", usr_users.first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.last_name, ", ", usr_users.first_name, " ", usr_users.middle_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.last_name, ", ", usr_users.first_name))'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('lower(concat(usr_users.first_name, " ", usr_users.last_name))'), 'like', '%' . $search . '%');
            })
            ->orWhere('roles.name', 'like', '%' . $search . '%')
            ->select('usr_users.*', 'roles.name as role')
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage]);
        return view('maintenance.admins.admins', compact('admins', 'search', 'perPage'));
    }
    /**
     * Stores a new admin user in the system.
     *
     * This function will take the RFID and role from the request and
     * store a new admin user in the system. It will roll back the
     * transaction if there is an error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->input('adminID') == null) {
            return redirect()->route('maintenance.create-admin')->with('toast-warning', 'Please select an admin');
        }
        if ($request->input('role') == 'None') {
            return redirect()->route('maintenance.create-admin')->with('toast-warning', 'Please select a role');
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $role = $request->input('role');
            $admin = User::with('privileges')->where('rfid', $request->input('adminID'))->first();
            if($admin->privileges->user_type === 'student' && $role === 'Super Admin'){
                DB::rollBack();
                return redirect()->route('maintenance.create-admin')->with('toast-warning', 'A student cannot be assigned as Super Admin');
            }
            $admin->assignRole($role);
            $this->notification($admin, $role);
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('maintenance.create-admin')->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->route('maintenance.admins')->with('toast-success', 'Admin created successfully');
    }
    /**
     * Returns a view for editing an admin user.
     *
     * This function will take the request and search for an admin user
     * in the system. It will return a view with the searched admin
     * and the available admin roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $admin = null;
        $super_admin = null;
        try {
            $id = $request->input('id');
            $admin = User::findOrFail($id);
            $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
            if ($authAdmin->hasRole(RolesEnum::SUPER_ADMIN)) {
                $super_admin = Role::where('name', 'Super Admin')
                    ->where('guard_name', 'admin')
                    ->first();
            }
            $roles = Role::where('guard_name', 'admin')
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.admins.edit', compact('admin', 'super_admin', 'roles'));
    }
    /**
     * Update an admin user with the given request data.
     *
     * This function validates the request and checks if the authenticated
     * admin has permission to modify the admin. It then updates the admin
     * and syncs the roles. If there is an error, it rolls back the transaction
     * and redirects to the previous page with an error message.
     *
     * @throws \Illuminate\Database\QueryException
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = new User();
        $request->validate([
            'first-name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle-name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last-name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'email'         => 'required|email|unique:' . $user->getTable() . ',email,' . $request->input('id'),
            'role'          => [
                'required',
                function ($attribute, $value, $fail) {
                    $role = Role::where('guard_name', 'admin')
                        ->where(function($query) use ($value) {
                            $query->where('id', $value)
                                  ->orWhere('name', $value);
                        })
                        ->first();
                    
                    if (!$role) {
                        $fail('Selected role is invalid.');
                    }
                }
            ],
        ], [
            'first-name.required'    => 'First name is required',
            'last-name.required'     => 'Last name is required',
            'email.required'         => 'Email is required',
            'email.email'            => 'Email must be a valid email address',
            'email.unique'           => 'Email has already been taken',
            'role.required'          => 'Role is required',
        ]);
        
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
            
            if ($authAdmin->hasAnyRole(RolesEnum::SUPER_ADMIN, RolesEnum::ADMIN)) {
                // Find role by name or ID
                $role = Role::where('guard_name', 'admin')
                    ->where(function($query) use ($request) {
                        $query->where('id', $request->input('role'))
                              ->orWhere('name', $request->input('role'));
                    })
                    ->first();
                
                $admin = User::with('privileges')->findOrFail($request->input('id'));
                
                if($admin->privileges->user_type == 'student' && $role->name == 'Super Admin'){
                    DB::rollBack();
                    return redirect()->back()->with('toast-warning', 'A student cannot be assigned as Super Admin');
                }
                
                $admin->update([
                    'first_name'    => $request->input('first-name'),
                    'middle_name'   => $request->input('middle-name'),
                    'last_name'     => $request->input('last-name'),
                    'email'         => $request->input('email'),
                ]);
                
                $admin->syncRoles($role);
                $this->notification($admin, $role->name);
            } else {
                return redirect()->back()->with('toast-error', 'You do not have permission to modify admin');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        
        DB::commit();
        return redirect()->route('maintenance.admins')->with('toast-success', 'Admin updated successfully');
    }
    /**
     * Delete an admin user with the given request data.
     *
     * This function checks if the authenticated user has permission to delete
     * the admin. It then deletes the admin and syncs the roles. If there
     * is an error, it rolls back the transaction and directs to the
     * previous page with an error message.
     *
     * @throws \Exception
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $userToDelete = User::findOrFail($request->input('id'));
        $authenticatedUser = User::findOrFail(Auth::guard('admin')->user()->id);

        if ($authenticatedUser->id === $userToDelete->id) {
            return redirect()->back()->with('toast-warning', 'You cannot delete yourself.');
        }
        if ($userToDelete->hasRole(RolesEnum::SUPER_ADMIN)) {
            if (!$authenticatedUser->hasRole(RolesEnum::SUPER_ADMIN)) {
                return redirect()->back()->with('toast-warning', 'You cannot delete a super admin.');
            }
        }
        DB::beginTransaction();
        try {
            $userToDelete->syncRoles([]);  
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.admins')->with('toast-success', 'Admin deleted successfully.');
    }
    /**
     * Sends an email notification to the given user regarding the new role.
     *
     * @param User $user The user to send the notification to.
     * @param string $role The new role of the user.
     */
    private function notification(User $user, $role)
    {
        Mail::to($user->email)->send(new RoleEmailMessage($user, $role));
    }
}
