<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikProfile extends Model
{
    protected $fillable = [
        'router_id',
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
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
