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
            'user_id' => $this->faker->randomElement([1, 2, 4]),
            'book_id' => $this->faker->numberBetween(1, 50),
            'transaction_type' => $this->faker->randomElement(['Borrow', 'Return']),
            'date_borrowed' => $this->faker->dateTimeThisYear(),
            'due_date' => $this->faker->dateTimeThisYear(),
            'return_date' => $this->faker->dateTimeThisYear(),
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
