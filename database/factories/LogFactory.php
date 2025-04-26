<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => $this->faker->randomElement([5, 6, 30, 34, 35, 47]),
            'computer_use'  => $this->faker->randomElement(['Yes', 'No']),
            'timestamp'     => $this->faker->dateTimeThisYear(),
            'action'        => $this->faker->randomElement(['Time in', 'Time out']),
        ];
    }
}
