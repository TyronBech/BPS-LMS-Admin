<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected static $incrementingId = 1;
    public function definition(): array
    {
        return [
            'accession' => 'ACC-' . str_pad(self::$incrementingId++, 6, '0', STR_PAD_LEFT),
            'call_number' => strtoupper(Str::random(3)) . ' ' . rand(100, 999) . '.' . rand(10, 99) . ' ' . Str::random(2),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'edition' => $this->faker->numberBetween(1, 10),
            'place_of_publication' => $this->faker->city(),
            'publisher' => $this->faker->company(),
            'copyrights' => $this->faker->year(),
            'remarks' => $this->faker->randomElement(['On Shelf', 'Lost', 'Missing']),
            'category_id' => $this->faker->numberBetween(1, 5),
            'cover_image' => 'default.jpg',
            'digital_copy_url' => $this->faker->url(),
            'barcode' => $this->faker->ean13(),
            'availability_status' => $this->faker->randomElement(['Available', 'Borrowed', 'In Use', 'Reserved']),
            'condition_status' => $this->faker->randomElement(['New', 'Good', 'Fair', 'Poor']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
