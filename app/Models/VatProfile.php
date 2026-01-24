<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatProfile extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all VAT collections using this profile.
     */
    public function collections()
    {
        return $this->hasMany(VatCollection::class);
    }

    /**
     * Calculate VAT amount from base amount
     */
    public function calculateVat(float $baseAmount): float
    {
        return round($baseAmount * ($this->rate / 100), 2);
    }

    /**
     * Calculate total amount including VAT
     */
    public function calculateTotal(float $baseAmount): float
    {
        return round($baseAmount + $this->calculateVat($baseAmount), 2);
    }
}
