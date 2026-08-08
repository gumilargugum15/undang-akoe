<?php

namespace App\Http\Resources;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Everything the public invitation page needs in one shot: core content,
 * the couple's resolved theme (base config + their overrides, already
 * merged), and SEO tags for the SSR <head>. Guestbook wall, envelopes, and
 * RSVP submission stay on their own dedicated public endpoints (Phase 9/10) —
 * paginated/mutating concerns that don't belong bundled into a page-load read.
 *
 * @mixin Invitation
 */
class PublicInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $config = $this->resolvedThemeConfig();
        $coverPhoto = $this->cover_photo ? Storage::disk('public')->url($this->cover_photo) : null;
        // Falls back to the opening-gate cover photo when no separate one is set — most
        // invitations only ever upload one photo, so the Home section still gets a header.
        $homeCoverPhoto = $this->home_cover_photo ? Storage::disk('public')->url($this->home_cover_photo) : $coverPhoto;

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'event_category' => $this->event_category,
            'cover_photo' => $coverPhoto,
            'home_cover_photo' => $homeCoverPhoto,
            'theme' => [
                'id' => $this->theme->slug,
                'name' => $this->theme->name,
                'tagline' => $this->theme->description,
                ...$config,
            ],
            // Always eager-loaded in the controller, so there's no "not loaded yet" case to guard
            // against here — just a possibly-nonexistent row (no SEO configured for this invitation
            // yet), which the null-safe operator already handles.
            'seo' => [
                'meta_title' => $this->seo?->meta_title,
                'meta_description' => $this->seo?->meta_description,
                'og_image' => $this->seo?->og_image ? Storage::disk('public')->url($this->seo->og_image) : null,
                'favicon' => $this->seo?->favicon ? Storage::disk('public')->url($this->seo->favicon) : null,
            ],
            'couples' => [
                'groom' => ($groom = $this->couples->firstWhere('role', 'groom')) ? new CoupleResource($groom) : null,
                'bride' => ($bride = $this->couples->firstWhere('role', 'bride')) ? new CoupleResource($bride) : null,
            ],
            // Generic N-per-invitation counterpart to `couples` above — always present (empty
            // for wedding invitations) so this resource stays a pure data mapper; the frontend
            // decides what to render from `event_category`, already in this same payload.
            'honorees' => HonoreeResource::collection($this->honorees),
            'events' => InvitationEventResource::collection($this->events),
            'love_stories' => LoveStoryResource::collection($this->loveStories),
            'gallery' => GalleryResource::collection($this->gallery),
            'music' => ($music = $this->music) && $music->is_active ? new MusicResource($music) : null,
        ];
    }
}
