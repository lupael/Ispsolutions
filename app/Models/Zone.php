<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Zone Model
 *
 * Represents geographic zones/areas for customer organization and reporting.
 * Supports hierarchical zones with parent-child relationships.
 */
class Zone extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'code',
        'description',
        'latitude',
        'longitude',
        'radius',
        'color',
        'is_active',
        'coverage_type', // 'point', 'radius', 'polygon'
        'coverage_data', // JSON data for polygon coordinates
        'metadata', // Additional zone metadata
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'decimal:2',
        'coverage_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent zone
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'parent_id');
    }

    /**
     * Get child zones
     */
    public function children(): HasMany
    {
        return $this->hasMany(Zone::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get customers in this zone
     */
    public function customers(): HasMany
    {
        return $this->hasMany(User::class, 'zone_id')->where('role_level', 100);
    }

    /**
     * Get network users in this zone
     */
    public function networkUsers(): HasMany
    {
        return $this->hasMany(NetworkUser::class, 'zone_id');
    }

    /**
     * Scope for active zones
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root zones (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get full zone path (hierarchical)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get total customer count in zone and all child zones
     * Note: This attribute should be used sparingly as it can be expensive.
     * For reports, use withCount() queries instead.
     */
    public function getTotalCustomersCountAttribute(): int
    {
        // Use a single query with subquery to count customers in this zone and all descendants
        return User::where('role_level', 100)
            ->where(function ($query) {
                $query->where('zone_id', $this->id)
                    ->orWhereIn('zone_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('zones')
                            ->where('parent_id', $this->id)
                            ->orWhereRaw('parent_id IN (SELECT id FROM zones WHERE parent_id = ?)', [$this->id]);
                    });
            })
            ->count();
    }

    /**
     * Get active customer count
     */
    public function getActiveCustomersCountAttribute(): int
    {
        return $this->customers()->where('is_active', true)->count();
    }
}
