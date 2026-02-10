<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Scope a query to only include active sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('acctstoptime');
    }
    /**
     * Get the user that owns the session.
     *
     * Note: This creates a cross-database relationship between the 'radius' connection
     * and the default connection. Do not eager load this relationship as it may cause
     * performance issues. Use subquery filtering instead for better performance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * Get formatted session duration in hours and minutes.
     *
     * @return string
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
     * Get formatted upload data in MB or GB.
     *
     * @return string
     */
    public function getUploadFormattedAttribute(): string
    {
        return $this->formatBytes($this->acctinputoctets ?? 0);
    }

    /**
     * Get formatted download data in MB or GB.
     *
     * @return string
     */
    public function getDownloadFormattedAttribute(): string
    {
        return $this->formatBytes($this->acctoutputoctets ?? 0);
    }

    /**
     * Get formatted total data (upload + download) in MB or GB.
     *
     * @return string
     */
    public function getTotalFormattedAttribute(): string
    {
        $total = ($this->acctinputoctets ?? 0) + ($this->acctoutputoctets ?? 0);
        return $this->formatBytes($total);
    }

    /**
     * Helper to format bytes into a human-readable string (MB/GB).
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0.00 MB';
        }

        $mb = $bytes / (1024 * 1024);

        if ($mb >= 1024) {
            return number_format($mb / 1024, 2) . ' GB';
        }

        return number_format($mb, 2) . ' MB';
    }
}
