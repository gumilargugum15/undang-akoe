<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\StoreBannerRequest;
use App\Http\Requests\Banner\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Services\BannerService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct(
        private readonly BannerService $banners,
        private readonly ImageProcessingService $images,
    ) {}

    /**
     * An admin managing banners sees every one (including inactive/expired, to
     * re-enable or reschedule); the public homepage only ever sees live ones.
     */
    public function index(Request $request): JsonResponse
    {
        $liveOnly = ! $request->user()?->isAdmin();

        return response()->json([
            'data' => BannerResource::collection($this->banners->list($liveOnly)),
        ]);
    }

    /**
     * Guest-facing — no auth. Mounted at GET /public/banners.
     */
    public function publicIndex(): JsonResponse
    {
        return response()->json([
            'data' => BannerResource::collection($this->banners->list(true)),
        ]);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $request->safe()->except('image');
        $data['image'] = $this->images->storePhoto($request->file('image'), 'banners', maxWidth: 1920);

        $banner = $this->banners->create($data);

        return response()->json([
            'message' => 'Banner berhasil ditambahkan.',
            'data' => new BannerResource($banner),
        ], 201);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $this->images->storePhoto($request->file('image'), 'banners', maxWidth: 1920);
        }

        $banner = $this->banners->update($banner, $data);

        return response()->json([
            'message' => 'Banner berhasil diperbarui.',
            'data' => new BannerResource($banner),
        ]);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $this->banners->delete($banner);

        return response()->json([
            'message' => 'Banner berhasil dihapus.',
        ]);
    }
}
