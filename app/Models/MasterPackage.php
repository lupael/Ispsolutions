<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master Package Model
 * 
 * Represents the top-level package in the 3-tier hierarchy:
 * 1. Master Package (this) - Created by developer/super-admin
 * 2. Operator Package Rate - Operator-specific pricing
 * 3. Package - Final customer packages
 * 
 * @property int $id
 * @property int|null $tenant_id
 * @property int $created_by
 * @property string $name
 * @property string|null $description
 * @property int|null $speed_upload Upload speed in kbps
 * @property int|null $speed_download Download speed in kbps
 * @property int|null $volume_limit Volume limit in MB
 * @property int $validity_days
 * @property decimal $base_price Base price for operators
 * @property string $visibility public/private
 * @property bool $is_trial_package
 * @property string $status active/inactive
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MasterPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'description',
        'speed_upload',
        'speed_download',
        'volume_limit',
        'validity_days',
        'base_price',
        'visibility',
        'is_trial_package',
        'status',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'created_by' => 'integer',
        'speed_upload' => 'integer',
        'speed_download' => 'integer',
        'volume_limit' => 'integer',
        'validity_days' => 'integer',
        'base_price' => 'decimal:2',
        'is_trial_package' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this master package
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get operator package rates associated with this master package
     */
    public function operatorRates(): HasMany
    {
        return $this->hasMany(OperatorPackageRate::class, 'master_package_id');
    }

    /**
     * Get packages derived from this master package
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'master_package_id');
    }

    /**
     * Scope query to active packages only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to trial packages
     */
    public function scopeTrial(Builder $query): Builder
    {
        return $query->where('is_trial_package', true);
    }

    /**
     * Scope query to public packages
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope query by tenant
     */
    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope query for global packages (no tenant)
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('tenant_id');
    }

    /**
     * Get the count of operators using this master package
     */
    public function getOperatorCountAttribute(): int
    {
        return $this->operatorRates()->count();
    }

    /**
     * Get the count of customers using packages derived from this master package
     * Note: This performs a database query. Consider eager loading or caching for performance.
     */
    public function getCustomerCountAttribute(): int
    {
        return $this->packages()->withCount('networkUsers')->get()->sum('network_users_count');
    }

    /**
     * Check if the master package can be deleted
     */
    public function canDelete(): bool
    {
        // Cannot delete trial packages
        if ($this->is_trial_package) {
            return false;
        }

        // Cannot delete if operators or customers exist
        if ($this->operatorRates()->count() > 0 || $this->customer_count > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get deletion prevention reason
     */
    public function getDeletionPreventionReason(): ?string
    {
        if ($this->is_trial_package) {
            return 'Cannot delete trial packages';
        }

        $operatorCount = $this->operatorRates()->count();
        if ($operatorCount > 0) {
            return "Cannot delete: {$operatorCount} operator(s) are using this package";
        }

        $customerCount = $this->customer_count;
        if ($customerCount > 0) {
            return "Cannot delete: {$customerCount} customer(s) are using packages derived from this master package";
        }

        return null;
    }
}
