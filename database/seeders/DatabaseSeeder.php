<?php

namespace Database\Seeders;

use App\Models\User;
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
        $this->call([
            RolePermissionSeeder::class,
            ImportantTablesSeeder::class,
        ]);

        $admin = User::updateOrCreate(
            [
                'email' => 'tyronbechayda1112@gmail.com',
            ],
            [
                'rfid' => '6160866730887',
                'privilege_id' => null,
                'first_name' => 'Tyron',
                'middle_name' => null,
                'last_name' => 'Bechayda',
                'suffix' => null,
                'gender' => 'Male',
                'profile_image' => 'default.jpg',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['Super Admin']);
    }
}
