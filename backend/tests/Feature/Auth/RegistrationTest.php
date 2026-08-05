<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_new_user_can_register_and_receives_a_customer_role(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'budi@example.com')
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonPath('data.user.is_active', true)
            ->assertJsonStructure(['data' => ['user', 'token', 'expires_at']]);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'customer']);

        $user = User::where('email', 'budi@example.com')->first();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    #[Test]
    public function registration_fails_with_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'exists@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Duplicate',
            'email' => 'exists@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Budi',
            'email' => 'budi2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}
