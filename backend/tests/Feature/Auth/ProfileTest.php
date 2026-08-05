<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_cannot_view_the_profile(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    #[Test]
    public function an_authenticated_user_can_view_and_update_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->apiAs($user)->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Old Name');

        $this->apiAs($user)->putJson('/api/profile', ['name' => 'New Name', 'phone' => '081234567890'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.phone', '081234567890');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    #[Test]
    public function changing_password_requires_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        $this->apiAs($user)->putJson('/api/profile/password', [
            'current_password' => 'wrong',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['current_password']);
    }

    #[Test]
    public function a_user_can_change_their_password_and_login_with_the_new_one(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        $this->apiAs($user)->putJson('/api/profile/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ])->assertOk();
    }

    #[Test]
    public function changing_password_revokes_other_sessions_but_keeps_the_current_one(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);
        $user->createToken('other-device');

        $this->apiAs($user)->putJson('/api/profile/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        // The "other-device" token is gone; only the one apiAs() just created for this request remains.
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
