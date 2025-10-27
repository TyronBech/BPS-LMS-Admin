<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FetchDataTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic unit test example.
     */
    public function test_fetchCurrentTimeInUsers(): void
    {
        
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');

        Log::factory()->create([
            'user_id'       => 106,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now(),
            'time_out' => null
        ]);
        Log::factory()->create([
            'user_id'       => 106,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now()->subDay(), // yesterday
            'time_out' => null
        ]);
        Log::factory()->create([
            'user_id'       => 106,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now(),
            'time_out' => Carbon::now()
        ]);

        // Act
        $response = $this->getJson(route('fetch-current-count'));

        // Assert
        $response->assertOk()
                 ->assertJson(['active_count' => 1]); // only 1 user matches
    }
}
