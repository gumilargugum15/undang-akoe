<?php

namespace App\Http\Resources;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Music */
class MusicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'source' => $this->source,
            'title' => $this->title,
            'artist' => $this->artist,
            'url' => $this->source === 'upload'
                ? ($this->file_path ? Storage::disk('public')->url($this->file_path) : null)
                : $this->external_url,
            'autoplay' => $this->autoplay,
            'is_loop' => $this->is_loop,
            'is_active' => $this->is_active,
        ];
    }
}
