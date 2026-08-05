<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoveStory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mirrors the DB-level default — see Phase 3/4/5 notes on why a freshly
     * created Eloquent instance otherwise returns null instead of 0 here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'invitation_id',
        'title',
        'story_date',
        'description',
        'photo',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'story_date' => 'date',
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
