<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoveStory\StoreLoveStoryRequest;
use App\Http\Requests\LoveStory\UpdateLoveStoryRequest;
use App\Http\Resources\LoveStoryResource;
use App\Models\Invitation;
use App\Models\LoveStory;
use App\Services\ImageProcessingService;
use App\Services\LoveStoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class LoveStoryController extends Controller
{
    public function __construct(
        private readonly LoveStoryService $stories,
        private readonly ImageProcessingService $images,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => LoveStoryResource::collection($this->stories->getForInvitation($invitation)),
        ]);
    }

    public function store(StoreLoveStoryRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->images->storePhoto($request->file('photo'), "love-stories/{$invitation->id}", maxWidth: 1600);
        }

        $story = $this->stories->create($invitation, $data);

        return response()->json([
            'message' => 'Cerita cinta berhasil ditambahkan.',
            'data' => new LoveStoryResource($story),
        ], 201);
    }

    public function update(UpdateLoveStoryRequest $request, Invitation $invitation, LoveStory $story): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $story);

        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            if ($story->photo) {
                Storage::disk('public')->delete($story->photo);
            }
            $data['photo'] = $this->images->storePhoto($request->file('photo'), "love-stories/{$invitation->id}", maxWidth: 1600);
        }

        $story = $this->stories->update($story, $data);

        return response()->json([
            'message' => 'Cerita cinta berhasil diperbarui.',
            'data' => new LoveStoryResource($story),
        ]);
    }

    public function destroy(Invitation $invitation, LoveStory $story): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $story);

        if ($story->photo) {
            Storage::disk('public')->delete($story->photo);
        }

        $this->stories->delete($story);

        return response()->json([
            'message' => 'Cerita cinta berhasil dihapus.',
        ]);
    }

    private function assertBelongsToInvitation(Invitation $invitation, LoveStory $story): void
    {
        abort_unless($story->invitation_id === $invitation->id, 404);
    }
}
