<?php

namespace Database\Seeders;

use App\Models\EmployeeDetail;
use App\Models\StudentDetail;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\VisitorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('CALL DistributeStagingUsers()');

        $employeePrivilege = UserGroup::query()
            ->where('user_type', 'employee')
            ->where('category', 'Librarian')
            ->first();

        if (!$employeePrivilege) {
            $employeePrivilege = UserGroup::query()
                ->where('user_type', 'employee')
                ->first();
        }

        foreach (StagingUserSeeder::accounts() as $account) {
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
                'password' => Hash::make(StagingUserSeeder::DEFAULT_PASSWORD),
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
