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

    public function create(Invitation $invitation, array $data): Gallery;

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, Gallery>
     */
    public function createMany(Invitation $invitation, array $items): Collection;

    public function update(Gallery $item, array $data): Gallery;

    public function delete(Gallery $item): void;
}
