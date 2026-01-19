<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type', // asset, liability, equity, revenue, expense
        'sub_type',
        'description',
        'parent_account_id',
        'debit_balance',
        'credit_balance',
        'balance',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'debit_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get parent account
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get child accounts
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Get debit entries
     */
    public function debitEntries()
    {
        return $this->hasMany(GeneralLedgerEntry::class, 'debit_account_id');
    }

    /**
     * Get credit entries
     */
    public function creditEntries()
    {
        return $this->hasMany(GeneralLedgerEntry::class, 'credit_account_id');
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope system accounts
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
}
