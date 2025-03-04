<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Penalty;

class PenaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $model = Penalty::class;
    public function run(): void
    {
        Penalty::factory()->count(3)->create();
    }
}
