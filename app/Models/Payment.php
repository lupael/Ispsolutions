<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'payment_number',
        'user_id',
        'collected_by',
        'invoice_id',
        'payment_gateway_id',
        'amount',
        'transaction_id',
        'status', // pending, completed, failed, refunded
        'payment_method', // gateway, card, cash, bank_transfer
        'payment_data', // JSON field for gateway response
        'payment_type', // hotspot_recharge, installation, equipment, etc.
        'paid_at',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Optimized query scopes with indexed filters
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    public function scopeWithRelations(Builder $query): Builder
    {
        // Optimized: Eager load with select to minimize data transfer
        return $query->with([
            'user:id,name,email',
            'invoice:id,invoice_number,total_amount,status',
            'gateway:id,name,type',
            'collector:id,name,email',
        ]);
    }

    public function scopeRecentPayments(Builder $query, int $days = 30): Builder
    {
        return $query->where('paid_at', '>=', now()->subDays($days));
    }
}
