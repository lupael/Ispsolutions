<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use App\Models\OltBackup;
use App\Models\Onu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Net\SSH2;
use RuntimeException;

class OltService implements OltServiceInterface
{
    /**
     * @var array<int, SSH2>
     */
    private array $connections = [];

    /**
     * Connect to an OLT device.
     */
    public function connect(int $oltId): bool
    {
        try {
            $olt = Olt::findOrFail($oltId);

            if (! $olt->canConnect()) {
                Log::warning("OLT {$oltId} cannot be connected: invalid configuration");

                return false;
            }

            // Close existing connection if any
            if (isset($this->connections[$oltId])) {
                $this->disconnect($oltId);
            }

            $connection = $this->createConnection($olt);

            if (! $connection->login($olt->username, $olt->password)) {
                Log::error("Failed to authenticate to OLT {$oltId}");

                return false;
            }

            $this->connections[$oltId] = $connection;

            Log::info("Successfully connected to OLT {$oltId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error connecting to OLT {$oltId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Disconnect from an OLT device.
     */
    public function disconnect(int $oltId): bool
    {
        if (isset($this->connections[$oltId])) {
            $this->connections[$oltId]->disconnect();
            unset($this->connections[$oltId]);

            Log::info("Disconnected from OLT {$oltId}");

            return true;
        }

        return false;
    }

    /**
     * Test connection to OLT.
     */
    public function testConnection(int $oltId): array
    {
        $startTime = microtime(true);

        try {
            $olt = Olt::findOrFail($oltId);

            if (! $olt->canConnect()) {
                return [
                    'success' => false,
                    'message' => 'OLT configuration is invalid or incomplete',
                    'latency' => 0,
                ];
            }

            $connection = $this->createConnection($olt);

            if (! $connection->login($olt->username, $olt->password)) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed',
                    'latency' => (int) ((microtime(true) - $startTime) * 1000),
                ];
            }

            // Test command execution
            $result = $connection->exec('show version');
            $connection->disconnect();

            $latency = (int) ((microtime(true) - $startTime) * 1000);

            if ($result === false) {
                return [
                    'success' => false,
                    'message' => 'Command execution failed',
                    'latency' => $latency,
                ];
            }

            // Update health status
            $olt->update([
                'health_status' => 'healthy',
                'last_health_check_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'latency' => $latency,
            ];
        } catch (\Exception $e) {
            Log::error("Error testing connection to OLT {$oltId}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'latency' => (int) ((microtime(true) - $startTime) * 1000),
            ];
        }
    }

    /**
     * Discover ONUs on the OLT.
     */
    public function discoverOnus(int $oltId): array
    {
        try {
            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId];
            $onus = [];

            // This is a mock implementation - real implementation would parse actual OLT output
            // Different OLT vendors (Huawei, ZTE, Fiberhome) have different commands and output formats
            $output = $connection->exec('show gpon onu state');

            if ($output === false) {
                throw new RuntimeException('Failed to execute discovery command');
            }

            // Parse output (this is simplified - real implementation would vary by vendor)
            $lines = explode("\n", $output);

            foreach ($lines as $line) {
                // Mock parsing - real implementation would parse actual OLT output format
                if (preg_match('/(\d+\/\d+\/\d+)\s+(\d+)\s+([A-Z0-9]+)\s+(\w+)/', $line, $matches)) {
                    $onus[] = [
                        'pon_port' => $matches[1],
                        'onu_id' => (int) $matches[2],
                        'serial_number' => $matches[3],
                        'status' => strtolower($matches[4]),
                    ];
                }
            }

            Log::info('Discovered ' . count($onus) . " ONUs on OLT {$oltId}");

            return $onus;
        } catch (\Exception $e) {
            Log::error("Error discovering ONUs on OLT {$oltId}: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Sync ONUs from OLT to database.
     */
    public function syncOnus(int $oltId): int
    {
        try {
            $olt = Olt::findOrFail($oltId);
            $discoveredOnus = $this->discoverOnus($oltId);
            $syncedCount = 0;

            DB::transaction(function () use ($olt, $discoveredOnus, &$syncedCount): void {
                foreach ($discoveredOnus as $onuData) {
                    $onu = Onu::updateOrCreate(
                        [
                            'olt_id' => $olt->id,
                            'serial_number' => $onuData['serial_number'],
                        ],
                        [
                            'pon_port' => $onuData['pon_port'],
                            'onu_id' => $onuData['onu_id'],
                            'status' => $onuData['status'],
                            'signal_rx' => $onuData['signal_rx'] ?? null,
                            'signal_tx' => $onuData['signal_tx'] ?? null,
                            'last_seen_at' => now(),
                            'last_sync_at' => now(),
                            'tenant_id' => $olt->tenant_id,
                        ]
                    );

                    $syncedCount++;
                }
            });

            Log::info("Synced {$syncedCount} ONUs from OLT {$oltId}");

            return $syncedCount;
        } catch (\Exception $e) {
            Log::error("Error syncing ONUs from OLT {$oltId}: " . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get detailed ONU status.
     */
    public function getOnuStatus(int $onuId): array
    {
        try {
            $onu = Onu::with('olt')->findOrFail($onuId);

            if (! $this->ensureConnected($onu->olt_id)) {
                throw new RuntimeException("Failed to connect to OLT {$onu->olt_id}");
            }

            $connection = $this->connections[$onu->olt_id];

            // Execute ONU status command (vendor-specific)
            $command = "show gpon onu detail-info gpon-onu_{$onu->pon_port}:{$onu->onu_id}";
            $output = $connection->exec($command);

            if ($output === false) {
                throw new RuntimeException('Failed to execute status command');
            }

            // Parse output (simplified - real implementation varies by vendor)
            $status = [
                'status' => $onu->status,
                'signal_rx' => $onu->signal_rx,
                'signal_tx' => $onu->signal_tx,
                'distance' => $onu->distance,
                'uptime' => null,
                'last_update' => now()->toIso8601String(),
            ];

            return $status;
        } catch (\Exception $e) {
            Log::error("Error getting ONU {$onuId} status: " . $e->getMessage());

            return [
                'status' => 'unknown',
                'signal_rx' => null,
                'signal_tx' => null,
                'distance' => null,
                'uptime' => null,
                'last_update' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Refresh ONU status from OLT.
     */
    public function refreshOnuStatus(int $onuId): bool
    {
        try {
            $status = $this->getOnuStatus($onuId);

            Onu::where('id', $onuId)->update([
                'status' => $status['status'],
                'signal_rx' => $status['signal_rx'],
                'signal_tx' => $status['signal_tx'],
                'distance' => $status['distance'],
                'last_sync_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error refreshing ONU {$onuId} status: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Authorize an ONU.
     */
    public function authorizeOnu(int $onuId): bool
    {
        try {
            $onu = Onu::with('olt')->findOrFail($onuId);

            if (! $this->ensureConnected($onu->olt_id)) {
                throw new RuntimeException("Failed to connect to OLT {$onu->olt_id}");
            }

            $connection = $this->connections[$onu->olt_id];

            // Execute authorization command (vendor-specific)
            $command = "gpon onu authorize gpon-onu_{$onu->pon_port}:{$onu->onu_id}";
            $output = $connection->exec($command);

            if ($output === false) {
                throw new RuntimeException('Failed to execute authorization command');
            }

            $onu->update(['status' => 'online']);

            Log::info("Authorized ONU {$onuId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error authorizing ONU {$onuId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Unauthorize an ONU.
     */
    public function unauthorizeOnu(int $onuId): bool
    {
        try {
            $onu = Onu::with('olt')->findOrFail($onuId);

            if (! $this->ensureConnected($onu->olt_id)) {
                throw new RuntimeException("Failed to connect to OLT {$onu->olt_id}");
            }

            $connection = $this->connections[$onu->olt_id];

            // Execute unauthorization command (vendor-specific)
            $command = "no gpon onu authorize gpon-onu_{$onu->pon_port}:{$onu->onu_id}";
            $output = $connection->exec($command);

            if ($output === false) {
                throw new RuntimeException('Failed to execute unauthorization command');
            }

            $onu->update(['status' => 'offline']);

            Log::info("Unauthorized ONU {$onuId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error unauthorizing ONU {$onuId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Reboot an ONU.
     */
    public function rebootOnu(int $onuId): bool
    {
        try {
            $onu = Onu::with('olt')->findOrFail($onuId);

            if (! $this->ensureConnected($onu->olt_id)) {
                throw new RuntimeException("Failed to connect to OLT {$onu->olt_id}");
            }

            $connection = $this->connections[$onu->olt_id];

            // Execute reboot command (vendor-specific)
            $command = "gpon onu reboot gpon-onu_{$onu->pon_port}:{$onu->onu_id}";
            $output = $connection->exec($command);

            if ($output === false) {
                throw new RuntimeException('Failed to execute reboot command');
            }

            Log::info("Rebooted ONU {$onuId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error rebooting ONU {$onuId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Create backup of OLT configuration.
     */
    public function createBackup(int $oltId): bool
    {
        try {
            $olt = Olt::findOrFail($oltId);

            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId];

            // Execute backup command (vendor-specific)
            $output = $connection->exec('display current-configuration');

            if ($output === false || empty($output)) {
                throw new RuntimeException('Failed to retrieve configuration');
            }

            // Create backup directory if it doesn't exist
            $backupDir = 'backups/olts/' . $oltId;
            Storage::makeDirectory($backupDir);

            // Generate backup filename
            $timestamp = now()->format('Y-m-d_His');
            $filename = "olt_{$oltId}_backup_{$timestamp}.cfg";
            $filepath = $backupDir . '/' . $filename;

            // Save backup
            Storage::put($filepath, $output);
            $fileSize = strlen($output);

            // Create backup record
            OltBackup::create([
                'olt_id' => $oltId,
                'file_path' => $filepath,
                'file_size' => $fileSize,
                'backup_type' => 'manual',
            ]);

            // Update OLT last backup timestamp
            $olt->update(['last_backup_at' => now()]);

            Log::info("Created backup for OLT {$oltId}: {$filename}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating backup for OLT {$oltId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Get list of backups for OLT.
     */
    public function getBackupList(int $oltId): array
    {
        try {
            $backups = OltBackup::where('olt_id', $oltId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $backups->map(function (OltBackup $backup) {
                return [
                    'id' => $backup->id,
                    'file_path' => $backup->file_path,
                    'file_size' => $backup->file_size,
                    'backup_type' => $backup->backup_type,
                    'created_at' => $backup->created_at->toIso8601String(),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error("Error getting backup list for OLT {$oltId}: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Export backup and return file path.
     */
    public function exportBackup(int $oltId, string $backupId): ?string
    {
        try {
            $backup = OltBackup::where('olt_id', $oltId)
                ->where('id', $backupId)
                ->firstOrFail();

            if (! $backup->exists()) {
                Log::warning("Backup file not found: {$backup->file_path}");

                return null;
            }

            return storage_path('app/' . $backup->file_path);
        } catch (\Exception $e) {
            Log::error("Error exporting backup {$backupId} for OLT {$oltId}: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Apply configuration to OLT.
     */
    public function applyConfiguration(int $oltId, array $config): bool
    {
        try {
            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId];

            // Enter configuration mode
            $connection->exec('configure terminal');

            // Apply each configuration command
            foreach ($config as $command) {
                $output = $connection->exec($command);

                if ($output === false) {
                    throw new RuntimeException("Failed to execute command: {$command}");
                }
            }

            // Save configuration
            $connection->exec('save');
            $connection->exec('exit');

            Log::info("Applied configuration to OLT {$oltId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error applying configuration to OLT {$oltId}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Get OLT statistics.
     */
    public function getOltStatistics(int $oltId): array
    {
        try {
            $olt = Olt::withCount([
                'onus',
                'onus as online_onus_count' => function ($query) {
                    $query->where('status', 'online');
                },
                'onus as offline_onus_count' => function ($query) {
                    $query->where('status', 'offline');
                },
            ])->findOrFail($oltId);

            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId];

            // Get system information (vendor-specific)
            $output = $connection->exec('show system');

            // Parse output for statistics (simplified)
            return [
                'uptime' => 0, // Would parse from output
                'temperature' => null,
                'cpu_usage' => null,
                'memory_usage' => null,
                'total_onus' => $olt->onus_count ?? 0,
                'online_onus' => $olt->online_onus_count ?? 0,
                'offline_onus' => $olt->offline_onus_count ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("Error getting statistics for OLT {$oltId}: " . $e->getMessage());

            return [
                'uptime' => 0,
                'temperature' => null,
                'cpu_usage' => null,
                'memory_usage' => null,
                'total_onus' => 0,
                'online_onus' => 0,
                'offline_onus' => 0,
            ];
        }
    }

    /**
     * Get port utilization.
     */
    public function getPortUtilization(int $oltId): array
    {
        try {
            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId];

            // Get port statistics (vendor-specific)
            $output = $connection->exec('show interface statistics');

            // Parse output (simplified - would parse actual OLT output)
            return [];
        } catch (\Exception $e) {
            Log::error("Error getting port utilization for OLT {$oltId}: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Get bandwidth usage statistics.
     */
    public function getBandwidthUsage(int $oltId, string $period = 'hourly'): array
    {
        // This would typically query a time-series database or stored statistics
        // For now, return empty array
        return [];
    }

    /**
     * Create SSH connection to OLT.
     */
    private function createConnection(Olt $olt): SSH2
    {
        $connection = new SSH2($olt->ip_address, $olt->port);
        $connection->setTimeout(30);

        return $connection;
    }

    /**
     * Ensure connection to OLT is established.
     */
    private function ensureConnected(int $oltId): bool
    {
        if (! isset($this->connections[$oltId])) {
            return $this->connect($oltId);
        }

        return true;
    }

    /**
     * Destructor to clean up connections.
     */
    public function __destruct()
    {
        foreach (array_keys($this->connections) as $oltId) {
            $this->disconnect($oltId);
        }
    }
}
