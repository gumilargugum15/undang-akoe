<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BannerRepository implements BannerRepositoryInterface
{
    public function all(bool $liveOnly): Collection
    {
        $query = Banner::query()->orderBy('sort_order');

        if (! $liveOnly) {
            return $query->get();
        }

        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->get();
    }

    public function create(array $data): Banner
    {
        return Banner::create($data);
    }

    public function update(Banner $banner, array $data): Banner
    {
        $banner->update($data);

        return $banner->fresh();
    }

    public function delete(Banner $banner): void
    {
        $banner->delete();
    }
}
