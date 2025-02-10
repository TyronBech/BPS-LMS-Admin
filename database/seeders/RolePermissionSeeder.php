<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        //Permission::create(['name' => 'Edit Users',       'guard_name' => 'admin']);
        //Permission::create(['name' => 'Delete Users',     'guard_name' => 'admin']);
        //Permission::create(['name' => 'Create Users',     'guard_name' => 'admin']);
        //Permission::create(['name' => 'Modify Admins',    'guard_name' => 'admin']);
        Permission::create(['name' => 'Create Books',    'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Books',    'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Books',    'guard_name' => 'admin']);

        //$role = Role::create(['name' => 'Admin', 'guard_name' => 'admin']);
        //$role->givePermissionTo('Edit Users', 'Create Users');
        //$role = Role::findById(3, 'admin');
        //$permission = Permission::findById(3, 'admin');
        //$role->revokePermissionTo($permission);
        //$role->givePermissionTo('Edit Users', 'Create Users');
        $role = Role::create(['name' => 'Librarian', 'guard_name' => 'admin']);
        $role->givePermissionTo('Create Books', 'Edit Books', 'Delete Books');
    }
}
