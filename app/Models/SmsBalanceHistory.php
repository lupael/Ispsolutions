<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * SMS Balance History Model
 * 
 * Tracks SMS credit purchases, usage, refunds, and adjustments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.1
 * 
 * @property int $id
 * @property int $operator_id
 * @property string $transaction_type Type of transaction (purchase, usage, refund, adjustment)
 * @property int $amount SMS credits added or deducted
 * @property int $balance_before SMS balance before transaction
 * @property int $balance_after SMS balance after transaction
 * @property string|null $reference_type Related entity type
 * @property int|null $reference_id Related entity ID
 * @property string|null $notes Additional transaction details
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $operator
 */
class SmsBalanceHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_balance_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'operator_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operator_id' => 'integer',
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'reference_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the operator associated with this history entry
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Check if this is a purchase transaction
     */
    public function isPurchase(): bool
    {
        return $this->transaction_type === 'purchase';
    }

    /**
     * Check if this is a usage transaction
     */
    public function isUsage(): bool
    {
        return $this->transaction_type === 'usage';
    }

    /**
     * Check if this is a refund transaction
     */
    public function isRefund(): bool
    {
        return $this->transaction_type === 'refund';
    }

    /**
     * Check if this is an adjustment transaction
     */
    public function isAdjustment(): bool
    {
        return $this->transaction_type === 'adjustment';
    }

    /**
     * Get the change in balance (positive or negative)
     */
    public function getBalanceChange(): int
    {
        return $this->balance_after - $this->balance_before;
    }
}
