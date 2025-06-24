<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * Test login with correct credentials
     */
    public function test_loginCorrectCredentials(): void
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'rfid'          => '1234567890',
            'first_name'    => 'Test',
            'last_name'     => 'User',
            'gender'        => 'Male',
            'email'         => 'test@example.com',
            'password'      => bcrypt('password123'),
        ]);

        // Act: Attempt login with correct credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert: Check if redirected to intended route (default: /home)
        $response->assertRedirect('/admin/dashboard');

        // Assert: Check that user is authenticated
        $this->assertAuthenticatedAs($user);
    }
    /**
     * Test login with wrong credentials
     */
    public function test_loginWrongCredentials(): void
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'rfid'          => '1234567890',
            'first_name'    => 'Test',
            'last_name'     => 'User',
            'gender'        => 'Male',
            'email'         => 'test@example.com',
            'password'      => bcrypt('password123'),
        ]);

        // Act: Attempt login with correct credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password124',
        ]);

        // Assert: Should redirect back with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('email'); // Laravel returns error for 'email' field by default

        // Assert: User should not be authenticated
        $this->assertGuest();
    }
}
