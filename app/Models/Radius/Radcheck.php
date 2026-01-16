<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class Radcheck extends Model
{
    protected $connection = 'radius';

    protected $table = 'radcheck';

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];

    public $timestamps = true;
}
