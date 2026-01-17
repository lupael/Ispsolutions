<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikIpPool extends Model
{
    protected $fillable = [
        'router_id',
        'name',
        'ranges',
    ];

    protected $casts = [
        'router_id' => 'integer',
        'ranges' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
