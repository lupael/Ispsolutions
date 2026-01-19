<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GeneralLedgerEntry extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'date',
        'reference_number',
        'description',
        'type', // invoice, payment, expense, adjustment
        'source_type',
        'source_id',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'notes',
        'created_by',
        'reversed_at',
        'reversed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    /**
     * Get debit account
     */
    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id');
    }

    /**
     * Get credit account
     */
    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

    /**
     * Get source (polymorphic)
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get reverser
     */
    public function reverser()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope not reversed
     */
    public function scopeNotReversed($query)
    {
        return $query->whereNull('reversed_at');
    }

    /**
     * Check if entry is reversed
     */
    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }
}
