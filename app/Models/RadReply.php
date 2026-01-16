<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadReply extends Model
{
    protected $connection = 'radius';
    
    protected $table = 'radreply';
    
    public $timestamps = false;

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
