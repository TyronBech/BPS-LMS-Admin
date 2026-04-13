<?php

namespace Database\Seeders;

use App\Models\EmployeeDetail;
use App\Models\StagingUser;
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

        $defaultPassword = 'password';

        $requestedSuperAdmins = [
            [
                'email' => 'tyronbechayda1112@gmail.com',
                'rfid' => '6160866730887',
                'first_name' => 'Tyron',
                'middle_name' => null,
                'last_name' => 'Bechayda',
                'suffix' => null,
                'gender' => 'Male',
                'employee_id' => 'EMP-TYRON-0001',
                'employee_role' => 'Librarian',
            ],
            [
                'email' => 'jcormita000@gmail.com',
                'rfid' => null,
                'first_name' => 'Jhon Carl',
                'middle_name' => null,
                'last_name' => 'Ormita',
                'suffix' => null,
                'gender' => 'Prefer not to say',
                'employee_id' => 'EMP-JCORMITA-0001',
                'employee_role' => 'Librarian',
            ],
            [
                'email' => 'christiangomelanfog123@gmail.com',
                'rfid' => null,
                'first_name' => 'Christian',
                'middle_name' => null,
                'last_name' => 'Gomelan',
                'suffix' => null,
                'gender' => 'Prefer not to say',
                'employee_id' => 'EMP-GOMELANFOG-0001',
                'employee_role' => 'Librarian',
            ],
            [
                'email' => 'princessryanramos29@gmail.com',
                'rfid' => null,
                'first_name' => 'Princess Ryan',
                'middle_name' => '',
                'last_name' => 'Ramos',
                'suffix' => null,
                'gender' => 'Female',
                'employee_id' => 'EMP-RAMOS-0001',
                'employee_role' => 'Librarian',
            ],
        ];

        foreach ($requestedSuperAdmins as $account) {
            StagingUser::updateOrCreate(
                ['email' => $account['email']],
                [
                    'rfid' => $account['rfid'],
                    'first_name' => $account['first_name'],
                    'middle_name' => $account['middle_name'],
                    'last_name' => $account['last_name'],
                    'suffix' => $account['suffix'],
                    'gender' => $account['gender'],
                    'email' => $account['email'],
                    'password' => $defaultPassword,
                    'profile_image' => 'default.jpg',
                    'user_type' => 'employee',
                    'employee_id' => $account['employee_id'],
                    'employee_role' => $account['employee_role'],
                ]
            );
        }

        DB::statement('CALL DistributeStagingUsers()');

        foreach ($requestedSuperAdmins as $account) {
            $user = User::where('email', $account['email'])->first();

            if (!$user) {
                continue;
            }

            $user->update([
                'rfid' => $account['rfid'],
                'privilege_id' => $employeePrivilege?->id,
                'first_name' => $account['first_name'],
                'middle_name' => $account['middle_name'],
                'last_name' => $account['last_name'],
                'suffix' => $account['suffix'],
                'gender' => $account['gender'],
                'profile_image' => 'default.jpg',
                'password' => Hash::make($defaultPassword),
            ]);

            EmployeeDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $account['employee_id'],
                    'employee_role' => $account['employee_role'],
                ]
            );

            StudentDetail::where('user_id', $user->id)->delete();
            VisitorDetail::where('user_id', $user->id)->delete();

            $user->syncRoles(['Super Admin']);
        }
    }
}
