<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikQueue extends Model
{
    protected $fillable = [
        'router_id',
        'name',
        'target',
        'parent',
        'max_limit',
        'burst_limit',
        'burst_threshold',
        'burst_time',
        'priority',
    ];

    protected $casts = [
        'router_id' => 'integer',
        'burst_time' => 'integer',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
