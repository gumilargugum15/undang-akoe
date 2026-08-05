<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = ['photo', 'video_youtube', 'video_mp4'];

    /**
     * Mirrors the DB-level default — see prior phases' notes on why a freshly
     * created Eloquent instance otherwise returns null instead of 0 here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'invitation_id',
        'type',
        'file_path',
        'external_url',
        'thumbnail',
        'caption',
        'category',
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
