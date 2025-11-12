<?php

namespace Database\Factories;

use App\Models\EmployeeDetail;
use App\Models\StudentDetail;
use App\Models\User;
use App\Models\VisitorDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfid' => $this->faker->unique()->ean13(),
            'privilege_id' => $this->faker->numberBetween(1, 5),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->lastName(),
            'last_name' => $this->faker->lastName(),
            'suffix' => $this->faker->optional()->suffix(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'profile_image' => 'default.jpg',
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('secret'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if ($user->privilege_id == 1) {
                StudentDetail::factory()->create(['user_id' => $user->id]);
            } elseif (in_array($user->privilege_id, [2, 3, 4])) {
                EmployeeDetail::factory()->create(['user_id' => $user->id]);
            } elseif ($user->privilege_id == 5) {
                VisitorDetail::factory()->create([
                    'user_id' => $user->id,
                    'gender' => $user->gender
                ]);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
