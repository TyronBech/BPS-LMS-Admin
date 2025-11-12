<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
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
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $book = Book::inRandomOrder()->first() ?? Book::factory()->create();

        $transactionType = $this->faker->randomElement(['Borrowed', 'Returned', 'Reserved']);
        $dateBorrowed = null;
        $dueDate = null;
        $returnDate = null;
        $reservedDate = null;
        $status = 'Pending';

        switch ($transactionType) {
            case 'Borrowed':
                $dateBorrowed = $this->faker->dateTimeThisYear();
                $dueDate = $this->faker->dateTimeInInterval($dateBorrowed, '+14 days');
                $status = $this->faker->randomElement(['Pending', 'Overdue', 'Lost', 'Missing']);
                break;
            case 'Returned':
                $dateBorrowed = $this->faker->dateTimeThisYear();
                $returnDate = $this->faker->dateTimeInInterval($dateBorrowed, '+14 days');
                $dueDate = $this->faker->dateTimeInInterval($dateBorrowed, '+7 days');
                $status = 'Completed';
                break;
            case 'Reserved':
                $reservedDate = $this->faker->dateTimeThisYear();
                $status = $this->faker->randomElement(['Pending', 'Cancelled']);
                break;
        }


        return [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'date_borrowed' => $dateBorrowed,
            'due_date' => $dueDate,
            'return_date' => $returnDate,
            'reserved_date' => $reservedDate,
            'transaction_type' => $transactionType,
            'status' => $status,
            'book_condition' => $this->faker->randomElement(['New', 'Good', 'Fair', 'Poor']),
            'penalty_total' => 0,
            'penalty_status' => $this->faker->randomElement(['Paid', 'Unpaid', 'Waived']),
            'remarks' => "Sample remarks for transaction",
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
