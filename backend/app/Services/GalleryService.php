<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Invitation;
use App\Repositories\Interfaces\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GalleryService
{
    public function __construct(
        private readonly GalleryRepositoryInterface $gallery,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Gallery>
     */
    public function getForInvitation(Invitation $invitation, array $filters = []): Collection
    {
        return $this->gallery->forInvitation($invitation, $filters);
    }

    public function create(Invitation $invitation, array $data): Gallery
    {
        if (($data['type'] ?? null) === 'photo') {
            $this->assertCanAddPhotos($invitation, count: 1);
        }

        return $this->gallery->create($invitation, $data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, Gallery>
     */
    public function createMany(Invitation $invitation, array $items): Collection
    {
        // storeBulk (the only caller) is photo-only, unlike the single-item create() above.
        $this->assertCanAddPhotos($invitation, count: count($items));

        return $this->gallery->createMany($invitation, $items);
    }

    /**
     * Enforces the invitation's package `max_photos` (null = unlimited) and active period —
     * same pattern as GuestService::create()'s max_guests/duration checks. $count lets a bulk
     * upload be rejected as a whole rather than partially succeeding past the cap.
     */
    private function assertCanAddPhotos(Invitation $invitation, int $count): void
    {
        if ($invitation->hasExpired()) {
            throw ValidationException::withMessages([
                'invitation' => ['Masa aktif undangan sudah berakhir. Perpanjang paket untuk menambah foto baru.'],
            ]);
        }

        $maxPhotos = $invitation->package?->max_photos;

        if ($maxPhotos === null) {
            return;
        }

        $existing = $this->gallery->countPhotosForInvitation($invitation);

        if ($existing + $count > $maxPhotos) {
            throw ValidationException::withMessages([
                'gallery' => ["Batas maksimal {$maxPhotos} foto untuk paket ini sudah tercapai. Upgrade paket untuk menambah lebih banyak foto."],
            ]);
        }
    }

    public function update(Gallery $item, array $data): Gallery
    {
        return $this->gallery->update($item, $data);
    }

    public function delete(Gallery $item): void
    {
        $this->gallery->delete($item);
    }
}
