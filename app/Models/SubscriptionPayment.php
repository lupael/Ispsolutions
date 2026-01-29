<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Subscription Payment Model
 * 
 * Represents a payment made by an operator for platform subscription
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.3
 * 
 * @property int $id
 * @property int $operator_subscription_id
 * @property int $operator_id
 * @property float $amount Payment amount
 * @property string|null $payment_method Payment gateway used
 * @property string|null $transaction_id Payment gateway transaction ID
 * @property string $status Payment status (pending, completed, failed, refunded)
 * @property \Carbon\Carbon $billing_period_start
 * @property \Carbon\Carbon $billing_period_end
 * @property string|null $invoice_number
 * @property string|null $notes Additional notes or failure reason
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read OperatorSubscription $subscription
 * @property-read User $operator
 */
class SubscriptionPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'operator_subscription_id',
        'operator_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'billing_period_start',
        'billing_period_end',
        'invoice_number',
        'notes',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operator_subscription_id' => 'integer',
        'operator_id' => 'integer',
        'amount' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subscription this payment belongs to
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(OperatorSubscription::class, 'operator_subscription_id');
    }

    /**
     * Get the operator who made the payment
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted(?string $transactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     *
     * @param string $reason Failure reason
     */
    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    /**
     * Mark payment as refunded
     *
     * @param string|null $reason Refund reason
     */
    public function markRefunded(?string $reason = null): void
    {
        $this->update([
            'status' => 'refunded',
            'notes' => $reason ?? $this->notes,
        ]);
    }

    /**
     * Get the billing period duration in days
     */
    public function getBillingPeriodDuration(): int
    {
        return $this->billing_period_start->diffInDays($this->billing_period_end);
    }

    /**
     * Generate invoice number if not set
     */
    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        $invoiceNumber = 'SUB-' . str_pad((string) $this->id, 8, '0', STR_PAD_LEFT);
        $this->update(['invoice_number' => $invoiceNumber]);

        return $invoiceNumber;
    }

    /**
     * Scope to get completed payments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending payments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed payments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get payments for a specific operator
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $operatorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Scope to get payments for a specific billing period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForBillingPeriod($query, $start, $end)
    {
        return $query->where('billing_period_start', '>=', $start)
            ->where('billing_period_end', '<=', $end);
    }
}
