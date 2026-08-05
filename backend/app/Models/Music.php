<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Music extends Model
{
    use HasFactory;

    // Laravel's pluralizer treats "Music" as uncountable and guesses the table name
    // "music" — the migration created it as "musics", so this must be explicit.
    protected $table = 'musics';

    public const SOURCES = ['upload', 'spotify', 'youtube_music'];

    /**
     * Mirrors the DB-level defaults — see Phase 3/4/5 notes on why a freshly
     * created Eloquent instance otherwise returns null instead of these here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'source' => 'upload',
        'autoplay' => true,
        'is_loop' => true,
        'is_active' => true,
    ];

    protected $fillable = [
        'invitation_id',
        'source',
        'title',
        'artist',
        'file_path',
        'external_url',
        'autoplay',
        'is_loop',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'autoplay' => 'boolean',
            'is_loop' => 'boolean',
            'is_active' => 'boolean',
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
