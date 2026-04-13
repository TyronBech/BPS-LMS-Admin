<?php

namespace Database\Seeders;

use App\Models\EmployeeDetail;
use App\Models\StudentDetail;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\VisitorDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement("SET @current_user_id = 'seeder'");

        $this->call([
            RolePermissionSeeder::class,
            UserGroupSeeder::class,
            CategorySeeder::class,
            BookSeeder::class,
            SubjectSeeder::class,
            SubjectAccessCodeSeeder::class,
            UserSeeder::class,
            StudentDetailSeeder::class,
            EmployeeDetailSeeder::class,
            VisitorDetailSeeder::class,
            InventorySeeder::class,
            PenaltyRuleSeeder::class,
            TransactionSeeder::class,
            PenaltySeeder::class,
            LogSeeder::class,
            ResetBookMatrix::class,
        ]);

        $employeePrivilege = UserGroup::query()
            ->where('user_type', 'employee')
            ->where('category', 'Librarian')
            ->first();

        if (!$employeePrivilege) {
            $employeePrivilege = UserGroup::query()
                ->where('user_type', 'employee')
                ->first();
        }

        $admin = User::updateOrCreate(
            [
                'email' => 'tyronbechayda1112@gmail.com',
            ],
            [
                'rfid' => '6160866730887',
                'privilege_id' => $employeePrivilege?->id,
                'first_name' => 'Tyron',
                'middle_name' => null,
                'last_name' => 'Bechayda',
                'suffix' => null,
                'gender' => 'Male',
                'profile_image' => 'default.jpg',
                'password' => Hash::make('password'),
            ]
        );

        EmployeeDetail::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'employee_id' => 'EMP-TYRON-0001',
                'employee_role' => 'Librarian',
            ]
        );

        StudentDetail::where('user_id', $admin->id)->delete();
        VisitorDetail::where('user_id', $admin->id)->delete();

        $admin->syncRoles(['Super Admin']);
    }
}
