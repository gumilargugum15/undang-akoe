<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DigitalEnvelope\StoreDigitalEnvelopeRequest;
use App\Http\Requests\DigitalEnvelope\UpdateDigitalEnvelopeRequest;
use App\Http\Resources\DigitalEnvelopeResource;
use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use App\Services\DigitalEnvelopeService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DigitalEnvelopeController extends Controller
{
    public function __construct(
        private readonly DigitalEnvelopeService $envelopes,
        private readonly ImageProcessingService $images,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => DigitalEnvelopeResource::collection($this->envelopes->getForInvitation($invitation)),
        ]);
    }

    /**
     * Public — no auth. Guests need this to actually send a gift. 404s the
     * same way the RSVP/guestbook endpoints do for a draft/suspended undangan.
     */
    public function publicIndex(Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->isLive(), 404);

        return response()->json([
            'data' => DigitalEnvelopeResource::collection($this->envelopes->getPublicList($invitation)),
        ]);
    }

    public function store(StoreDigitalEnvelopeRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->safe()->except('qr_image');

        if ($data['type'] === 'qris') {
            $data['provider_name'] ??= 'QRIS';
        }

        if ($request->hasFile('qr_image')) {
            $data['qr_image'] = $this->images->storeLosslessImage($request->file('qr_image'), "envelopes/{$invitation->id}");
        }

        $envelope = $this->envelopes->create($invitation, $data);

        return response()->json([
            'message' => 'Amplop digital berhasil ditambahkan.',
            'data' => new DigitalEnvelopeResource($envelope),
        ], 201);
    }

    public function update(UpdateDigitalEnvelopeRequest $request, Invitation $invitation, DigitalEnvelope $envelope): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $envelope);

        $data = $request->safe()->except('qr_image');

        if ($request->hasFile('qr_image')) {
            if ($envelope->qr_image) {
                Storage::disk('public')->delete($envelope->qr_image);
            }

            $data['qr_image'] = $this->images->storeLosslessImage($request->file('qr_image'), "envelopes/{$invitation->id}");
        }

        $envelope = $this->envelopes->update($envelope, $data);

        return response()->json([
            'message' => 'Amplop digital berhasil diperbarui.',
            'data' => new DigitalEnvelopeResource($envelope),
        ]);
    }

    public function destroy(Invitation $invitation, DigitalEnvelope $envelope): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $envelope);

        if ($envelope->qr_image) {
            Storage::disk('public')->delete($envelope->qr_image);
        }

        $this->envelopes->delete($envelope);

        return response()->json([
            'message' => 'Amplop digital berhasil dihapus.',
        ]);
    }

    private function assertBelongsToInvitation(Invitation $invitation, DigitalEnvelope $envelope): void
    {
        abort_unless($envelope->invitation_id === $invitation->id, 404);
    }
}
