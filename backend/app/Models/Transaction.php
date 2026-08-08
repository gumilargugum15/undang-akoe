<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_EXPIRED,
        self::STATUS_REFUNDED,
    ];

    /**
     * Only manual verification methods exist today (no payment gateway) — see
     * TransactionService/CheckoutService.
     */
    public const PAYMENT_METHODS = ['bank_transfer', 'qris', 'dana', 'gopay'];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $fillable = [
        'invoice_number',
        'user_id',
        'package_id',
        'package_name_snapshot',
        'invitation_id',
        'amount',
        'payment_method',
        'payment_channel',
        'status',
        'paid_at',
        'expired_at',
        'proof_image',
        'proof_uploaded_at',
        'verified_by',
        'verified_at',
        'gateway_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'proof_uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAwaitingVerification(): bool
    {
        return $this->isPending() && $this->proof_uploaded_at !== null;
    }

    /**
     * Manual-payment instructions for a method — the same lookup whether it's shown right
     * after checkout or when a customer returns to an already-pending transaction later.
     * Delegates to PaymentSettingService, which reads admin-configured values (site_settings)
     * with config/payment.php as the fallback — see that class for the actual per-method shape.
     *
     * @return array<string, mixed>
     */
    public static function paymentInstructionsFor(string $method): array
    {
        return app(\App\Services\PaymentSettingService::class)->instructionsFor($method);
    }
}
