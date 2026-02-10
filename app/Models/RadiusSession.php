<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'nas_ip_address',
        'nas_port',
        'acct_session_id',
        'acct_start_time',
        'acct_stop_time',
        'acct_session_time',
        'acct_input_octets',
        'acct_output_octets',
        'acct_terminate_cause',
    ];

    protected $casts = [
        'acct_start_time' => 'datetime',
        'acct_stop_time' => 'datetime',
        'acct_session_time' => 'integer',
        'acct_input_octets' => 'integer',
        'acct_output_octets' => 'integer',
    ];

    /**
     * Scope a query to only include active sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('acct_stop_time');
    }

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted session duration in hours and minutes.
     *
     * @return string
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->acct_session_time) {
            return '0m';
        }

        $minutes = floor($this->acct_session_time / 60);
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
        return $this->formatBytes($this->acct_input_octets ?? 0);
    }

    /**
     * Get formatted download data in MB or GB.
     *
     * @return string
     */
    public function getDownloadFormattedAttribute(): string
    {
        return $this->formatBytes($this->acct_output_octets ?? 0);
    }

    /**
     * Get formatted total data (upload + download) in MB or GB.
     *
     * @return string
     */
    public function getTotalFormattedAttribute(): string
    {
        $total = ($this->acct_input_octets ?? 0) + ($this->acct_output_octets ?? 0);
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
