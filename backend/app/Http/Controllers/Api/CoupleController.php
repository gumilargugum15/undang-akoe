<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Couple\UpsertCoupleRequest;
use App\Http\Resources\CoupleResource;
use App\Models\Invitation;
use App\Services\CoupleService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CoupleController extends Controller
{
    public function __construct(
        private readonly CoupleService $couples,
        private readonly ImageProcessingService $images,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => CoupleResource::collection($this->couples->getForInvitation($invitation)),
        ]);
    }

    public function upsert(UpsertCoupleRequest $request, Invitation $invitation, string $role): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->validated();
        $existing = $invitation->couples()->where('role', $role)->first();

        if ($request->hasFile('photo')) {
            if ($existing?->photo) {
                Storage::disk('public')->delete($existing->photo);
            }

            $data['photo'] = $this->images->storePhoto($request->file('photo'), "couples/{$invitation->id}", maxWidth: 1200);
        }

        $couple = $this->couples->upsert($invitation, $role, $data);

        return response()->json([
            'message' => 'Data mempelai berhasil disimpan.',
            'data' => new CoupleResource($couple),
        ]);
    }

    public function destroy(Invitation $invitation, string $role): JsonResponse
    {
        $this->authorize('update', $invitation);

        $existing = $invitation->couples()->where('role', $role)->first();

        if ($existing?->photo) {
            Storage::disk('public')->delete($existing->photo);
        }

        $this->couples->remove($invitation, $role);

        return response()->json([
            'message' => 'Data mempelai berhasil dihapus.',
        ]);
    }
}
