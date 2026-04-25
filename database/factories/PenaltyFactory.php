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
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Penalty $penalty) {
            $transaction = $penalty->transaction;

            if (!$transaction) {
                return;
            }

            $total = (float) $transaction->penalties()->sum('amount');

            $transaction->update([
                'penalty_total' => $total,
                'penalty_status' => $total > 0 ? 'Unpaid' : 'No Penalty',
            ]);
        });
    }

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
