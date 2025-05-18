<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomElement([5, 6, 8, 9, 19, 21, 22, 24, 25, 27]),
            'book_id' => $this->faker->numberBetween(47, 89),
            'transaction_type' => $this->faker->randomElement(['Borrowed', 'Returned', 'Reserved']),
            'date_borrowed' => $this->faker->dateTimeThisYear(),
            'due_date' => $this->faker->dateTimeThisYear(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['Pending', 'Completed', 'Overdue', 'Cancelled', 'Lost', 'Missing']),
            'book_condition' => $this->faker->randomElement(['New', 'Good', 'Fair', 'Poor']),
            'penalty_total' => 0,
            'penalty_status' => $this->faker->randomElement(['Paid', 'Unpaid', 'Waived']),
            'remarks' => "Sample remarks for transaction",
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
