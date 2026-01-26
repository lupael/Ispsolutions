<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MikrotikRouter extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'ip_address',
        'host',
        'api_port',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'api_port' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pppoeUsers(): HasMany
    {
        return $this->hasMany(MikrotikPppoeUser::class, 'router_id');
    }

    // Alias for backward compatibility - returns PPPoE users (MikrotikPppoeUser models)
    // Note: This does NOT return NetworkUser models. NetworkUsers are indirectly related through PackageProfileMapping
    // If you need NetworkUser models, query through packageMappings relationship
    public function networkUsers(): HasMany
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
        return $query->where('status', 'active');
    }

    public function scopeByTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
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
     */
    public function disconnect(): void
    {
        $this->update(['status' => 'offline']);
        // Additional cleanup can be added here if needed
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
                    'status' => 'online',
                    'last_seen' => now()
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
            // For now, we just update the last_seen timestamp
            $this->update([
                'last_seen' => now(),
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
