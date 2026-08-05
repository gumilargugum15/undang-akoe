<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\UpsertMusicRequest;
use App\Http\Resources\MusicResource;
use App\Models\Invitation;
use App\Services\MusicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    public function __construct(
        private readonly MusicService $music,
    ) {}

    public function show(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        $music = $this->music->getForInvitation($invitation);

        return response()->json([
            'data' => $music ? new MusicResource($music) : null,
        ]);
    }

    public function upsert(UpsertMusicRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->safe()->except('file');
        $newFilePath = null;

        if ($data['source'] === 'upload' && $request->hasFile('file')) {
            $existing = $this->music->getForInvitation($invitation);
            if ($existing?->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $newFilePath = $request->file('file')->store("music/{$invitation->id}", 'public');
        }

        $music = $this->music->upsert($invitation, $data, $newFilePath);

        return response()->json([
            'message' => 'Musik berhasil disimpan.',
            'data' => new MusicResource($music),
        ]);
    }

    public function destroy(Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $existing = $this->music->getForInvitation($invitation);
        if ($existing?->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $this->music->delete($invitation);

        return response()->json([
            'message' => 'Musik berhasil dihapus.',
        ]);
    }
}
