<?php

namespace App\Repositories\Interfaces;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

interface PackageRepositoryInterface
{
    /**
     * @return Collection<int, Package>
     */
    public function all(bool $activeOnly): Collection;

    public function create(array $data): Package;

    public function update(Package $package, array $data): Package;

    public function delete(Package $package): void;
}
