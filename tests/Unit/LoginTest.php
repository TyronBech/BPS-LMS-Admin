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
    /**
     * Test login with empty credentials
     */
    public function test_loginEmptyCredentials(): void
    {
        // Act: Attempt login with empty credentials
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        // Assert: Should redirect back with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('email'); // Laravel returns error for 'email' field by default

        // Assert: User should not be authenticated
        $this->assertGuest();
    }
    /**
     * Test login for SQL Injection in email field
     * This test checks if the application is vulnerable to SQL injection attacks through the email field.
     * It attempts to inject SQL code in the email field and checks if the application handles it
     */
    public function test_MysqlInjectionForEmail(): void
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
            'email' => "test@example.com' OR 1=1 --",
            'password' => 'password124',
        ]);

        // Assert: Should redirect back with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('email'); // Laravel returns error for 'email' field by default

        // Assert: User should not be authenticated
        $this->assertGuest();
    }
    /**
     * Test login for SQL Injection in password field
     * This test checks if the application is vulnerable to SQL injection attacks through the password field.
     * It attempts to inject SQL code in the password field and checks if the application handles it
     */
    public function test_MysqlInjectionForPassword(): void
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
            'password' => "password123' OR 1=1 --",
        ]);

        // Assert: Should redirect back with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('password');

        // Assert: User should not be authenticated
        $this->assertGuest();
    }
}
