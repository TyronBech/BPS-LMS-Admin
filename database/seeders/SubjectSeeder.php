<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
  public function run(): void
  {
    Subject::factory()->count(30)->create();
  }
}
