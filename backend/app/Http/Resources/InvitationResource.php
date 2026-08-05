<?php

namespace App\Http\Resources;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Invitation */
class InvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The {invitation} route-model-binding used by every invitation-management
            // endpoint resolves by numeric id, not uuid.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            // The public invitation page is served by the frontend app, not this API —
            // url() would build from APP_URL (this backend's own domain) and produce a
            // broken link.
            'public_url' => rtrim(config('app.frontend_url'), '/').'/'.$this->slug,
            'event_category' => $this->event_category,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            // whenLoaded() already returns null itself when the relation is loaded but empty
            // (package_id is nullable) — it never even calls this closure in that case.
            'package' => $this->whenLoaded('package', fn () => [
                'name' => $this->package->name,
                'slug' => $this->package->slug,
            ]),
            'owner' => $this->when($request->user()?->isAdmin(), fn () => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ])),
            'theme_settings' => $this->theme_settings,
            'cover_photo' => $this->cover_photo ? Storage::disk('public')->url($this->cover_photo) : null,
            'view_count' => $this->view_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
