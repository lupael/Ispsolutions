<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Bkash Agreement Model
 * 
 * Represents a tokenization agreement with Bkash for one-click payments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Bkash Tokenization
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.4
 * 
 * @property int $id
 * @property int $user_id
 * @property string $agreement_id Bkash agreement ID
 * @property string|null $payment_id Initial payment ID
 * @property string $status Agreement status (pending, active, cancelled, expired)
 * @property string $customer_msisdn Customer mobile number
 * @property \Carbon\Carbon|null $created_time
 * @property \Carbon\Carbon|null $cancelled_time
 * @property \Carbon\Carbon|null $expired_time
 * @property string|null $metadata Additional metadata (JSON)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection $tokens
 */
class BkashAgreement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'agreement_id',
        'payment_id',
        'status',
        'customer_msisdn',
        'created_time',
        'cancelled_time',
        'expired_time',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'created_time' => 'datetime',
        'cancelled_time' => 'datetime',
        'expired_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user who owns this agreement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all tokens associated with this agreement
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(BkashToken::class, 'bkash_agreement_id');
    }

    /**
     * Get the active token for this agreement
     */
    public function activeToken()
    {
        return $this->tokens()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if agreement is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if agreement is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if agreement is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if agreement is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Mark agreement as active
     */
    public function markActive(): void
    {
        $this->update([
            'status' => 'active',
            'created_time' => now(),
        ]);
    }

    /**
     * Mark agreement as cancelled
     */
    public function markCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_time' => now(),
        ]);
    }

    /**
     * Mark agreement as expired
     */
    public function markExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'expired_time' => now(),
        ]);
    }

    /**
     * Scope to get active agreements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get pending agreements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get cancelled agreements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get expired agreements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope to get agreements for a specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
