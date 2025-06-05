<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use phpDocumentor\Reflection\Types\Nullable;

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
            'user_id'       => $this->faker->randomElement([5, 6, 8, 9, 19, 21, 22, 24, 25, 27, 29, 30]),
            'computer_use'  => $this->faker->randomElement(['Yes', 'No']),
            'time_in'       => $this->faker->dateTimeBetween('-1 month', 'now'),
            'time_out'      => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'remarks'       => null,
        ];
    }
}
