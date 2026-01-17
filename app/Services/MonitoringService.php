<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;
use App\Contracts\MonitoringServiceInterface;
use App\Contracts\OltServiceInterface;
use App\Models\BandwidthUsage;
use App\Models\DeviceMonitor;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Models\Onu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitoringService implements MonitoringServiceInterface
{
    public function __construct(
        private MikrotikServiceInterface $mikrotikService,
        private OltServiceInterface $oltService
    ) {}

    /**
     * Monitor a device and collect metrics
     */
    public function monitorDevice(string $type, int $id): array
    {
        $device = $this->resolveDevice($type, $id);
        
        if (!$device) {
            throw new \InvalidArgumentException("Device not found: {$type}#{$id}");
        }

        $metrics = match ($type) {
            'router' => $this->monitorRouter($device),
            'olt' => $this->monitorOlt($device),
            'onu' => $this->monitorOnu($device),
            default => throw new \InvalidArgumentException("Unsupported device type: {$type}")
        };

        // Store or update monitoring data
        DeviceMonitor::updateOrCreate(
            [
                'monitorable_type' => $this->getMorphType($type),
                'monitorable_id' => $id,
            ],
            [
                'tenant_id' => $device->tenant_id ?? null,
                'status' => $metrics['status'],
                'cpu_usage' => $metrics['cpu_usage'] ?? null,
                'memory_usage' => $metrics['memory_usage'] ?? null,
                'uptime' => $metrics['uptime'] ?? null,
                'last_check_at' => now(),
            ]
        );

        return $metrics;
    }

    /**
     * Get current status of a device
     */
    public function getDeviceStatus(string $type, int $id): array
    {
        $monitor = DeviceMonitor::where('monitorable_type', $this->getMorphType($type))
            ->where('monitorable_id', $id)
            ->first();

        if (!$monitor) {
            return ['status' => 'unknown', 'message' => 'No monitoring data available'];
        }

        return [
            'status' => $monitor->status,
            'cpu_usage' => $monitor->cpu_usage,
            'memory_usage' => $monitor->memory_usage,
            'uptime' => $monitor->uptime,
            'uptime_human' => $monitor->getUptimeHuman(),
            'last_check_at' => $monitor->last_check_at?->toDateTimeString(),
        ];
    }

    /**
     * Get status of all monitored devices
     */
    public function getAllDeviceStatuses(): array
    {
        $monitors = DeviceMonitor::with('monitorable')->get();

        return [
            'routers' => $monitors->where('monitorable_type', 'App\\Models\\MikrotikRouter')->values(),
            'olts' => $monitors->where('monitorable_type', 'App\\Models\\Olt')->values(),
            'onus' => $monitors->where('monitorable_type', 'App\\Models\\Onu')->values(),
            'summary' => [
                'total' => $monitors->count(),
                'online' => $monitors->where('status', 'online')->count(),
                'offline' => $monitors->where('status', 'offline')->count(),
                'degraded' => $monitors->where('status', 'degraded')->count(),
                'unknown' => $monitors->where('status', 'unknown')->count(),
            ],
        ];
    }

    /**
     * Record bandwidth usage for a device
     */
    public function recordBandwidthUsage(string $type, int $id, int $upload, int $download): bool
    {
        $device = $this->resolveDevice($type, $id);
        
        if (!$device) {
            Log::warning("Cannot record bandwidth for non-existent device: {$type}#{$id}");
            return false;
        }

        BandwidthUsage::create([
            'tenant_id' => $device->tenant_id ?? null,
            'monitorable_type' => $this->getMorphType($type),
            'monitorable_id' => $id,
            'timestamp' => now(),
            'upload_bytes' => $upload,
            'download_bytes' => $download,
            'total_bytes' => $upload + $download,
            'period_type' => 'raw',
        ]);

        return true;
    }

    /**
     * Get bandwidth usage for a device within a period
     */
    public function getBandwidthUsage(string $type, int $id, string $period, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays(7);
        $endDate = $endDate ?? Carbon::now();

        $query = BandwidthUsage::device($this->getMorphType($type), $id)
            ->periodType($period)
            ->dateRange($startDate, $endDate)
            ->orderBy('timestamp');

        $usages = $query->get();

        return [
            'device_type' => $type,
            'device_id' => $id,
            'period' => $period,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
            'data' => $usages->map(fn($usage) => [
                'timestamp' => $usage->timestamp->toDateTimeString(),
                'upload_bytes' => $usage->upload_bytes,
                'download_bytes' => $usage->download_bytes,
                'total_bytes' => $usage->total_bytes,
                'upload_human' => $usage->getUploadHuman(),
                'download_human' => $usage->getDownloadHuman(),
                'total_human' => $usage->getTotalHuman(),
            ]),
            'summary' => [
                'total_upload' => $usages->sum('upload_bytes'),
                'total_download' => $usages->sum('download_bytes'),
                'total_bytes' => $usages->sum('total_bytes'),
            ],
        ];
    }

    /**
     * Get bandwidth usage data formatted for charting
     */
    public function getBandwidthGraph(string $type, int $id, string $period): array
    {
        $startDate = match ($period) {
            'hourly' => Carbon::now()->subDay(),
            'daily' => Carbon::now()->subMonth(),
            'weekly' => Carbon::now()->subMonths(3),
            'monthly' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };

        $usages = BandwidthUsage::device($this->getMorphType($type), $id)
            ->periodType($period)
            ->dateRange($startDate, Carbon::now())
            ->orderBy('timestamp')
            ->get();

        $labels = [];
        $uploadData = [];
        $downloadData = [];
        $totalData = [];

        foreach ($usages as $usage) {
            $labels[] = $this->formatTimestampForPeriod($usage->timestamp, $period);
            $uploadData[] = round($usage->upload_bytes / 1024 / 1024, 2); // Convert to MB
            $downloadData[] = round($usage->download_bytes / 1024 / 1024, 2);
            $totalData[] = round($usage->total_bytes / 1024 / 1024, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Upload (MB)',
                    'data' => $uploadData,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                ],
                [
                    'label' => 'Download (MB)',
                    'data' => $downloadData,
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                ],
                [
                    'label' => 'Total (MB)',
                    'data' => $totalData,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Aggregate raw bandwidth data to hourly
     */
    public function aggregateHourlyData(): int
    {
        $cutoffTime = Carbon::now()->subHours(2);
        
        $rawData = BandwidthUsage::raw()
            ->where('timestamp', '<', $cutoffTime)
            ->get()
            ->groupBy(function ($item) {
                return $item->monitorable_type . '_' . 
                       $item->monitorable_id . '_' . 
                       $item->timestamp->format('Y-m-d H');
            });

        $processed = 0;

        foreach ($rawData as $group) {
            $first = $group->first();
            $hourStart = Carbon::parse($first->timestamp)->startOfHour();

            BandwidthUsage::create([
                'tenant_id' => $first->tenant_id,
                'monitorable_type' => $first->monitorable_type,
                'monitorable_id' => $first->monitorable_id,
                'timestamp' => $hourStart,
                'upload_bytes' => $group->sum('upload_bytes'),
                'download_bytes' => $group->sum('download_bytes'),
                'total_bytes' => $group->sum('total_bytes'),
                'period_type' => 'hourly',
            ]);

            $processed += $group->count();
        }

        // Delete aggregated raw data
        if ($processed > 0) {
            BandwidthUsage::raw()
                ->where('timestamp', '<', $cutoffTime)
                ->delete();
        }

        return $processed;
    }

    /**
     * Aggregate hourly bandwidth data to daily
     */
    public function aggregateDailyData(): int
    {
        $cutoffDate = Carbon::now()->subDays(2)->startOfDay();
        
        $hourlyData = BandwidthUsage::hourly()
            ->where('timestamp', '<', $cutoffDate)
            ->get()
            ->groupBy(function ($item) {
                return $item->monitorable_type . '_' . 
                       $item->monitorable_id . '_' . 
                       $item->timestamp->format('Y-m-d');
            });

        $processed = 0;

        foreach ($hourlyData as $group) {
            $first = $group->first();
            $dayStart = Carbon::parse($first->timestamp)->startOfDay();

            BandwidthUsage::create([
                'tenant_id' => $first->tenant_id,
                'monitorable_type' => $first->monitorable_type,
                'monitorable_id' => $first->monitorable_id,
                'timestamp' => $dayStart,
                'upload_bytes' => $group->sum('upload_bytes'),
                'download_bytes' => $group->sum('download_bytes'),
                'total_bytes' => $group->sum('total_bytes'),
                'period_type' => 'daily',
            ]);

            $processed += $group->count();
        }

        // Delete aggregated hourly data
        if ($processed > 0) {
            BandwidthUsage::hourly()
                ->where('timestamp', '<', $cutoffDate)
                ->delete();
        }

        return $processed;
    }

    /**
     * Aggregate daily bandwidth data to weekly
     */
    public function aggregateWeeklyData(): int
    {
        $cutoffDate = Carbon::now()->subWeeks(2)->startOfWeek();
        
        $dailyData = BandwidthUsage::daily()
            ->where('timestamp', '<', $cutoffDate)
            ->get()
            ->groupBy(function ($item) {
                return $item->monitorable_type . '_' . 
                       $item->monitorable_id . '_' . 
                       $item->timestamp->startOfWeek()->format('Y-m-d');
            });

        $processed = 0;

        foreach ($dailyData as $group) {
            $first = $group->first();
            $weekStart = Carbon::parse($first->timestamp)->startOfWeek();

            BandwidthUsage::create([
                'tenant_id' => $first->tenant_id,
                'monitorable_type' => $first->monitorable_type,
                'monitorable_id' => $first->monitorable_id,
                'timestamp' => $weekStart,
                'upload_bytes' => $group->sum('upload_bytes'),
                'download_bytes' => $group->sum('download_bytes'),
                'total_bytes' => $group->sum('total_bytes'),
                'period_type' => 'weekly',
            ]);

            $processed += $group->count();
        }

        // Delete aggregated daily data
        if ($processed > 0) {
            BandwidthUsage::daily()
                ->where('timestamp', '<', $cutoffDate)
                ->delete();
        }

        return $processed;
    }

    /**
     * Aggregate weekly bandwidth data to monthly
     */
    public function aggregateMonthlyData(): int
    {
        $cutoffDate = Carbon::now()->subMonths(2)->startOfMonth();
        
        $weeklyData = BandwidthUsage::weekly()
            ->where('timestamp', '<', $cutoffDate)
            ->get()
            ->groupBy(function ($item) {
                return $item->monitorable_type . '_' . 
                       $item->monitorable_id . '_' . 
                       $item->timestamp->format('Y-m');
            });

        $processed = 0;

        foreach ($weeklyData as $group) {
            $first = $group->first();
            $monthStart = Carbon::parse($first->timestamp)->startOfMonth();

            BandwidthUsage::create([
                'tenant_id' => $first->tenant_id,
                'monitorable_type' => $first->monitorable_type,
                'monitorable_id' => $first->monitorable_id,
                'timestamp' => $monthStart,
                'upload_bytes' => $group->sum('upload_bytes'),
                'download_bytes' => $group->sum('download_bytes'),
                'total_bytes' => $group->sum('total_bytes'),
                'period_type' => 'monthly',
            ]);

            $processed += $group->count();
        }

        // Delete aggregated weekly data
        if ($processed > 0) {
            BandwidthUsage::weekly()
                ->where('timestamp', '<', $cutoffDate)
                ->delete();
        }

        return $processed;
    }

    /**
     * Monitor a MikroTik router
     */
    private function monitorRouter(MikrotikRouter $router): array
    {
        try {
            $resources = $this->mikrotikService->getResources($router->id);
            
            return [
                'status' => 'online',
                'cpu_usage' => $resources['cpu-load'] ?? null,
                'memory_usage' => isset($resources['free-memory'], $resources['total-memory']) 
                    ? round((1 - ($resources['free-memory'] / $resources['total-memory'])) * 100, 2)
                    : null,
                'uptime' => $this->parseUptime($resources['uptime'] ?? null),
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to monitor router {$router->id}: {$e->getMessage()}");
            return ['status' => 'offline'];
        }
    }

    /**
     * Monitor an OLT
     */
    private function monitorOlt(Olt $olt): array
    {
        try {
            $stats = $this->oltService->getOltStatistics($olt->id);
            
            return [
                'status' => $stats['online_onus'] > 0 ? 'online' : 'degraded',
                'cpu_usage' => $stats['cpu_usage'] ?? null,
                'memory_usage' => $stats['memory_usage'] ?? null,
                'uptime' => $stats['uptime'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to monitor OLT {$olt->id}: {$e->getMessage()}");
            return ['status' => 'offline'];
        }
    }

    /**
     * Monitor an ONU
     */
    private function monitorOnu(Onu $onu): array
    {
        try {
            $status = $this->oltService->getOnuStatus($onu->id);
            
            return [
                'status' => $status['status'] === 'online' ? 'online' : 'offline',
                'cpu_usage' => null, // ONUs typically don't report CPU
                'memory_usage' => null,
                'uptime' => $status['uptime'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to monitor ONU {$onu->id}: {$e->getMessage()}");
            return ['status' => 'offline'];
        }
    }

    /**
     * Resolve device model by type and id
     */
    private function resolveDevice(string $type, int $id): ?Model
    {
        return match ($type) {
            'router' => MikrotikRouter::find($id),
            'olt' => Olt::find($id),
            'onu' => Onu::find($id),
            default => null,
        };
    }

    /**
     * Get morph type for device
     */
    private function getMorphType(string $type): string
    {
        return match ($type) {
            'router' => 'App\\Models\\MikrotikRouter',
            'olt' => 'App\\Models\\Olt',
            'onu' => 'App\\Models\\Onu',
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Parse uptime string to seconds
     */
    private function parseUptime(?string $uptime): ?int
    {
        if (!$uptime) {
            return null;
        }

        // Parse MikroTik uptime format (e.g., "1w2d3h4m5s")
        preg_match_all('/(\d+)([wdhms])/', $uptime, $matches, PREG_SET_ORDER);
        
        $seconds = 0;
        foreach ($matches as $match) {
            $value = (int)$match[1];
            $seconds += match ($match[2]) {
                'w' => $value * 604800,
                'd' => $value * 86400,
                'h' => $value * 3600,
                'm' => $value * 60,
                's' => $value,
                default => 0,
            };
        }

        return $seconds ?: null;
    }

    /**
     * Format timestamp for period
     */
    private function formatTimestampForPeriod(Carbon $timestamp, string $period): string
    {
        return match ($period) {
            'hourly' => $timestamp->format('M d, H:i'),
            'daily' => $timestamp->format('M d'),
            'weekly' => $timestamp->format('M d') . ' (W' . $timestamp->weekOfYear . ')',
            'monthly' => $timestamp->format('M Y'),
            default => $timestamp->format('Y-m-d H:i:s'),
        };
    }
}
