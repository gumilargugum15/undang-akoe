<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    public const POSITIONS = ['home_hero', 'home_secondary', 'sidebar'];

    /**
     * Mirrors the DB-level defaults — see Phase 3/4/5 notes on why a freshly
     * created Eloquent instance otherwise returns null instead of these here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'position' => 'home_hero',
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected $fillable = [
        'title',
        'image',
        'link_url',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Whether this banner should currently be shown to guests: active, and — if a schedule
     * window is set — within it. A banner with no starts_at/ends_at is always in-window.
     */
    public function isCurrentlyLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
