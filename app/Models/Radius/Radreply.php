<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class Radreply extends Model
{
    protected $connection = 'radius';
    protected $table = 'radreply';

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];

    public $timestamps = true;
}
