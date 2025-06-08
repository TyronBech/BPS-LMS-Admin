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
            'transaction_id'    => $this->faker->randomElement([64, 67, 69, 73, 74]),
            'penalty_rule_id'   => $this->faker->numberBetween(1, 4),
            'amount'            => $this->faker->randomFloat(5, 50, 100),
        ];
    }
}
