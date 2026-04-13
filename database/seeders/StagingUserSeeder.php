<?php

namespace Database\Seeders;

use App\Models\StagingUser;
use Illuminate\Database\Seeder;

class StagingUserSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'password';

    public static function accounts(): array
    {
        return [
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
                'middle_name' => null,
                'last_name' => 'Ramos',
                'suffix' => null,
                'gender' => 'Female',
                'employee_id' => 'EMP-RAMOS-0001',
                'employee_role' => 'Librarian',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::accounts() as $account) {
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
                    'password' => self::DEFAULT_PASSWORD,
                    'profile_image' => 'default.jpg',
                    'user_type' => 'employee',
                    'employee_id' => $account['employee_id'],
                    'employee_role' => $account['employee_role'],
                ]
            );
        }
    }
}
