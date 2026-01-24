<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvancePayment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'amount',
        'remaining_balance',
        'payment_method',
        'transaction_reference',
        'notes',
        'payment_date',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the user that made the advance payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who received this payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Check if advance payment has remaining balance
     */
    public function hasBalance(): bool
    {
        return $this->remaining_balance > 0;
    }

    /**
     * Check if advance payment is fully utilized
     */
    public function isFullyUtilized(): bool
    {
        return $this->remaining_balance <= 0;
    }
}
