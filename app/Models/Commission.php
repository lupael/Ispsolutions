<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Commission Model
 *
 * Tracks commission earnings for Operators (Level 30) and Sub-Operators (Level 40).
 * 
 * NOTE: The database column 'reseller_id' is kept for backward compatibility
 * but semantically refers to the operator_id. Operators and Sub-Operators
 * earn commissions from customer payments.
 * 
 * Terminology:
 * - "Reseller" (deprecated) → "Operator" (Level 30)
 * - "Sub-Reseller" (deprecated) → "Sub-Operator" (Level 40)
 */
class Commission extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'reseller_id', // NOTE: Column kept for backward compatibility, refers to operator_id
        'payment_id',
        'invoice_id',
        'commission_amount',
        'commission_percentage',
        'status', // pending, paid, cancelled
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the operator who earned this commission.
     * 
     * @return BelongsTo
     * @deprecated Use operator() method instead. Method kept for backward compatibility.
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the operator who earned this commission.
     * 
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
