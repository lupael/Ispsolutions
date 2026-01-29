<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Auto-Debit History Model
 * 
 * Tracks auto-debit payment attempts for customers
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.2
 * 
 * @property int $id
 * @property int $customer_id
 * @property int|null $bill_id
 * @property float $amount
 * @property string $status
 * @property string|null $failure_reason
 * @property int $retry_count
 * @property string|null $payment_method
 * @property string|null $transaction_id
 * @property \Carbon\Carbon $attempted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $customer
 * @property-read SubscriptionBill|null $bill
 */
class AutoDebitHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auto_debit_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'bill_id',
        'amount',
        'status',
        'failure_reason',
        'retry_count',
        'payment_method',
        'transaction_id',
        'attempted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'customer_id' => 'integer',
        'bill_id' => 'integer',
        'amount' => 'decimal:2',
        'retry_count' => 'integer',
        'attempted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer who owns this auto-debit attempt
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the bill associated with this auto-debit attempt
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(SubscriptionBill::class, 'bill_id');
    }

    /**
     * Check if auto-debit was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if auto-debit failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if auto-debit is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark auto-debit as successful
     */
    public function markSuccessful(?string $transactionId = null): void
    {
        $this->update([
            'status' => 'success',
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Mark auto-debit as failed
     *
     * @param string $reason Failure reason
     */
    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    /**
     * Scope to get successful auto-debits
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed auto-debits
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get pending auto-debits
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get auto-debits for a specific customer
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
