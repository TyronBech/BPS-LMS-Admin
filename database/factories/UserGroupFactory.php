<?php

namespace Database\Factories;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserGroup>
 */
class UserGroupFactory extends Factory
{
  protected $model = UserGroup::class;

  public function definition(): array
  {
    $preset = $this->faker->randomElement([
      [
        'user_type' => 'student',
        'category' => 'Regular Student',
        'max_book_allowed' => 3,
        'duration_type' => 'standard',
        'renewal_limit' => 2,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Teacher',
        'max_book_allowed' => 5,
        'duration_type' => 'standard',
        'renewal_limit' => 3,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Staff',
        'max_book_allowed' => 4,
        'duration_type' => 'standard',
        'renewal_limit' => 2,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Librarian',
        'max_book_allowed' => 10,
        'duration_type' => 'unlimited',
        'renewal_limit' => 5,
      ],
      [
        'user_type' => 'visitor',
        'category' => 'Guest Visitor',
        'max_book_allowed' => 1,
        'duration_type' => 'none',
        'renewal_limit' => 0,
      ],
    ]);

    return $preset;
  }
}
