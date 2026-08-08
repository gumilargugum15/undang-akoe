<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GuestService
{
    public function __construct(
        private readonly GuestRepositoryInterface $guests,
    ) {}

    /**
     * @return Collection<int, Guest>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->guests->forInvitation($invitation);
    }

    /**
     * Enforces the invitation's package `max_guests` (null = unlimited) — read from the
     * package row rather than checked against a plan name, same as every other package limit
     * in this app (see InvitationService::activate() for max_active_invitations) — plus a
     * blanket check that the invitation's active period (package duration_days) hasn't lapsed.
     */
    public function create(Invitation $invitation, array $data): Guest
    {
        if ($invitation->hasExpired()) {
            throw ValidationException::withMessages([
                'invitation' => ['Masa aktif undangan sudah berakhir. Perpanjang paket untuk menambah tamu baru.'],
            ]);
        }

        $maxGuests = $invitation->package?->max_guests;

        if ($maxGuests !== null && $this->guests->countForInvitation($invitation) >= $maxGuests) {
            throw ValidationException::withMessages([
                'guests' => ["Batas maksimal {$maxGuests} tamu untuk paket ini sudah tercapai. Upgrade paket untuk menambah lebih banyak tamu."],
            ]);
        }

        return $this->guests->create($invitation, $data);
    }

    public function delete(Guest $guest): void
    {
        $this->guests->delete($guest);
    }

    public function findByToken(Invitation $invitation, string $token): ?Guest
    {
        return $this->guests->findByToken($invitation, $token);
    }
}
