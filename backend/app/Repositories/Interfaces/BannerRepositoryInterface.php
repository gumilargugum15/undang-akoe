<?php

namespace App\Repositories\Interfaces;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;

interface BannerRepositoryInterface
{
    /**
     * @return Collection<int, Banner>
     */
    public function all(bool $liveOnly): Collection;

    public function create(array $data): Banner;

    public function update(Banner $banner, array $data): Banner;

    public function delete(Banner $banner): void;
}
