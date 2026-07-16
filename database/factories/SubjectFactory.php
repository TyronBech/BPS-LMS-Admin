<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
  protected $model = Subject::class;

  public function definition(): array
  {
    return [
      'ddc' => str_pad((string) $this->faker->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
      'name' => $this->faker->unique()->words(2, true),
    ];
  }
}
