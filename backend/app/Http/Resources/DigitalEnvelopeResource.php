<?php

namespace App\Http\Resources;

use App\Models\DigitalEnvelope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin DigitalEnvelope */
class DigitalEnvelopeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'provider_name' => $this->provider_name,
            'account_number' => $this->account_number,
            'account_holder' => $this->account_holder,
            'qr_image' => $this->qr_image ? Storage::disk('public')->url($this->qr_image) : null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
