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

    /**
     * Get formatted duration
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->acctsessiontime) {
            return '0m';
        }

        $minutes = floor($this->acctsessiontime / 60);
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get formatted upload data
     */
    public function getUploadFormattedAttribute(): string
    {
        if (!$this->acctinputoctets) {
            return '0 MB';
        }

        $mb = $this->acctinputoctets / (1024 * 1024);
        
        if ($mb >= 1024) {
            return number_format($mb / 1024, 2) . ' GB';
        }

        return number_format($mb, 2) . ' MB';
    }

    /**
     * Get formatted download data
     */
    public function getDownloadFormattedAttribute(): string
    {
        if (!$this->acctoutputoctets) {
            return '0 MB';
        }

        $mb = $this->acctoutputoctets / (1024 * 1024);
        
        if ($mb >= 1024) {
            return number_format($mb / 1024, 2) . ' GB';
        }

        return number_format($mb, 2) . ' MB';
    }

    /**
     * Get formatted total data
     */
    public function getTotalFormattedAttribute(): string
    {
        $total = ($this->acctinputoctets ?? 0) + ($this->acctoutputoctets ?? 0);
        
        if (!$total) {
            return '0 MB';
        }

        $mb = $total / (1024 * 1024);
        
        if ($mb >= 1024) {
            return number_format($mb / 1024, 2) . ' GB';
        }

        return number_format($mb, 2) . ' MB';
    }
}
