<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MikrotikRouter extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use HasAuditLog;

    /**
     * Status constants for the router's administrative state.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const API_STATUS_ONLINE = 'online';
    public const API_STATUS_OFFLINE = 'offline';

    protected $fillable = [
        'tenant_id',
        'nas_id',
        'name',
        'ip_address',
        'public_ip',
        'host',
        'api_port',
        'api_type',
        'username',
        'password',
        'overwrite_ppp_secrets_comment',
        'radius_secret',
        'primary_auth',
        'status',
        'api_status',
        'last_checked_at',
        'last_error',
        'response_time_ms',
    ];

    protected $hidden = [
        'password',
        'radius_secret',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'radius_secret' => 'encrypted',
        'api_port' => 'integer',
        'last_checked_at' => 'datetime',
        'response_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pppoeUsers(): HasMany
    {
        return $this->hasMany(MikrotikPppoeUser::class, 'router_id');
    }

    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class, 'nas_id');
    }

    /**
     * @deprecated Use pppoeUsers() instead. This is an alias for backward compatibility.
     * @note This method returns a collection of MikrotikPppoeUser models, NOT User or NetworkUser models.
     * @return HasMany
     */
    public function legacyPppoeUsers(): HasMany
    {
        return $this->pppoeUsers();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(MikrotikProfile::class, 'router_id');
    }

    public function ipPools(): HasMany
    {
        return $this->hasMany(MikrotikIpPool::class, 'router_id');
    }

    public function vpnAccounts(): HasMany
    {
        return $this->hasMany(MikrotikVpnAccount::class, 'router_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(MikrotikQueue::class, 'router_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(RouterConfiguration::class, 'router_id');
    }

    public function packageMappings(): HasMany
    {
        return $this->hasMany(PackageProfileMapping::class, 'router_id');
    }

    // Note: NetworkUser relationship is indirect through PackageProfileMapping
    // Uncomment if router_id is added to network_users table
    // public function networkUsers(): HasMany
    // {
    //     return $this->hasMany(NetworkUser::class, 'router_id');
    // }

    // Optimized query scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Check if the router is administratively active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the router's API is currently online.
     */
    public function isApiOnline(): bool
    {
        return $this->api_status === self::API_STATUS_ONLINE;
    }

    /**
     * Test connectivity to the router
     */
    public function testConnectivity(): bool
    {
        try {
            // Use MikroTik API service to test connection by attempting to connect
            $service = app(\App\Services\MikrotikService::class);
            return $service->connectRouter($this->id);
        } catch (\Exception $e) {
            \Log::error("Router connectivity test failed: " . $e->getMessage(), [
                'router_id' => $this->id,
                'router_name' => $this->name
            ]);
            return false;
        }
    }

    /**
     * Disconnect from the router
     * @return bool
     */
    public function disconnect(): bool
    {
        return $this->update([
            'api_status' => self::API_STATUS_OFFLINE,
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Connect to the router
     */
    public function connect(): bool
    {
        try {
            $service = app(\App\Services\MikrotikService::class);
            $connected = $service->connectRouter($this->id);

            if ($connected) {
                $this->update([
                    'api_status' => self::API_STATUS_ONLINE,
                    'last_checked_at' => now()
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Router connection failed: " . $e->getMessage(), [
                'router_id' => $this->id,
                'router_name' => $this->name
            ]);
            return false;
        }
    }

    /**
     * Refresh router statistics
     */
    public function refreshStats(): void
    {
        try {
            // Note: MikrotikService does not currently have getSystemResources() method
            // This is a placeholder for future implementation
            // For now, we just update the last_checked_at timestamp
            $this->update([
                'last_checked_at' => now(),
            ]);

            \Log::info("Router stats refresh placeholder called", [
                'router_id' => $this->id,
                'router_name' => $this->name
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to refresh router stats: " . $e->getMessage(), [
                'router_id' => $this->id,
                'router_name' => $this->name
            ]);
        }
    }
}
