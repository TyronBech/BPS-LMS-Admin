<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class RolesController extends Controller
{
    public function index()
    {
        $roles_and_permissions = Role::with('permissions')->get();
        $admins = Admin::all();
        return view('roles_permissions.roles', compact('roles_and_permissions', 'admins'));
    }
}
