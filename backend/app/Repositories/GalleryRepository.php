<?php

namespace App\Repositories;

use App\Models\Gallery;
use App\Models\Invitation;
use App\Repositories\Interfaces\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GalleryRepository implements GalleryRepositoryInterface
{
    public function forInvitation(Invitation $invitation, array $filters = []): Collection
    {
        $query = $invitation->gallery();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->orderBy('sort_order')->get();
    }

    public function countPhotosForInvitation(Invitation $invitation): int
    {
        return $invitation->gallery()->where('type', 'photo')->count();
    }

    public function create(Invitation $invitation, array $data): Gallery
    {
        return $invitation->gallery()->create($data);
    }

    public function createMany(Invitation $invitation, array $items): Collection
    {
        return new Collection(array_map(
            fn (array $item) => $invitation->gallery()->create($item),
            $items,
        ));
    }

    public function update(Gallery $item, array $data): Gallery
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(Gallery $item): void
    {
        $item->delete();
    }
}
