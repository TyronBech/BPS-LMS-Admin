<?php

namespace Database\Factories;

use App\Models\VisitorDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitorDetail>
 */
class VisitorDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VisitorDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_org' => $this->faker->company(),
            'purpose' => $this->faker->sentence(),
        ];
    }
}
