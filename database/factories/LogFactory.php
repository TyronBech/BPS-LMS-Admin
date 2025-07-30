<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use phpDocumentor\Reflection\Types\Nullable;
use App\Models\User;

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
            'user_id'       => $this->faker->randomElement(User::pluck('id')->toArray()),
            'computer_use'  => $this->faker->randomElement(['Yes', 'No']),
            'time_in'       => $this->faker->dateTimeBetween('-1 month', 'now'),
            'time_out'      => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'remarks'       => null,
        ];
    }
}
