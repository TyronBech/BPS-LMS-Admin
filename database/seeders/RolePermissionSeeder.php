<?php

namespace Database\Seeders;

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
        Permission::create(['name' => 'Modify Admins',                       'guard_name' => 'admin']);
        Permission::create(['name' => 'Add Users',                           'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Users',                          'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Users',                        'guard_name' => 'admin']);
        Permission::create(['name' => 'Add Books',                           'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Books',                          'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Books',                        'guard_name' => 'admin']);
        Permission::create(['name' => 'Create Reports',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'View User Reports',                   'guard_name' => 'admin']);
        Permission::create(['name' => 'View Summary Reports',                'guard_name' => 'admin']);
        Permission::create(['name' => 'View Inventory Reports',              'guard_name' => 'admin']);
        Permission::create(['name' => 'View Book Circulation Reports',       'guard_name' => 'admin']);
        Permission::create(['name' => 'View Accession List Reports',         'guard_name' => 'admin']);
        Permission::create(['name' => 'View Penalty Reports',                'guard_name' => 'admin']);
        Permission::create(['name' => 'Book Inventory',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'Import Users',                        'guard_name' => 'admin']);
        Permission::create(['name' => 'Import Faculties & Staffs',           'guard_name' => 'admin']);
        Permission::create(['name' => 'Import Books',                        'guard_name' => 'admin']);
        Permission::create(['name' => 'Add Privileges',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Privileges',                     'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Privileges',                   'guard_name' => 'admin']);
        Permission::create(['name' => 'Add Categories',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Categories',                     'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Categories',                   'guard_name' => 'admin']);
        Permission::create(['name' => 'Add Penalty Rule',                    'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Penalty Rule',                   'guard_name' => 'admin']);
        Permission::create(['name' => 'Delete Penalty Rule',                 'guard_name' => 'admin']);
        Permission::create(['name' => 'Edit Book Circulations',              'guard_name' => 'admin']);
        Permission::create(['name' => 'View Users Maintenance',              'guard_name' => 'admin']);
        Permission::create(['name' => 'View Books Maintenance',              'guard_name' => 'admin']);
        Permission::create(['name' => 'View Book Categories Maintenance',    'guard_name' => 'admin']);
        Permission::create(['name' => 'View Privileges Maintenance',         'guard_name' => 'admin']);
        Permission::create(['name' => 'View Penalty Rules Maintenance',      'guard_name' => 'admin']);
        Permission::create(['name' => 'View Book Circulations Maintenance',  'guard_name' => 'admin']);
        Permission::create(['name' => 'View Dashboard',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'Create Backups',                      'guard_name' => 'admin']);
        Permission::create(['name' => 'View Audit Reports',                  'guard_name' => 'admin']);
        Permission::create(['name' => 'Reservation Approvals',               'guard_name' => 'admin']);
        Permission::create(['name' => 'Modify UI Settings',                  'guard_name' => 'admin']);


        $role1 = Role::create(['name' => 'Super Admin',  'guard_name' => 'admin']);
        $role2 = Role::create(['name' => 'Admin',        'guard_name' => 'admin']);
        $role3 = Role::create(['name' => 'Librarian',    'guard_name' => 'admin']);

        // Super Admin gets all permissions
        $role1->givePermissionTo(Permission::all());

        // Admin gets most permissions except the most critical system ones
        $role2->givePermissionTo([
            'Add Users', 'Edit Users', 'Delete Users',
            'Add Books', 'Edit Books', 'Delete Books',
            'Create Reports', 'View User Reports', 'View Summary Reports',
            'View Inventory Reports', 'View Book Circulation Reports',
            'View Accession List Reports', 'View Penalty Reports',
            'Book Inventory', 'Import Users', 'Import Faculties & Staffs', 'Import Books',
            'Add Privileges', 'Edit Privileges', 'Delete Privileges',
            'Add Categories', 'Edit Categories', 'Delete Categories',
            'Add Penalty Rule', 'Edit Penalty Rule', 'Delete Penalty Rule',
            'Edit Book Circulations', 'View Users Maintenance', 'View Books Maintenance',
            'View Book Categories Maintenance', 'View Privileges Maintenance',
            'View Penalty Rules Maintenance', 'View Book Circulations Maintenance',
            'View Dashboard', 'Reservation Approvals'
        ]);

        // Librarian gets book, inventory, and circulation management permissions
        $role3->givePermissionTo([
            'Add Books', 'Edit Books',
            'Create Reports', 'View Inventory Reports', 'View Book Circulation Reports',
            'View Accession List Reports', 'Book Inventory', 'Import Books',
            'Edit Book Circulations', 'View Books Maintenance',
            'View Book Categories Maintenance', 'View Book Circulations Maintenance',
            'View Dashboard', 'Reservation Approvals'
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
