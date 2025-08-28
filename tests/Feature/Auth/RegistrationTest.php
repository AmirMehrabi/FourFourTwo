<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser123',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_requires_username(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_username_must_be_unique(): void
    {
        // Create a user with a username
        $this->post('/register', [
            'name' => 'First User',
            'username' => 'testuser',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Logout the first user
        $this->post('/logout');

        // Try to register another user with the same username
        $response = $this->post('/register', [
            'name' => 'Second User',
            'username' => 'testuser',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_username_validation_rules(): void
    {
        // Test minimum length
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'ab',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }
}
