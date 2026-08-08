<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * Mirrors the DB-level defaults — see Phase 3/4/5 notes on why a freshly
     * created Eloquent instance otherwise returns null instead of these here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'requires_payment' => true,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'is_free',
        'requires_payment',
        'auto_publish',
        'duration_days',
        'max_photos',
        'max_guests',
        'features',
        'limits',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'requires_payment' => 'boolean',
            'auto_publish' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The single gate every publish decision reads — never branch on `slug`/`name`
     * instead, so a new package tier needs a database row, not a code change.
     */
    public function requiresPayment(): bool
    {
        return $this->requires_payment;
    }

    public function autoPublishes(): bool
    {
        return $this->auto_publish;
    }

    /**
     * Reads a granular feature/limit toggle out of `limits` (e.g. "watermark",
     * "max_active_invitations") — the schemaless counterpart to the dedicated
     * max_photos/max_guests columns, for flags that don't need to be queried directly.
     */
    public function limit(string $key, mixed $default = null): mixed
    {
        return data_get($this->limits, $key, $default);
    }

    protected static function booted(): void
    {
        static::creating(function (Package $package) {
            $package->slug ??= Str::slug($package->name);
        });
    }

    /**
     * @return HasMany<Invitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
