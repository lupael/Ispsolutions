<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    protected $connection = 'radius';
    
    protected $table = 'radacct';
    
    protected $primaryKey = 'radacctid';
    
    public $timestamps = false;

    protected $fillable = [
        'acctsessionid',
        'acctuniqueid',
        'username',
        'realm',
        'nasipaddress',
        'nasportid',
        'nasporttype',
        'acctstarttime',
        'acctupdatetime',
        'acctstoptime',
        'acctsessiontime',
        'acctauthentic',
        'connectinfo_start',
        'connectinfo_stop',
        'acctinputoctets',
        'acctoutputoctets',
        'calledstationid',
        'callingstationid',
        'acctterminatecause',
        'servicetype',
        'framedprotocol',
        'framedipaddress',
        'framedipv6address',
        'framedipv6prefix',
        'framedinterfaceid',
        'delegatedipv6prefix',
    ];

    protected $casts = [
        'radacctid' => 'integer',
        'acctsessiontime' => 'integer',
        'acctinputoctets' => 'integer',
        'acctoutputoctets' => 'integer',
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
    ];
}
