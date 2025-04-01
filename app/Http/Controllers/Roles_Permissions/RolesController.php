<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\user;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function index()
    {
        $roles_with_permissions = Role::with('permissions')
                                    ->whereHas('permissions', function ($query) {
                                        $query->where('guard_name', 'admin')
                                                ->where('name', '!=', 'Modify Admins');
                                    })
                                    ->get();
        $permissions_with_roles = Permission::with('roles')
                                    ->where('guard_name', 'admin')
                                    ->where('name', '!=', 'Modify Admins')
                                    ->get();
        $admins = User::all();
        return view('roles_permissions.roles', compact('roles_with_permissions', 'permissions_with_roles', 'admins'));
    }
    public function create()
    {
        $permissions = Permission::all();
        return view('roles_permissions.create', compact('permissions'));
    }
    public function store(Request $request){
        $request->validate([
            'role' => 'required|string|max:50',
        ]);
        if($request->input('permissions') == null){
            return redirect()->route('maintenance.roles-and-permissions.create-role')->with('toast-warning', 'Please select at least one permission');
        }
        DB::beginTransaction();
        try{
            if(Role::where('name', $request->input('role'))->exists()){
                DB::rollBack();
                return redirect()->route('maintenance.roles-and-permissions.create-role')->with('toast-warning', 'Role already exists');
            }
            $role = Role::create(['name' => $request->input('role')]);
            $role->syncPermissions($request->input('permissions'));
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role created successfully');
    }
    public function edit(Request $request)
    {
        $role_id = array_keys($request->all())[0];
        try{
            $role = Role::findById($role_id);
            $permissions = Permission::with('roles')
                            ->where('guard_name', 'admin')
                            ->where('name', '!=', 'Modify Admins')
                            ->get();
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        return view('roles_permissions.edit', compact('role', 'permissions'));
    }
    public function update(Request $request){
        $request->validate([
            'role' => 'required|string|max:50',
        ]);
        if($request->input('permissions') == null){
            return redirect()->back()->with('toast-warning', 'Please select at least one permission');
        }
        DB::beginTransaction();
        try{
            $role = Role::findById($request->input('role_id'));
            $role->name = $request->input('role');
            $role->save();
            $role->syncPermissions($request->input('permissions'));
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role updated successfully');
    }
    public function destroy(Request $request){
        DB::beginTransaction();
        try{
            $role = Role::findById($request->input('id'));
            $role->revokePermissionTo($role->permissions);
            $role->delete();
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role deleted successfully');
    }
    private function assign_permission(Request $request, $role_id){
        $role = Role::findById($role_id);
        $role->givePermissionTo($request->input('permission'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Permission assigned successfully');
    }
    private function revoke_permission(Request $request, $role_id){
        $role = Role::findById($role_id);
        $role->revokePermissionTo($request->input('permission'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Permission revoked successfully');
    }
    private function assign_role(Request $request, $admin_id){
        $admin = User::find($admin_id);
        $admin->assignRole($request->input('role'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role assigned successfully');
    }
    private function revoke_role(Request $request, $admin_id){
        $admin = User::find($admin_id);
        $admin->removeRole($request->input('role'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role revoked successfully');
    }
}
