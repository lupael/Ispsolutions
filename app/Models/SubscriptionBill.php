<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionBill extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'bill_number',
        'billing_period_start',
        'billing_period_end',
        'amount',
        'tax',
        'discount',
        'total_amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Bill status constants
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_OVERDUE,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get the subscription this bill belongs to
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if bill is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if bill is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
               ($this->status === self::STATUS_PENDING && $this->due_date && $this->due_date->isPast());
    }

    /**
     * Mark bill as paid
     */
    public function markAsPaid(string $paymentMethod, ?string $paymentReference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Generate bill number with better uniqueness guarantee
     */
    public static function generateBillNumber(): string
    {
        // Use timestamp with microseconds and random suffix for better uniqueness
        $timestamp = now()->format('YmdHis') . now()->format('u');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        return 'BILL-' . $timestamp . '-' . $random;
    }

    /**
     * Calculate total amount including tax and discount
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->amount;
        $taxAmount = $this->tax ?? 0;
        $discountAmount = $this->discount ?? 0;

        return $subtotal + $taxAmount - $discountAmount;
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending bills
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter paid bills
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope to filter overdue bills
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_OVERDUE)
                ->orWhere(function ($q2) {
                    $q2->where('status', self::STATUS_PENDING)
                        ->where('due_date', '<', now());
                });
        });
    }

    /**
     * Scope to filter by subscription
     */
    public function scopeBySubscription($query, int $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }
}
