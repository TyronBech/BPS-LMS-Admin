<?php

namespace Database\Factories;

use App\Models\PenaltyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenaltyRule>
 */
class PenaltyRuleFactory extends Factory
{
  protected $model = PenaltyRule::class;

  public function definition(): array
  {
    $preset = $this->faker->randomElement([
      [
        'type' => 'Overdue',
        'description' => 'Overdue fine per day',
        'rate' => 5.00,
        'per_day' => 1,
      ],
      [
        'type' => 'Lost Book',
        'description' => 'Replacement fee for lost book',
        'rate' => 300.00,
        'per_day' => 0,
      ],
      [
        'type' => 'Damaged Book',
        'description' => 'Damage fee',
        'rate' => 150.00,
        'per_day' => 0,
      ],
    ]);

    return $preset;
  }
}
