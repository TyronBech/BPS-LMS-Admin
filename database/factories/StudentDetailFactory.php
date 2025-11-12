<?php

namespace Database\Factories;

use App\Models\StudentDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentDetail>
 */
class StudentDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_number' => $this->faker->unique()->numerify('####-#####-BPSU'),
            'level' => $this->faker->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
            'section' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
