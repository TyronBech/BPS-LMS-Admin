<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function index()
    {
        $roles_with_permissions = Role::with('permissions')->get();
        $permissions_with_roles = Permission::with('roles')->get();
        $admins = Admin::all();
        return view('roles_permissions.roles', compact('roles_with_permissions', 'permissions_with_roles', 'admins'));
    }
    public function create()
    {
        $permissions = Permission::all();
        return view('roles_permissions.create', compact('permissions'));
    }
    public function store(Request $request){
        $request->validate([
            'role'          => 'required|string|max:50',
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
            for($i = 0; $i < count($request->input('permissions')); $i++){
                $role->givePermissionTo($request->input('permissions')[$i]);
            }
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-error', 'Something went wrong');
        }
        DB::commit();
        return redirect()->route('maintenance.roles-and-permissions.management')->with('toast-success', 'Role created successfully');
    }
    public function edit($role_id)
    {
        $role = Role::findById($role_id);
        $permissions = Permission::all();
        return view('roles_permissions.edit', compact('role', 'permissions'));
    }
    public function update(Request $request, $role_id){
        $request->validate([
            'role'          => 'required|string|max:50',
            'permissions'   => 'required',
        ]);
        $role = Role::findById($role_id);
        $role->name = $request->input('role');
        $role->save();
        $role->syncPermissions($request->input('permissions'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role updated successfully');
    }
    public function destroy($role_id){
        $role = Role::findById($role_id);
        $role->delete();
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role deleted successfully');
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
        $admin = Admin::find($admin_id);
        $admin->assignRole($request->input('role'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role assigned successfully');
    }
    private function revoke_role(Request $request, $admin_id){
        $admin = Admin::find($admin_id);
        $admin->removeRole($request->input('role'));
        return redirect()->route('roles_permissions.roles')->with('toast-success', 'Role revoked successfully');
    }
}
