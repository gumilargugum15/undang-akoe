<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guestbook extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const ATTENDANCES = ['hadir', 'tidak_hadir', 'ragu'];

    /**
     * Mirrors the DB-level defaults — see prior phases' notes on why a
     * freshly created Eloquent instance otherwise returns null here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guest_count' => 1,
        'is_approved' => true,
    ];

    protected $fillable = [
        'invitation_id',
        'guest_name',
        'phone',
        'attendance',
        'guest_count',
        'message',
        'is_approved',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
