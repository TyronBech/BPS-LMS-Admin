<?php

namespace Database\Seeders;

use App\Models\PenaltyRule;
use Illuminate\Database\Seeder;

class PenaltyRuleSeeder extends Seeder
{
  public function run(): void
  {
    PenaltyRule::factory()->count(3)->create();
  }
}
