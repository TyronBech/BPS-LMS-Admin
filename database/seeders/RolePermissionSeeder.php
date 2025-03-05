<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Permission::create(['name' => 'Edit Users',     'guard_name' => 'admin']);
        // Permission::create(['name' => 'Delete Users',   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Create Users',   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Modify Admins',  'guard_name' => 'admin']);
        // Permission::create(['name' => 'Create Books',   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Edit Books',     'guard_name' => 'admin']);
        // Permission::create(['name' => 'Delete Books',   'guard_name' => 'admin']);

        // $role1 = Role::create(['name' => 'Super Admin',  'guard_name' => 'admin']);
        // $role2 = Role::create(['name' => 'Admin',        'guard_name' => 'admin']);
        // $role3 = Role::create(['name' => 'Librarian',    'guard_name' => 'admin']);
        // $role1->givePermissionTo('Edit Users', 'Create Users', 'Delete Users', 'Modify Admins', 'Create Books', 'Edit Books', 'Delete Books');
        // $role2->givePermissionTo('Edit Users', 'Create Users', 'Delete Users', 'Create Books', 'Edit Books', 'Delete Books');
        // $role3->givePermissionTo('Create Books', 'Edit Books', 'Delete Books');
        //$role = Role::findById(3, 'admin');
        //$permission = Permission::findById(3, 'admin');
        //$role->revokePermissionTo($permission);
        //$role->givePermissionTo('Edit Users', 'Create Users');
        //$role->givePermissionTo('Create Books', 'Edit Books', 'Delete Books');
        // $admin1 = User::find(1);
        // $admin1->assignRole('Super Admin');
        // $admin2 = User::find(2);
        // $admin2->assignRole('Admin');
    }
}
