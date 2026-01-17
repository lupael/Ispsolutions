<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'ip_address',
        'vendor',
        'model',
        'telnet_username',
        'telnet_password',
        'snmp_community',
        'telnet_port',
        'snmp_port',
        'max_onts',
        'description',
        'status',
    ];

    protected $hidden = [
        'telnet_password',
        'snmp_community',
    ];

    protected $casts = [
        'telnet_password' => 'encrypted',
        'snmp_community' => 'encrypted',
        'telnet_port' => 'integer',
        'snmp_port' => 'integer',
        'max_onts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
