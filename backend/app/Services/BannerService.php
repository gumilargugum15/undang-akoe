<?php

namespace App\Services;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BannerService
{
    public function __construct(
        private readonly BannerRepositoryInterface $banners,
    ) {}

    /**
     * @return Collection<int, Banner>
     */
    public function list(bool $liveOnly): Collection
    {
        return $this->banners->all($liveOnly);
    }

    public function create(array $data): Banner
    {
        return $this->banners->create($data);
    }

    public function update(Banner $banner, array $data): Banner
    {
        return $this->banners->update($banner, $data);
    }

    public function delete(Banner $banner): void
    {
        $this->banners->delete($banner);
    }
}
