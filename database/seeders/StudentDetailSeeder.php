<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentDetail;

class StudentDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $model = StudentDetail::class;
    public function run(): void
    {
        StudentDetail::factory()->count(5)->create();
    }
}
