<?php

namespace App\Http\Resources;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Theme */
class ThemeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Needed by every caller, not just admins — a customer submits this as
            // `theme_id` when creating/changing an invitation's theme.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'thumbnail' => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null,
            'type' => $this->type,
            // A customer choosing between free/premium themes needs to see the price
            // before picking one — this was previously (wrongly) admin-gated.
            'price' => (float) $this->price,
            'status' => $this->status,
            'supports_dark_mode' => $this->supports_dark_mode,
            'config' => $this->config,

            // Management-only fields — a customer picking a theme doesn't need these.
            $this->mergeWhen($request->user()?->isAdmin(), [
                'theme_category_id' => $this->theme_category_id,
                'banner_preview' => $this->banner_preview ? Storage::disk('public')->url($this->banner_preview) : null,
                'screenshots' => $this->screenshots,
                'version' => $this->version,
                'author' => $this->author,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
                'created_at' => $this->created_at?->toIso8601String(),
            ]),
        ];
    }
}
