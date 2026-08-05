<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationSeo extends Model
{
    protected $table = 'invitation_seo';

    protected $fillable = [
        'invitation_id',
        'meta_title',
        'meta_description',
        'og_image',
        'favicon',
        'ga_tracking_id',
        'fb_pixel_id',
    ];

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
