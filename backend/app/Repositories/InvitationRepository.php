<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\User;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvitationRepository implements InvitationRepositoryInterface
{
    public function paginate(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Invitation::query()->with(['theme:id,name,slug,thumbnail', 'user:id,name,email']);

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['event_category'])) {
            $query->where('event_category', $filters['event_category']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Invitation
    {
        return Invitation::create($data);
    }

    public function update(Invitation $invitation, array $data): Invitation
    {
        $invitation->update($data);

        return $invitation->fresh(['theme', 'package', 'user']);
    }

    public function delete(Invitation $invitation): void
    {
        $invitation->delete();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Invitation::withTrashed()
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    public function countActivePublishedForUser(int $userId, ?int $exceptInvitationId = null): int
    {
        return Invitation::query()
            ->where('user_id', $userId)
            ->where('status', Invitation::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($exceptInvitationId, fn ($q) => $q->where('id', '!=', $exceptInvitationId))
            ->count();
    }
}
