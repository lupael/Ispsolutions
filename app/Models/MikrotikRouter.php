<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
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
}
