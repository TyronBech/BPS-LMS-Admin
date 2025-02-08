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
        //Permission::create(['name' => 'Add Users',        'guard_name' => 'admin']);
        //Permission::create(['name' => 'Modify Admins',    'guard_name' => 'admin']);

        $role = Role::create(['name' => 'Admin', 'guard_name' => 'admin']);
        $role->givePermissionTo('Edit Users', 'Add Users');
    }
}
