<?php

namespace App\Models\Freeradius;

use Illuminate\Database\Eloquent\Model;

class nas extends Model
{
    protected $connection = 'radius';
    protected $table = 'nas';
}
