<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Honoree\StoreHonoreeRequest;
use App\Http\Requests\Honoree\UpdateHonoreeRequest;
use App\Http\Resources\HonoreeResource;
use App\Models\Honoree;
use App\Models\Invitation;
use App\Services\HonoreeService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HonoreeController extends Controller
{
    public function __construct(
        private readonly HonoreeService $honorees,
        private readonly ImageProcessingService $images,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => HonoreeResource::collection($this->honorees->getForInvitation($invitation)),
        ]);
    }

    public function store(StoreHonoreeRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->images->storePhoto($request->file('photo'), "honorees/{$invitation->id}", maxWidth: 1600);
        }

        $honoree = $this->honorees->create($invitation, $data);

        return response()->json([
            'message' => 'Data yang dirayakan berhasil ditambahkan.',
            'data' => new HonoreeResource($honoree),
        ], 201);
    }

    public function update(UpdateHonoreeRequest $request, Invitation $invitation, Honoree $honoree): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $honoree);

        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            if ($honoree->photo) {
                Storage::disk('public')->delete($honoree->photo);
            }
            $data['photo'] = $this->images->storePhoto($request->file('photo'), "honorees/{$invitation->id}", maxWidth: 1600);
        }

        $honoree = $this->honorees->update($honoree, $data);

        return response()->json([
            'message' => 'Data yang dirayakan berhasil diperbarui.',
            'data' => new HonoreeResource($honoree),
        ]);
    }

    public function destroy(Invitation $invitation, Honoree $honoree): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $honoree);

        if ($honoree->photo) {
            Storage::disk('public')->delete($honoree->photo);
        }

        $this->honorees->delete($honoree);

        return response()->json([
            'message' => 'Data yang dirayakan berhasil dihapus.',
        ]);
    }

    private function assertBelongsToInvitation(Invitation $invitation, Honoree $honoree): void
    {
        abort_unless($honoree->invitation_id === $invitation->id, 404);
    }
}
