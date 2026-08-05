<?php

namespace App\Http\Resources;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Gallery */
class GalleryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'url' => $this->type === 'video_youtube'
                ? $this->external_url
                : ($this->file_path ? Storage::disk('public')->url($this->file_path) : null),
            'thumbnail' => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null,
            'caption' => $this->caption,
            'category' => $this->category,
            'sort_order' => $this->sort_order,
        ];
    }
}
