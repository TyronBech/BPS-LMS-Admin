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

        $initialActiveCount = \App\Models\Log::where('time_in', '!=', null)
            ->where('time_out', null)
            ->whereDate('time_in', Carbon::today())
            ->count();

        Log::factory()->create([
            'user_id'       => $user->id,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now(),
            'time_out' => null
        ]);
        Log::factory()->create([
            'user_id'       => $user->id,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now()->subDay(), // yesterday
            'time_out' => null
        ]);
        Log::factory()->create([
            'user_id'       => $user->id,
            'computer_use'  => 'Yes',
            'time_in' => Carbon::now(),
            'time_out' => Carbon::now()
        ]);

        // Act
        $response = $this->getJson(route('fetch-current-count'));

        // Assert
        $response->assertOk()
                 ->assertJson(['active_count' => $initialActiveCount + 1]); // increases by 1
    }
}
