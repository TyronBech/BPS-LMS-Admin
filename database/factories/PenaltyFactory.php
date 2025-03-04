<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penalty>
 */
class PenaltyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 5),
            'book_id' => $this->faker->numberBetween(1, 50),
            'transaction_type' => $this->faker->randomElement(['late_return', 'lost_book']),
            'penalty_date' => $this->faker->date(),
            'penalty_type' => $this->faker->randomElement(['fine', 'replacement_cost']),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
