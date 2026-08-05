<?php

namespace App\Services;

use App\Models\Package;
use App\Repositories\Interfaces\PackageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PackageService
{
    public function __construct(
        private readonly PackageRepositoryInterface $packages,
    ) {}

    /**
     * @return Collection<int, Package>
     */
    public function list(bool $activeOnly): Collection
    {
        return $this->packages->all($activeOnly);
    }

    public function create(array $data): Package
    {
        return $this->packages->create($data);
    }

    public function update(Package $package, array $data): Package
    {
        return $this->packages->update($package, $data);
    }

    /**
     * A package still chosen by existing invitations can't be deleted outright — that would
     * silently null out `invitations.package_id` for customers who already picked it. Deactivating
     * (is_active=false) hides it from new selections without touching what's already assigned.
     */
    public function delete(Package $package): void
    {
        if ($package->invitations()->exists()) {
            throw ValidationException::withMessages([
                'package' => ['Paket tidak dapat dihapus karena masih dipakai oleh satu atau lebih undangan. Nonaktifkan paket ini sebagai gantinya.'],
            ]);
        }

        $this->packages->delete($package);
    }
}
