<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'is_checked_in' => false,
    ];

    protected $fillable = [
        'invitation_id',
        'name',
        'phone',
        'category',
        'slug_token',
        'qr_code',
        'is_checked_in',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'is_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Both columns are unique + required at the DB level. slug_token drives the
        // personalized `?to=` link (this phase); qr_code exists in the schema for a future
        // event-day check-in feature — not built yet, so it just gets a same-shaped random
        // value to satisfy the column rather than sitting unused/nullable.
        static::creating(function (Guest $guest) {
            $guest->slug_token ??= Str::random(12);
            $guest->qr_code ??= Str::random(16);
        });
    }

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
