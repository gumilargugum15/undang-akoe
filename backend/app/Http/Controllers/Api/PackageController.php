<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Package\StorePackageRequest;
use App\Http\Requests\Package\UpdatePackageRequest;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packages,
    ) {}

    /**
     * An admin sees every package (including inactive, to re-enable later); a customer
     * choosing a package on invitation creation only ever sees the active ones.
     */
    public function index(Request $request): JsonResponse
    {
        $activeOnly = ! $request->user()?->isAdmin();

        return response()->json([
            'data' => PackageResource::collection($this->packages->list($activeOnly)),
        ]);
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = $this->packages->create($request->validated());

        return response()->json([
            'message' => 'Paket berhasil ditambahkan.',
            'data' => new PackageResource($package),
        ], 201);
    }

    public function update(UpdatePackageRequest $request, Package $package): JsonResponse
    {
        $package = $this->packages->update($package, $request->validated());

        return response()->json([
            'message' => 'Paket berhasil diperbarui.',
            'data' => new PackageResource($package),
        ]);
    }

    public function destroy(Package $package): JsonResponse
    {
        $this->packages->delete($package);

        return response()->json([
            'message' => 'Paket berhasil dihapus.',
        ]);
    }
}
