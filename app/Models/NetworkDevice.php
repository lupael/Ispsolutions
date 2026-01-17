<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'device_type',
        'ip_address',
        'latitude',
        'longitude',
        'location',
        'status',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outgoingLinks(): HasMany
    {
        return $this->hasMany(NetworkLink::class, 'source_device_id');
    }

    public function incomingLinks(): HasMany
    {
        return $this->hasMany(NetworkLink::class, 'target_device_id');
    }
}
