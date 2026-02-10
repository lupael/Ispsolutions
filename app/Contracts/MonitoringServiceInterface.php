<?php

declare(strict_types=1);

namespace App\Contracts;

use Carbon\Carbon;

interface MonitoringServiceInterface
{
    /**
     * Monitor a device and collect metrics
     *
     * @param string $type Device type (router/olt/onu)
     * @param int $id Device ID
     *
     * @return array Device metrics (status, cpu_usage, memory_usage, uptime)
     */
    public function monitorDevice(string $type, int $id): array;

    /**
     * Get current status of a device
     *
     * @param string $type Device type
     * @param int $id Device ID
     *
     * @return array Status information
     */
    public function getDeviceStatus(string $type, int $id): array;

    /**
     * Get status of all monitored devices
     *
     * @return array All device statuses grouped by type
     */
    public function getAllDeviceStatuses(): array;

    /**
     * Record bandwidth usage for a device
     *
     * @param string $type Device type
     * @param int $id Device ID
     * @param int $upload Upload bytes
     * @param int $download Download bytes
     *
     * @return bool Success status
     */
    public function recordBandwidthUsage(string $type, int $id, int $upload, int $download): bool;

    /**
     * Get bandwidth usage for a device within a period
     *
     * @param string $type Device type
     * @param int $id Device ID
     * @param string $period Period type (hourly/daily/weekly/monthly)
     * @param Carbon|null $startDate Start date
     * @param Carbon|null $endDate End date
     *
     * @return array Bandwidth usage data
     */
    public function getBandwidthUsage(string $type, int $id, string $period, ?Carbon $startDate = null, ?Carbon $endDate = null): array;

    /**
     * Get bandwidth usage data formatted for charting
     *
     * @param string $type Device type
     * @param int $id Device ID
     * @param string $period Period type
     *
     * @return array Chart-ready data with labels and datasets
     */
    public function getBandwidthGraph(string $type, int $id, string $period): array;

    /**
     * Aggregate bandwidth data from a source period to a target period.
     *
     * @param string $sourcePeriod The source period (e.g., 'raw', 'hourly', 'daily').
     * @param string $targetPeriod The target period (e.g., 'hourly', 'daily', 'monthly').
     *
     * @return int Number of records processed
     */
    public function aggregateData(string $sourcePeriod, string $targetPeriod): int;
}
