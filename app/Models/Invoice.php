<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'user_id',
        'package_id',
        'amount',
        'tax_amount',
        'total_amount',
        'status', // draft, pending, paid, cancelled, overdue
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    // Optimized query scopes with indexed filters
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeWithUserAndPayments(Builder $query): Builder
    {
        // Optimized: Eager load with select to minimize data transfer
        return $query->with([
            'user:id,name,email',
            'payments' => function ($q) {
                $q->select('id', 'invoice_id', 'amount', 'status', 'paid_at');
            },
        ]);
    }

    public function scopeWithRelations(Builder $query): Builder
    {
        // Optimized: Load all necessary relations in one query
        return $query->with([
            'user:id,name,email',
            'package:id,name,price',
            'payments:id,invoice_id,amount,status,paid_at',
        ]);
    }
}
