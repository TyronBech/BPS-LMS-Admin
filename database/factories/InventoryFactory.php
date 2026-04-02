<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $book = Book::inRandomOrder()->first() ?? Book::factory()->create();

        return [
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
