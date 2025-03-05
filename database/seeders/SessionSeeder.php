<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Session::flush(); // Removes all session data for the current session.
        Session::regenerateToken(); // Regenerates the CSRF token.
        DB::table('sessions')->truncate();
    }
}
