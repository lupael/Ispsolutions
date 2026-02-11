<?php

declare(strict_types=1);

namespace App\Contracts;

interface OltServiceInterface
{
    // Connection management
    /**
     * Establish a connection to the OLT.
     */
    public function connect(int $oltId): bool;

    /**
     * Disconnect from the OLT.
     */
    public function disconnect(int $oltId): bool;

    /**
     * Test the connection to an OLT and return latency.
     *
     * @param \App\Models\Olt $olt The OLT model instance.
     * @return array{success: bool, message: string, latency: int}
     */
    public function testConnection(Olt $olt): array;

    // ONU Discovery and Management
    /**
     * Discover all ONUs on the OLT via SNMP or other means.
     *
     * @return array<int, array{serial_number: string, pon_port: string, onu_id: int, status: string, signal_rx?: float, signal_tx?: float}>
     */
    public function discoverOnus(int $oltId): array;

    /**
     * Sync ONUs from OLT to database
     * Compares discovered ONUs with the database and creates/updates records.
     * @return array{synced: int, new: int, updated: int, failed: int} Sync summary
     */
    public function syncOnus(int $oltId): array;

    /**
     * Get detailed ONU status
     *
     * @return array{status: string, signal_rx: float|null, signal_tx: float|null, distance: int|null, uptime: int|null, last_update: string}
     */
    public function getOnuStatus(int $onuId): array;

    /**
     * Force a refresh of a specific ONU's status from the device.
     */
    public function refreshOnuStatus(int $onuId): bool;

    // ONU Operations
    /**
     * Authorize a newly discovered ONU on the OLT.
     */
    public function authorizeOnu(int $onuId): bool;

    /**
     * Unauthorize/de-register an ONU from the OLT.
     */
    public function unauthorizeOnu(int $onuId): bool;

    /**
     * Reboot a specific ONU.
     */
    public function rebootOnu(int $onuId): bool;

    // Backup and Configuration
    /**
     * Create a configuration backup of the OLT.
     */
    public function createBackup(int $oltId): bool;

    /**
     * Get list of backups for OLT
     *
     * @return array<int, array{id: int, file_path: string, file_size: int, backup_type: string, created_at: string}>
     */
    public function getBackupList(int $oltId): array;

    /**
     * Export backup and return file path
     *
     * @param string $backupId The identifier for the backup to export.
     * @return string|null File path or null on failure
     */
    public function exportBackup(int $oltId, string $backupId): ?string;

    /**
     * Apply configuration to OLT
     *
     * @param array<string, mixed> $config
     */
    public function applyConfiguration(int $oltId, array $config): bool;

    // Statistics and Monitoring
    /**
     * Get overall OLT health and performance statistics.
     *
     * @param \App\Models\Olt $olt The OLT model instance.
     * @return array{uptime: int, temperature: float|null, cpu_usage: float|null, memory_usage: float|null, total_onus: int, online_onus: int, offline_onus: int}
     */
    public function getOltStatistics(Olt $olt): array;

    /**
     * Get port utilization
     * Returns traffic statistics for each physical port on the OLT.
     * @return array<string, array{port: string, rx_bytes: int, tx_bytes: int, rx_packets: int, tx_packets: int, utilization: float}>
     */
    public function getPortUtilization(int $oltId): array;

    /**
     * Get bandwidth usage statistics
     *
     * @param string $period Period: hourly, daily, weekly, monthly
     *
     * @return array<int, array{timestamp: string, rx_bytes: int, tx_bytes: int, rx_rate: float, tx_rate: float}>
     */
    public function getBandwidthUsage(int $oltId, string $period = 'hourly'): array;
}
