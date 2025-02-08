<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class PermissionsController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $admin = Admin::findOrFail(1);
        $adminRole = $admin->getRoleNames();
        $adminPermissions = $admin->getAllPermissions();
        return ['roles' => $roles, 'permissions' => $permissions, 'adminRole' => $adminRole, 'adminPermissions' => $adminPermissions];
    }
}
