<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkUser extends Model
{
    protected $fillable = [
        'username',
        'password',
        'service_type',
        'package_id',
        'status',
        'user_id',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
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
}
