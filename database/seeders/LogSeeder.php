<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Log;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::factory()->count(100)->create();
        // Log::create([
        //     'user_id'       => 5,
        //     'computer_use'  => 'Yes',
        //     'timestamp'     => now(),
        //     'action'        => 'Time in',
        // ]);
        // Log::create([
        //     'user_id'       => 35,
        //     'computer_use'  => 'No',
        //     'timestamp'     => now(),
        //     'action'        => 'Time out',
        // ]);
    }
}
