<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Operator Subscription Model
 * 
 * Represents a platform subscription for an operator
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.3
 * 
 * @property int $id
 * @property int $operator_id
 * @property int $subscription_plan_id
 * @property string $status Subscription status (active, suspended, cancelled, expired)
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property int $billing_cycle Billing cycle in months (1=monthly, 3=quarterly, 12=yearly)
 * @property \Carbon\Carbon|null $next_billing_date
 * @property bool $auto_renew
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $operator
 * @property-read SubscriptionPlan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection $payments
 */
class OperatorSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'operator_id',
        'subscription_plan_id',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
        'billing_cycle',
        'next_billing_date',
        'auto_renew',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operator_id' => 'integer',
        'subscription_plan_id' => 'integer',
        'billing_cycle' => 'integer',
        'auto_renew' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the operator who owns this subscription
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the subscription plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Get all payments for this subscription
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'operator_subscription_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' 
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Mark subscription as cancelled
     */
    public function markCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Mark subscription as expired
     */
    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark subscription as suspended
     */
    public function markSuspended(): void
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Reactivate a suspended subscription
     */
    public function reactivate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Renew the subscription for another billing cycle
     */
    public function renew(): void
    {
        // Extend from current expiration when available to avoid shortening early renewals
        $baseDate = $this->expires_at !== null ? $this->expires_at->copy() : now();
        $newExpiry = $baseDate->addMonths($this->billing_cycle);

        $this->update([
            'expires_at' => $newExpiry,
            'next_billing_date' => $newExpiry,
            'status' => 'active',
        ]);
    }

    /**
     * Get the number of days until subscription expires
     */
    public function getDaysUntilExpiration(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Check if subscription is about to expire (within 7 days)
     */
    public function isAboutToExpire(): bool
    {
        $daysUntilExpiration = $this->getDaysUntilExpiration();
        return $daysUntilExpiration !== null && $daysUntilExpiration <= 7 && $daysUntilExpiration >= 0;
    }

    /**
     * Scope to get active subscriptions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired subscriptions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere(function ($subq) {
                    $subq->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());
                });
        });
    }

    /**
     * Scope to get subscriptions due for billing
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now());
    }

    /**
     * Scope to get subscriptions for a specific operator
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $operatorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }
}
