<?php

namespace App\Services;

use App\Models\Guestbook;
use App\Models\Invitation;
use App\Repositories\Interfaces\GuestbookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class GuestbookService
{
    public function __construct(
        private readonly GuestbookRepositoryInterface $guestbook,
    ) {}

    public function submitRsvp(Invitation $invitation, array $data): Guestbook
    {
        if (! $invitation->isLive()) {
            throw ValidationException::withMessages([
                'invitation' => ['Undangan ini belum menerima konfirmasi kehadiran.'],
            ]);
        }

        if ($data['attendance'] !== 'hadir') {
            $data['guest_count'] = 1;
        }

        return $this->guestbook->create($invitation, $data);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(Invitation $invitation, array $filters): LengthAwarePaginator
    {
        return $this->guestbook->paginate($invitation, $filters, (int) ($filters['per_page'] ?? 15));
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Invitation $invitation): array
    {
        return $this->guestbook->summary($invitation);
    }

    public function delete(Guestbook $guestbook): void
    {
        $this->guestbook->delete($guestbook);
    }

    public function setApproval(Guestbook $guestbook, bool $approved): Guestbook
    {
        return $this->guestbook->setApproval($guestbook, $approved);
    }

    /**
     * Public "Wall of Love" — 404s the same way submitRsvp's caller does for
     * a draft/suspended invitation, so unpublished undangan stay invisible.
     */
    public function getPublicWall(Invitation $invitation, int $perPage = 15): LengthAwarePaginator
    {
        return $this->guestbook->publicWall($invitation, $perPage);
    }
}
