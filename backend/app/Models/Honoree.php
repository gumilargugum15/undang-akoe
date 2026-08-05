<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Honoree extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mirrors the DB-level default — see Couple's identical note on why a
     * freshly created Eloquent instance otherwise returns null instead of 0 here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'invitation_id',
        'role_label',
        'nickname',
        'full_name',
        'parent_name',
        'instagram_handle',
        'photo',
        'description',
        'meta',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
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
