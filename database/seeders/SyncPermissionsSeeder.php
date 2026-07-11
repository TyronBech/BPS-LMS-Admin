<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions we need for the report module additions
        $permissions = [
            'Create Non-Circulation Entry',
            'View Non-Circulation Reports',
            'Create Printing Entry',
            'View Printing Reports',
        ];

        $permissionInstances = [];
        foreach ($permissions as $permissionName) {
            $permissionInstances[] = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);
        }

        // Roles to assign
        $roles = ['Super Admin', 'Admin', 'Librarian'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'admin')->first();
            if ($role) {
                $role->givePermissionTo($permissionInstances);
            }
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
