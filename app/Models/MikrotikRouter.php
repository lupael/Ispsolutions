<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MikrotikRouter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'status',
    ];
}
