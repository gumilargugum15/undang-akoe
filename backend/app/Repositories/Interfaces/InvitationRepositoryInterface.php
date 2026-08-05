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
}
