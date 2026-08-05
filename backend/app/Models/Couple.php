<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Couple extends Model
{
    use HasFactory, SoftDeletes;

    public const ROLES = ['groom', 'bride'];

    /**
     * Mirrors the DB-level default — see Phase 3/4 notes on why a freshly
     * created Eloquent instance otherwise returns null instead of 0 here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'invitation_id',
        'role',
        'nickname',
        'full_name',
        'parent_name',
        'instagram_handle',
        'photo',
        'description',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
