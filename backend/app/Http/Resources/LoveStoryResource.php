<?php

namespace App\Http\Resources;

use App\Models\LoveStory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin LoveStory */
class LoveStoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'story_date' => $this->story_date?->toDateString(),
            'description' => $this->description,
            'photo' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'sort_order' => $this->sort_order,
        ];
    }
}
