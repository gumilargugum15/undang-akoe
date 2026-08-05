<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const EVENT_CATEGORIES = [
        'wedding', 'birthday', 'khitan', 'aqiqah', 'anniversary', 'corporate', 'graduation', 'custom',
    ];

    public const STATUSES = ['draft', 'published', 'expired', 'suspended'];

    /**
     * Mirrors the DB-level default so a freshly-created instance reflects it
     * immediately instead of returning null until a refresh() (see Phase 3's
     * User::is_active fix for the same class of bug).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'view_count' => 0,
    ];

    protected $fillable = [
        'user_id',
        'theme_id',
        'package_id',
        'event_category',
        'title',
        'slug',
        'subdomain',
        'status',
        'is_active',
        'theme_settings',
        'cover_photo',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'theme_settings' => 'array',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Theme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return HasOne<InvitationSeo, $this>
     */
    public function seo(): HasOne
    {
        return $this->hasOne(InvitationSeo::class);
    }

    /**
     * @return HasMany<Couple, $this>
     */
    public function couples(): HasMany
    {
        return $this->hasMany(Couple::class);
    }

    /**
     * The generic, N-per-invitation counterpart to `couples()` — used by every
     * non-wedding category (birthday, khitan, ...) instead of the fixed groom/bride pair.
     *
     * @return HasMany<Honoree, $this>
     */
    public function honorees(): HasMany
    {
        return $this->hasMany(Honoree::class);
    }

    /**
     * Pre-registered invitees with a personalized `slug_token` link — distinct from
     * `guestbook()`, which holds self-submitted RSVP/wishes entries instead.
     *
     * @return HasMany<Guest, $this>
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * @return HasMany<InvitationEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(InvitationEvent::class);
    }

    /**
     * @return HasMany<Gallery, $this>
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    /**
     * @return HasMany<LoveStory, $this>
     */
    public function loveStories(): HasMany
    {
        return $this->hasMany(LoveStory::class);
    }

    /**
     * The `musics` table technically allows multiple rows per invitation, but the app enforces
     * a single "current track" slot (see MusicRepository::upsert) — same single-slot pattern as
     * Couple's groom/bride rows, just without a role column to key on.
     *
     * @return HasOne<Music, $this>
     */
    public function music(): HasOne
    {
        return $this->hasOne(Music::class);
    }

    /**
     * @return HasMany<Guestbook, $this>
     */
    public function guestbook(): HasMany
    {
        return $this->hasMany(Guestbook::class);
    }

    /**
     * Whether the invitation is currently visible/interactable by the public
     * — gates RSVP submission, the guestbook wall, and the digital envelope
     * list. A draft/suspended invitation 404s on all of these.
     */
    public function isLive(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    /**
     * @return HasMany<DigitalEnvelope, $this>
     */
    public function envelopes(): HasMany
    {
        return $this->hasMany(DigitalEnvelope::class);
    }

    /**
     * @return HasMany<InvitationVisit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(InvitationVisit::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * The theme's base `config` with this invitation's `theme_settings`
     * overrides layered on top — recursively, so a customer overriding just
     * `tokens.primary` doesn't wipe out the rest of the base `tokens` block.
     *
     * @return array<string, mixed>
     */
    public function resolvedThemeConfig(): array
    {
        return array_replace_recursive(
            $this->theme->config ?? [],
            $this->theme_settings ?? [],
        );
    }
}
