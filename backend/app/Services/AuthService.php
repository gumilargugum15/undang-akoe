<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Number of days a token stays valid when "remember" is requested at login.
     */
    private const REMEMBER_TOKEN_DAYS = 30;

    /**
     * Number of days a token stays valid on a normal (non-remembered) login.
     */
    private const DEFAULT_TOKEN_DAYS = 1;

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(array $data): array
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
        ]);

        event(new Registered($user));

        return $this->issueToken($user, remember: false);
    }

    public function login(array $credentials, bool $remember = false): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Hubungi admin untuk bantuan.'],
            ]);
        }

        return $this->issueToken($user, $remember);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function forgotPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $this->users->update($user, ['password' => Hash::make($password)]);
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->users->update($user, array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ], fn ($value) => ! is_null($value)));
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini tidak sesuai.'],
            ]);
        }

        $this->users->update($user, ['password' => Hash::make($newPassword)]);

        // Sign out every other session/device; the token used for this very request stays valid.
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
    }

    private function issueToken(User $user, bool $remember): array
    {
        $expiresAt = now()->addDays($remember ? self::REMEMBER_TOKEN_DAYS : self::DEFAULT_TOKEN_DAYS);

        $token = $user->createToken('auth_token-'.Str::random(8), ['*'], $expiresAt);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'expires_at' => $expiresAt,
        ];
    }
}
