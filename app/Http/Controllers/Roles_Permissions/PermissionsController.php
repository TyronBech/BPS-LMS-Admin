<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsController extends Controller
{
    public function index()
    {
        $admin = User::findOrFail(3);
        $adminRole = $admin->getRoleNames();
        $adminPermissions = $admin->getAllPermissions();
        return ['adminRole' => $adminRole, 'adminPermissions' => $adminPermissions];
    }
}
