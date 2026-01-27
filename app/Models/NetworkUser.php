<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasOnlineStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkUser extends Model
{
    use HasFactory;
    use HasOnlineStatus;

    protected $fillable = [
        'username',
        'password',
        'service_type',
        'package_id',
        'status',
        'expiry_date',
        'connection_type',
        'billing_type',
        'device_type',
        'mac_address',
        'ip_address',
        'is_active',
        'user_id',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'is_active' => 'boolean',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ipAllocations(): HasMany
    {
        return $this->hasMany(IpAllocation::class, 'username', 'username');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(NetworkUserSession::class, 'user_id');
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    // Query scopes for optimization with indexed filters
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByServiceType(Builder $query, string $serviceType): Builder
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeWithPackageAndInvoices(Builder $query): Builder
    {
        // Optimized: Eager load relationships to avoid N+1 queries
        return $query->with(['package:id,name,price,bandwidth_upload,bandwidth_download']);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Calculate data usage percentage based on package limits
     */
    public function dataUsagePercent(): float
    {
        if (!$this->package || !$this->package->data_limit) {
            return 0;
        }

        // Get current month usage from sessions
        $usedData = $this->getCurrentMonthDataUsage();
        $limit = $this->package->data_limit;

        if ($limit === 0) {
            return 0;
        }

        return round(($usedData / $limit) * 100, 1);
    }

    /**
     * Format data usage display
     */
    public function formatDataUsage(): string
    {
        $usedData = $this->getCurrentMonthDataUsage();
        $limit = $this->package?->data_limit ?? 0;

        return $this->formatBytes($usedData) . ' / ' . $this->formatBytes($limit);
    }

    /**
     * Get current month data usage in bytes
     */
    protected function getCurrentMonthDataUsage(): int
    {
        $startOfMonth = now()->startOfMonth();
        
        // Optimized: Calculate total in single query using actual column names
        return (int) $this->sessions()
            ->where('created_at', '>=', $startOfMonth)
            ->selectRaw('SUM(upload_bytes + download_bytes) as total_bytes')
            ->value('total_bytes') ?? 0;
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
