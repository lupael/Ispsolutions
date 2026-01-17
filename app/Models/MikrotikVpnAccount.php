<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikVpnAccount extends Model
{
    protected $fillable = [
        'router_id',
        'username',
        'password',
        'profile',
        'enabled',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'router_id' => 'integer',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }
}
