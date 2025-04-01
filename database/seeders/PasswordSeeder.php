<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::where('id', 1)->first();
        $admin = User::where('id', 18)->first();
        $librarian = User::where('id', 2)->first();

        $superadmin->password = Hash::make('Super@admin');
        $admin->password = Hash::make('Admin@admin');
        $librarian->password = Hash::make('Librarian@admin');
        $superadmin->save();
        $admin->save();
        $librarian->save();
    }
}
