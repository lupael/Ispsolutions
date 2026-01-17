<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiscoDevice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'ip_address',
        'device_type',
        'model',
        'ios_version',
        'ssh_username',
        'ssh_password',
        'enable_password',
        'ssh_port',
        'telnet_port',
        'description',
        'status',
    ];

    protected $hidden = [
        'ssh_password',
        'enable_password',
    ];

    protected $casts = [
        'ssh_password' => 'encrypted',
        'enable_password' => 'encrypted',
        'ssh_port' => 'integer',
        'telnet_port' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
