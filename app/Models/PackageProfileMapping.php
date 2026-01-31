<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PackageProfileMapping Model
 *
 * Junction table between packages and MikroTik profiles.
 * CRITICAL: Uses BelongsToTenant trait to ensure tenant isolation and prevent data leaks.
 */
class PackageProfileMapping extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'package_id',
        'router_id',
        'profile_name',
        'speed_control_method',
        'ip_pool_id',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'router_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }

    public function ipPool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'ip_pool_id');
    }
}
