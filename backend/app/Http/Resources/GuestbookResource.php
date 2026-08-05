<?php

namespace App\Http\Resources;

use App\Models\Guestbook;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Guestbook */
class GuestbookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The {rsvp} route-model-binding used by approve/reject/destroy resolves
            // by numeric id, not uuid.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'guest_name' => $this->guest_name,
            'attendance' => $this->attendance,
            'guest_count' => $this->guest_count,
            'message' => $this->message,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
