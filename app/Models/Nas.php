<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'nas';

    protected $fillable = [
        'tenant_id',
        'name',
        'nas_name',
        'short_name',
        'type',
        'ports',
        'secret',
        'server',
        'community',
        'description',
        'status',
    ];

    protected $hidden = [
        'secret',
        'community',
    ];

    protected $casts = [
        'ports' => 'integer',
        'secret' => 'encrypted',
        'community' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
