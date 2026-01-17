<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterConfiguration extends Model
{
    protected $fillable = [
        'router_id',
        'config_type',
        'config_data',
        'applied_at',
        'status',
    ];

    protected $casts = [
        'router_id' => 'integer',
        'config_data' => 'array',
        'applied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
