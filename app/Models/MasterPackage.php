<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
     * Get the count of customers using packages derived from this master package (cached)
     * Task 1.2: Add caching to MasterPackage model customer count
     */
    protected function customerCount(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                "master_package_customerCount_{$this->id}",
                150, // TTL: 150 seconds (2.5 minutes)
                fn () => $this->packages()->withCount('users')->get()->sum('users_count')
            )
        )->shouldCache();
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

    /**
     * Convert validity to days
     * Task 4.1: Add computed attributes to MasterPackage
     */
    public function getValidityInDaysAttribute(): int
    {
        return $this->validity_days ?? 0;
    }

    /**
     * Convert validity to hours
     * Task 4.1: Add computed attributes to MasterPackage
     */
    public function getValidityInHoursAttribute(): int
    {
        return ($this->validity_days ?? 0) * 24;
    }

    /**
     * Convert validity to minutes
     * Task 4.1: Add computed attributes to MasterPackage
     */
    public function getValidityInMinutesAttribute(): int
    {
        return ($this->validity_days ?? 0) * 24 * 60;
    }

    /**
     * Get readable rate unit (Mbps/Kbps)
     * Task 4.3: Add readable_rate_unit accessor
     */
    public function getReadableRateUnitAttribute(): string
    {
        // Determine if bandwidth is in Mbps or Kbps range
        $download = $this->speed_download ?? 0;
        
        if ($download >= 1024) {
            return 'Mbps';
        }
        
        return 'Kbps';
    }

    /**
     * Get total octet limit in bytes
     * Task 4.4: Add total_octet_limit accessor
     */
    public function getTotalOctetLimitAttribute(): ?int
    {
        if (!$this->volume_limit) {
            return null;
        }
        
        // Convert MB to bytes
        return $this->volume_limit * 1024 * 1024;
    }
}
