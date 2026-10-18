<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_is_disabled_in_production(): void
    {
        config(['app.env' => 'production']);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Public User',
            'email' => 'public@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_initial_owner_command_creates_one_verified_admin(): void
    {
        $variables = [
            'INITIAL_ADMIN_NAME' => 'Store Owner',
            'INITIAL_ADMIN_EMAIL' => 'owner@example.com',
            'INITIAL_ADMIN_PASSWORD' => 'a-unique-production-password',
        ];

        foreach ($variables as $name => $value) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        try {
            $this->artisan('app:create-owner')->assertExitCode(0);
        } finally {
            foreach (array_keys($variables) as $name) {
                putenv($name);
                unset($_ENV[$name], $_SERVER[$name]);
            }
        }

        $owner = User::query()->firstOrFail();
        $this->assertSame('admin', $owner->role);
        $this->assertNotNull($owner->email_verified_at);
        $this->assertTrue(password_verify('a-unique-production-password', $owner->password));
        $this->artisan('app:create-owner')->assertExitCode(1);
        $this->assertDatabaseCount('users', 1);
    }
}
