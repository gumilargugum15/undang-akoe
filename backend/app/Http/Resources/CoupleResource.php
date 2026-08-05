<?php

namespace App\Http\Resources;

use App\Models\Couple;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Couple */
class CoupleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'role' => $this->role,
            'nickname' => $this->nickname,
            'full_name' => $this->full_name,
            'parent_name' => $this->parent_name,
            'instagram_handle' => $this->instagram_handle,
            'photo' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ];
    }
}
