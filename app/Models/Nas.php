<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    protected $connection = 'radius';

    protected $table = 'nas';

    public $timestamps = false;

    protected $fillable = [
        'nasname',
        'shortname',
        'type',
        'secret',
        'ports',
        'server',
        'community',
        'description',
        'api_username',
        'api_password',
        'api_port',
        'tenant_id',
        'admin_id',
        'operator_id',
    ];
}
