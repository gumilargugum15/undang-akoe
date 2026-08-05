<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\StoreGalleryBulkRequest;
use App\Http\Requests\Gallery\StoreGalleryItemRequest;
use App\Http\Requests\Gallery\UpdateGalleryItemRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Models\Invitation;
use App\Services\GalleryService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function __construct(
        private readonly GalleryService $gallery,
        private readonly ImageProcessingService $images,
    ) {}

    public function index(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        $items = $this->gallery->getForInvitation($invitation, $request->only(['type', 'category']));

        return response()->json([
            'data' => GalleryResource::collection($items),
        ]);
    }

    public function store(StoreGalleryItemRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->safe()->except('file');

        if ($request->hasFile('file')) {
            $data['file_path'] = $data['type'] === 'video_mp4'
                ? $request->file('file')->store("gallery/videos/{$invitation->id}", 'public')
                : $this->images->storePhoto($request->file('file'), "gallery/photos/{$invitation->id}", maxWidth: 1920);
        }

        $item = $this->gallery->create($invitation, $data);

        return response()->json([
            'message' => 'Item galeri berhasil ditambahkan.',
            'data' => new GalleryResource($item),
        ], 201);
    }

    public function storeBulk(StoreGalleryBulkRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $items = array_map(fn ($file) => [
            'type' => 'photo',
            'file_path' => $this->images->storePhoto($file, "gallery/photos/{$invitation->id}", maxWidth: 1920),
            'category' => $request->validated('category'),
        ], $request->file('photos'));

        $created = $this->gallery->createMany($invitation, $items);

        return response()->json([
            'message' => count($created).' foto berhasil diunggah.',
            'data' => GalleryResource::collection($created),
        ], 201);
    }

    public function update(UpdateGalleryItemRequest $request, Invitation $invitation, Gallery $item): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $item);

        $item = $this->gallery->update($item, $request->validated());

        return response()->json([
            'message' => 'Item galeri berhasil diperbarui.',
            'data' => new GalleryResource($item),
        ]);
    }

    public function destroy(Invitation $invitation, Gallery $item): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $item);

        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        $this->gallery->delete($item);

        return response()->json([
            'message' => 'Item galeri berhasil dihapus.',
        ]);
    }

    private function assertBelongsToInvitation(Invitation $invitation, Gallery $item): void
    {
        abort_unless($item->invitation_id === $invitation->id, 404);
    }
}
