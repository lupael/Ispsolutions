<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'source_device_id',
        'target_device_id',
        'link_type',
        'bandwidth',
        'status',
    ];

    protected $casts = [
        'bandwidth' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sourceDevice(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'source_device_id');
    }

    public function targetDevice(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'target_device_id');
    }
}
