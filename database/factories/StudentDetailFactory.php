<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentDetail>
 */
class StudentDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->unique()->numberBetween(1, 5),
            'lrn' => $this->faker->unique()->ean8(),
            'grade_level' => $this->faker->randomElement(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12']),
            'section' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
