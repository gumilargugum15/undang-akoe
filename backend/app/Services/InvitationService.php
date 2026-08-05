<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(
        private readonly InvitationRepositoryInterface $invitations,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        return $this->invitations->paginate($user, $filters, (int) ($filters['per_page'] ?? 15));
    }

    public function create(User $owner, array $data): Invitation
    {
        $data['user_id'] = $owner->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);
        $data['status'] = 'draft';
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->invitations->create($data);
    }

    public function update(Invitation $invitation, array $data): Invitation
    {
        if (array_key_exists('slug', $data) && $data['slug'] !== $invitation->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $invitation->id);
        }

        return $this->invitations->update($invitation, $data);
    }

    public function delete(Invitation $invitation): void
    {
        $this->invitations->delete($invitation);
    }

    public function publish(Invitation $invitation): Invitation
    {
        if ($invitation->status === 'suspended') {
            throw ValidationException::withMessages([
                'status' => ['Undangan yang ditangguhkan admin tidak dapat dipublikasikan sendiri.'],
            ]);
        }

        return $this->invitations->update($invitation, [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, ['status' => 'draft']);
    }

    public function suspend(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, ['status' => 'suspended']);
    }

    public function reactivate(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, [
            'status' => $invitation->published_at ? 'published' : 'draft',
        ]);
    }

    /**
     * Dedicated theme-switch action (spec: POST/PATCH .../change-theme) — resets any prior
     * per-invitation color/font overrides, since they were tuned against the old theme's token
     * set and could look wrong (or simply be irrelevant) against the new one.
     */
    public function changeTheme(Invitation $invitation, int $themeId): Invitation
    {
        if ($invitation->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['Tema hanya bisa diganti selama undangan masih berstatus draft.'],
            ]);
        }

        return $this->invitations->update($invitation, [
            'theme_id' => $themeId,
            'theme_settings' => null,
        ]);
    }

    private function uniqueSlug(string $source, ?int $exceptId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $attempt = 1;

        while ($this->invitations->slugExists($slug, $exceptId)) {
            $attempt++;
            $slug = "{$base}-{$attempt}";
        }

        return $slug;
    }
}
