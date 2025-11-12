<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['legend' => 'CT', 'name' => 'Coffee Table'],
            ['legend' => 'EBCLE', 'name' => 'Christian Living Education'],
            ['legend' => 'FIC', 'name' => 'Fiction'],
            ['legend' => 'GR', 'name' => 'General Reference'],
            ['legend' => 'REF', 'name' => 'Reference'],
            ['legend' => 'TRF', 'name' => "Teacher's Reference-Filipiniana"],
            ['legend' => 'TREF', 'name' => "Teacher's Reference"],
            ['legend' => 'REV', 'name' => 'Reviewers'],
            ['legend' => 'COM', 'name' => 'Comics'],
            ['legend' => 'JHS', 'name' => 'Junior High School'],
            ['legend' => 'SHS', 'name' => 'Senior High School'],
            ['legend' => 'RW', 'name' => 'Research Works'],
            ['legend' => 'TG', 'name' => "Teacher's Guide"],
            ['legend' => 'EBCOOK', 'name' => 'Cookery'],
            ['legend' => 'TRY', 'name' => 'Testing'],
        ];

        $category = $this->faker->unique()->randomElement($categories);

        return [
            'legend' => $category['legend'],
            'name' => $category['name'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
