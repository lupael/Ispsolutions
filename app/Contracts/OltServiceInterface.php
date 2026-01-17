<?php

declare(strict_types=1);

namespace App\Contracts;

interface OltServiceInterface
{
    // Connection management
    public function connect(int $oltId): bool;

    public function disconnect(int $oltId): bool;

    /**
     * Test connection to OLT
     *
     * @return array{success: bool, message: string, latency: int}
     */
    public function testConnection(int $oltId): array;

    // ONU Discovery and Management
    /**
     * Discover all ONUs on the OLT
     *
     * @return array<int, array{serial_number: string, pon_port: string, onu_id: int, status: string, signal_rx?: float, signal_tx?: float}>
     */
    public function discoverOnus(int $oltId): array;

    /**
     * Sync ONUs from OLT to database
     *
     * @return int Number of ONUs synced
     */
    public function syncOnus(int $oltId): int;

    /**
     * Get detailed ONU status
     *
     * @return array{status: string, signal_rx: float|null, signal_tx: float|null, distance: int|null, uptime: int|null, last_update: string}
     */
    public function getOnuStatus(int $onuId): array;

    public function refreshOnuStatus(int $onuId): bool;

    // ONU Operations
    public function authorizeOnu(int $onuId): bool;

    public function unauthorizeOnu(int $onuId): bool;

    public function rebootOnu(int $onuId): bool;

    // Backup and Configuration
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
     * Get OLT statistics
     *
     * @return array{uptime: int, temperature: float|null, cpu_usage: float|null, memory_usage: float|null, total_onus: int, online_onus: int, offline_onus: int}
     */
    public function getOltStatistics(int $oltId): array;

    /**
     * Get port utilization
     *
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
