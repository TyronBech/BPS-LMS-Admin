<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\SubjectAccessCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubjectAccessCode>
 */
class SubjectAccessCodeFactory extends Factory
{
  protected $model = SubjectAccessCode::class;

  public function definition(): array
  {
    $subject = Subject::query()->inRandomOrder()->first() ?? Subject::factory()->create();

    return [
      'subject_id' => $subject->id,
      'access_code' => strtoupper($this->faker->bothify('???-###')),
    ];
  }
}
