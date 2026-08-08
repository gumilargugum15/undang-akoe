<?php

namespace App\Repositories\Interfaces;

use App\Models\Gallery;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface GalleryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Gallery>
     */
    public function forInvitation(Invitation $invitation, array $filters = []): Collection;

    /**
     * Counts only type=photo items — videos (mp4/YouTube) don't count against a package's
     * max_photos, which is specifically a photo limit.
     */
    public function countPhotosForInvitation(Invitation $invitation): int;

    public function create(Invitation $invitation, array $data): Gallery;

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, Gallery>
     */
    public function createMany(Invitation $invitation, array $items): Collection;

    public function update(Gallery $item, array $data): Gallery;

    public function delete(Gallery $item): void;
}
