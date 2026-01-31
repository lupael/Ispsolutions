<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'ipv4_pool_id',
        'ipv6_pool_id',
        'name',
        'local_address',
        'remote_address',
        'rate_limit',
        'session_timeout',
        'idle_timeout',
        'tenant_id',
    ];

    protected $casts = [
        'router_id' => 'integer',
        'ipv4_pool_id' => 'integer',
        'ipv6_pool_id' => 'integer',
        'tenant_id' => 'integer',
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }

    public function ipv4Pool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'ipv4_pool_id');
    }

    public function ipv6Pool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'ipv6_pool_id');
    }
}
