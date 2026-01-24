<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Operator Package Rate Model
 * 
 * Represents the middle tier in the 3-tier package hierarchy:
 * 1. Master Package - Base package created by developer/super-admin
 * 2. Operator Package Rate (this) - Operator-specific pricing
 * 3. Package - Final customer packages
 * 
 * @property int $id
 * @property int|null $tenant_id
 * @property int $operator_id
 * @property int|null $package_id Legacy field for backward compatibility
 * @property int|null $master_package_id
 * @property decimal $operator_price Price set by operator (must be <= base_price)
 * @property decimal $custom_price Legacy field for backward compatibility
 * @property decimal $commission_percentage
 * @property string $status active/inactive
 * @property int $assigned_by User who assigned this rate
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OperatorPackageRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'operator_id',
        'package_id', // Legacy
        'master_package_id',
        'operator_price',
        'custom_price', // Legacy
        'commission_percentage',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'operator_id' => 'integer',
        'package_id' => 'integer',
        'master_package_id' => 'integer',
        'operator_price' => 'decimal:2',
        'custom_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'assigned_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the operator who owns this rate
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the package (legacy relationship)
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Get the master package associated with this rate
     */
    public function masterPackage(): BelongsTo
    {
        return $this->belongsTo(MasterPackage::class, 'master_package_id');
    }

    /**
     * Get the user who assigned this rate
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get packages created from this operator rate
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'operator_package_rate_id');
    }

    /**
     * Validate that operator price doesn't exceed base price
     */
    public function validatePrice(): bool
    {
        if (!$this->master_package_id) {
            return true; // Legacy records without master package
        }

        $masterPackage = $this->masterPackage;
        if (!$masterPackage) {
            return false;
        }

        return $this->operator_price <= $masterPackage->base_price;
    }

    /**
     * Calculate suggested retail price based on margin
     */
    public function getSuggestedRetailPrice(float $marginPercentage = 20.0): float
    {
        return round($this->operator_price * (1 + $marginPercentage / 100), 2);
    }

    /**
     * Check if margin is too low (below threshold)
     */
    public function hasLowMargin(float $threshold = 10.0): bool
    {
        if (!$this->master_package_id) {
            return false;
        }

        $masterPackage = $this->masterPackage;
        if (!$masterPackage || $masterPackage->base_price == 0) {
            return false;
        }

        $margin = (($this->operator_price - $masterPackage->base_price) / $masterPackage->base_price) * 100;
        
        return $margin < $threshold;
    }
}
