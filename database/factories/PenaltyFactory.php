<?php

namespace Database\Factories;

use App\Models\PenaltyRule;
use App\Models\Transaction;
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
            'transaction_id'    => $this->faker->randomElement(Transaction::pluck('id')->toArray()),
            'penalty_rule_id'   => $this->faker->randomElement(PenaltyRule::pluck('id')->toArray()),
            'amount'            => $this->faker->randomFloat(5, 50, 100),
        ];
    }
}