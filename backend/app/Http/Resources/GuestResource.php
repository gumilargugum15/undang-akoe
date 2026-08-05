<?php

namespace App\Http\Resources;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Guest */
class GuestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'category' => $this->category,
            // The frontend builds the shareable link itself (invitation.public_url + this
            // token) rather than this resource composing a full URL — keeps Guest reads free
            // of an extra `invitation` eager load just to read its slug.
            'slug_token' => $this->slug_token,
            'is_checked_in' => $this->is_checked_in,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
