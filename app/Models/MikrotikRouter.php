<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MikrotikRouter extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'api_port' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pppoeUsers(): HasMany
    {
        return $this->hasMany(MikrotikPppoeUser::class, 'router_id');
    }
}
