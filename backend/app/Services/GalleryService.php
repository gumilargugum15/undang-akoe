<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Invitation;
use App\Repositories\Interfaces\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
        return $this->gallery->create($invitation, $data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, Gallery>
     */
    public function createMany(Invitation $invitation, array $items): Collection
    {
        return $this->gallery->createMany($invitation, $items);
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
