<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    /**
     * The app is Bearer-token-only (see Phase 3's architecture decision — no
     * stateful SPA cookie auth), so tests authenticate the same way real
     * clients do: a real Sanctum PersonalAccessToken, not actingAs()'s
     * TransientToken stand-in. That stand-in has no real `id`, which breaks
     * any code path touching currentAccessToken()->id (e.g. changePassword()).
     *
     * Auth::forgetGuards() is required when a test makes requests as two
     * different users in a row (e.g. asserting a stranger gets 403, then the
     * real owner gets 200) — the sanctum guard otherwise caches the first
     * request's resolved user and silently reuses it for the second one.
     */
    protected function apiAs(User $user): static
    {
        Auth::forgetGuards();

        $token = $user->createToken('test')->plainTextToken;

        // Accept: application/json matters beyond content-negotiation politeness here — it's what
        // makes Laravel return a 422 JSON body on validation failure instead of a 302 redirect.
        // postJson()/putJson() set it for you, but file uploads must use plain post()/put() (JSON
        // helpers encode the whole body, which breaks multipart), so it has to be set explicitly.
        return $this->withHeader('Authorization', "Bearer {$token}")
            ->withHeader('Accept', 'application/json');
    }
}
