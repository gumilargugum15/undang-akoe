<?php

namespace App\Repositories\Interfaces;

use App\Models\Guestbook;
use App\Models\Invitation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GuestbookRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(Invitation $invitation, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(Invitation $invitation, array $data): Guestbook;

    /**
     * @return array<string, int>
     */
    public function summary(Invitation $invitation): array;

    public function delete(Guestbook $guestbook): void;

    public function setApproval(Guestbook $guestbook, bool $approved): Guestbook;

    /**
     * Public "Wall of Love" — approved entries only.
     */
    public function publicWall(Invitation $invitation, int $perPage = 15): LengthAwarePaginator;
}
