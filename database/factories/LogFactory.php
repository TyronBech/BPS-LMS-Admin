<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $timeIn = $this->faker->dateTimeBetween('-7 months', 'now');
        $timeOut = $this->faker->optional(0.7)->dateTimeInInterval($timeIn, '+8 hours');

        return [
            'user_id'       => $user->id,
            'computer_use'  => $this->faker->randomElement(['Yes', 'No']),
            'time_in'       => $timeIn,
            'time_out'      => $timeOut,
            'remarks'       => null,
        ];
    }
}
