<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikPppoeUser extends Model
{
    protected $fillable = [
        'router_id',
        'username',
        'password',
        'service',
        'profile',
        'local_address',
        'remote_address',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'router_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
