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

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'operator_id',
        'master_package_id',
        'parent_package_id',
        'operator_package_rate_id',
        'name',
        'description',
        'price',
        'bandwidth_upload',
        'bandwidth_download',
        'data_limit',
        'validity_days',
        'billing_type',
        'billing_cycle',
        'daily_rate',
        'allow_partial_day',
        'status',
        'is_global',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'bandwidth_upload' => 'integer',
        'bandwidth_download' => 'integer',
        'data_limit' => 'integer',
        'validity_days' => 'integer',
        'is_global' => 'boolean',
        'allow_partial_day' => 'boolean',
        'master_package_id' => 'integer',
        'operator_package_rate_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get users subscribed to this package
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'service_package_id');
    }

    /**
     * @deprecated Use users() instead. NetworkUser model is deprecated.
     */
    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class, 'package_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function masterPackage(): BelongsTo
    {
        return $this->belongsTo(MasterPackage::class, 'master_package_id');
    }

    /**
     * Get the parent package
     * Task 8.2: Add relationships to Package model
     */
    public function parentPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'parent_package_id');
    }

    /**
     * Get child packages
     * Task 8.2: Add relationships to Package model
     */
    public function childPackages(): HasMany
    {
        return $this->hasMany(Package::class, 'parent_package_id');
    }

    /**
     * Check if this package has a parent
     */
    public function hasParent(): bool
    {
        return $this->parent_package_id !== null;
    }

    /**
     * Check if this package has children
     */
    public function hasChildren(): bool
    {
        return $this->childPackages()->exists();
    }

    public function operatorPackageRate(): BelongsTo
    {
        return $this->belongsTo(OperatorPackageRate::class, 'operator_package_rate_id');
    }

    public function profileMappings(): HasMany
    {
        return $this->hasMany(PackageProfileMapping::class, 'package_id');
    }

    public function operatorRates(): HasMany
    {
        return $this->hasMany(OperatorPackageRate::class, 'package_id');
    }

    public function fup(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PackageFup::class, 'package_id');
    }

    // Query scopes for optimization
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    public function scopeForOperator(Builder $query, int $operatorId): Builder
    {
        return $query->where(function ($q) use ($operatorId) {
            $q->where('is_global', true)
                ->orWhere('operator_id', $operatorId);
        });
    }

    /**
     * Get the count of customers using this package (cached)
     * Task 1.1: Add caching to Package model customer count
     */
    protected function customerCount(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                "package_customerCount_{$this->id}",
                150, // TTL: 150 seconds (2.5 minutes)
                fn () => $this->users()->count()
            )
        )->shouldCache();
    }

    /**
     * Get the price with fallback to minimum of 1
     * Task 5.1: Add price accessor to Package model
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value > 0 ? $value : 1,
            set: fn ($value) => $value
        );
    }

    /**
     * Convert validity to days
     * Task 4.2: Add computed attributes to Package model
     */
    public function getValidityInDaysAttribute(): int
    {
        return $this->validity_days ?? 0;
    }

    /**
     * Convert validity to hours
     * Task 4.2: Add computed attributes to Package model
     */
    public function getValidityInHoursAttribute(): int
    {
        return ($this->validity_days ?? 0) * 24;
    }

    /**
     * Convert validity to minutes
     * Task 4.2: Add computed attributes to Package model
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
        $download = $this->bandwidth_download ?? 0;
        
        if ($download >= 1024) {
            return 'Mbps';
        }
        
        return 'Kbps';
    }

    /**
     * Get total octet limit in bytes
     * Task 4.4: Add total_octet_limit accessor
     * Note: data_limit is already stored in bytes
     */
    public function getTotalOctetLimitAttribute(): ?int
    {
        // data_limit is already stored in bytes, return as-is
        return $this->data_limit;
    }
}
