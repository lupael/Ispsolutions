<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageProfileMapping extends Model
{
    protected $fillable = [
        'package_id',
        'router_id',
        'profile_name',
        'speed_control_method',
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
}
