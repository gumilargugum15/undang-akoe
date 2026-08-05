<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalEnvelope extends Model
{
    use HasFactory;

    public const TYPES = ['bank', 'ewallet', 'qris'];

    public const EWALLET_PROVIDERS = ['Dana', 'OVO', 'GoPay', 'ShopeePay'];

    /**
     * Mirrors the DB-level defaults — see prior phases' notes on why a
     * freshly created Eloquent instance otherwise returns null here.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected $fillable = [
        'invitation_id',
        'type',
        'provider_name',
        'account_number',
        'account_holder',
        'qr_image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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
