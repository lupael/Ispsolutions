<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Bandwidth Usage Model
 *
 * Stores bandwidth usage data for network devices.
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $monitorable_type
 * @property int $monitorable_id
 * @property \Illuminate\Support\Carbon $timestamp
 * @property int $upload_bytes
 * @property int $download_bytes
 * @property int $total_bytes
 * @property string $period_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BandwidthUsage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'monitorable_type',
        'monitorable_id',
        'timestamp',
        'upload_bytes',
        'download_bytes',
        'total_bytes',
        'period_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'upload_bytes' => 'integer',
        'download_bytes' => 'integer',
        'total_bytes' => 'integer',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent monitorable model (MikrotikRouter, Olt, or Onu).
     */
    public function monitorable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tenant that owns the bandwidth usage.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to filter by device type.
     */
    public function scopeDeviceType($query, string $type)
    {
        return $query->where('monitorable_type', $type);
    }

    /**
     * Scope a query to filter by device.
     */
    public function scopeDevice($query, string $type, int $id)
    {
        return $query->where('monitorable_type', $type)
            ->where('monitorable_id', $id);
    }

    /**
     * Scope a query to filter by period type.
     */
    public function scopePeriodType($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope a query to get raw data.
     */
    public function scopeRaw($query)
    {
        return $query->where('period_type', 'raw');
    }

    /**
     * Scope a query to get hourly data.
     */
    public function scopeHourly($query)
    {
        return $query->where('period_type', 'hourly');
    }

    /**
     * Scope a query to get daily data.
     */
    public function scopeDaily($query)
    {
        return $query->where('period_type', 'daily');
    }

    /**
     * Scope a query to get weekly data.
     */
    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    /**
     * Scope a query to get monthly data.
     */
    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    /**
     * Get upload in human-readable format.
     */
    public function getUploadHuman(): string
    {
        return $this->formatBytes($this->upload_bytes);
    }

    /**
     * Get download in human-readable format.
     */
    public function getDownloadHuman(): string
    {
        return $this->formatBytes($this->download_bytes);
    }

    /**
     * Get total in human-readable format.
     */
    public function getTotalHuman(): string
    {
        return $this->formatBytes($this->total_bytes);
    }

    /**
     * Format bytes to human-readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
