<?php

namespace App\Repositories\Interfaces;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvitationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(User $user, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Invitation;

    public function update(Invitation $invitation, array $data): Invitation;

    public function delete(Invitation $invitation): void;

    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * Count of this user's other invitations currently counted as "active" (published and
     * not yet expired) — used to enforce a package's `limits.max_active_invitations`.
     */
    public function countActivePublishedForUser(int $userId, ?int $exceptInvitationId = null): int;
}
