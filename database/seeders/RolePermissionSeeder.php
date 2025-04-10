<?php

namespace Database\Seeders;

use App\Enum\RolesEnum;
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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Permission::create(['name' => 'Modify Admins',                  'guard_name' => 'admin']);
        // Permission::create(['name' => 'Add Users',                      'guard_name' => 'admin']);
        // Permission::create(['name' => 'Edit Users',                     'guard_name' => 'admin']);
        // Permission::create(['name' => 'Delete Users',                   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Add Books',                      'guard_name' => 'admin']);
        // Permission::create(['name' => 'Edit Books',                     'guard_name' => 'admin']);
        // Permission::create(['name' => 'Delete Books',                   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Create Reports',                 'guard_name' => 'admin']);
        // Permission::create(['name' => 'View User Reports',              'guard_name' => 'admin']);
        // Permission::create(['name' => 'View Summary Reports',           'guard_name' => 'admin']);
        // Permission::create(['name' => 'View Inventory Reports',         'guard_name' => 'admin']);
        // Permission::create(['name' => 'View Transaction Reports',       'guard_name' => 'admin']);
        // Permission::create(['name' => 'View Book Circulation Reports',  'guard_name' => 'admin']);
        // Permission::create(['name' => 'Book Inventory',                 'guard_name' => 'admin']);
        // Permission::create(['name' => 'Import Users',                   'guard_name' => 'admin']);
        // Permission::create(['name' => 'Import Books',                   'guard_name' => 'admin']);
        
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
        // $admin1 = User::find(5);
        // $admin1->assignRole('Super Admin');
        // $admin2 = User::find(6);
        // $admin2->assignRole('Admin');
        //$user = User::find(18);
        //$user->removeRole(RolesEnum::SUPER_ADMIN);
        // $role1 = Role::findById(1, 'admin');
        // $role2 = Role::findById(2, 'admin');
        // $role3 = Role::findById(3, 'admin');
        // $role1->givePermissionTo('Modify Admins', 'Add Users', 'Edit Users', 'Delete Users', 'Add Books', 'Edit Books', 'Delete Books', 'Create Reports', 'View User Reports', 'View Summary Reports', 'View Inventory Reports', 'View Transaction Reports', 'View Book Circulation Reports', 'Book Inventory', 'Import Users', 'Import Books');
        // $role2->givePermissionTo('Add Users', 'Edit Users', 'Delete Users', 'Add Books', 'Edit Books', 'Delete Books', 'Create Reports', 'View User Reports', 'View Summary Reports', 'View Inventory Reports', 'View Transaction Reports', 'View Book Circulation Reports', 'Book Inventory', 'Import Users', 'Import Books');
        // $role3->givePermissionTo('Add Books', 'Edit Books', 'Delete Books', 'Create Reports', 'View User Reports', 'View Summary Reports', 'View Inventory Reports', 'View Transaction Reports', 'View Book Circulation Reports', 'Book Inventory', 'Import Users', 'Import Books');
    }
}
