<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
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
            'reservation_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['Available', 'Borrowed', 'In Use', 'Reserved']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
