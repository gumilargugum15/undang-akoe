<?php

namespace App\Repositories;

use App\Models\Package;
use App\Repositories\Interfaces\PackageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PackageRepository implements PackageRepositoryInterface
{
    public function all(bool $activeOnly): Collection
    {
        return Package::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->get();
    }

    public function create(array $data): Package
    {
        return Package::create($data);
    }

    public function update(Package $package, array $data): Package
    {
        $package->update($data);

        return $package->fresh();
    }

    public function delete(Package $package): void
    {
        $package->delete();
    }
}
