<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class Theme extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'theme_category_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'banner_preview',
        'screenshots',
        'version',
        'author',
        'status',
        'type',
        'price',
        'supports_dark_mode',
        'config',
        'is_active',
        'sort_order',
    ];

    /**
     * Mirrors the DB-level column defaults so a freshly `create()`d instance
     * reflects them in-memory before any `fresh()`/`refresh()` reload — MySQL
     * doesn't support RETURNING, so Eloquent otherwise only knows what was
     * explicitly passed in.
     */
    protected $attributes = [
        'version' => '1.0.0',
        'status' => 'draft',
        'type' => 'free',
        'price' => 0,
        'supports_dark_mode' => false,
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'screenshots' => 'array',
            'config' => 'array',
            'price' => 'decimal:2',
            'supports_dark_mode' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Theme $theme) {
            $theme->slug ??= Str::slug($theme->name);
        });
    }

    /**
     * @return BelongsTo<ThemeCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ThemeCategory::class, 'theme_category_id');
    }

    /**
     * @return HasMany<Invitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function isSelectableBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return $this->is_active;
        }

        return $this->is_active && $this->status === 'published';
    }

    /**
     * The `exists:themes,id` validation counterpart to isSelectableBy() — an
     * admin may assign any active theme (including drafts, for previewing
     * before publish), a customer only ones that are active and published.
     * Shared by StoreInvitationRequest, UpdateInvitationRequest, and
     * ChangeInvitationThemeRequest so the rule can't drift between them.
     */
    public static function selectableRule(bool $isAdmin): Exists
    {
        return Rule::exists('themes', 'id')->where(function ($query) use ($isAdmin) {
            $query->where('is_active', true);
            if (! $isAdmin) {
                $query->where('status', 'published');
            }
        });
    }
}
