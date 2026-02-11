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

            // Run vendor-specific 'enable' CLI step if present (some firmwares require privilege escalation)
            try {
                $commands = $this->getVendorCommands($olt);
                if (!empty($commands['enable'])) {
                    $connection->exec($commands['enable']);
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to run vendor enable command for OLT {$oltId}: " . $e->getMessage());
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
    public function testConnection(Olt $olt): array
    {
        $startTime = microtime(true);

        try {
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

            // Get vendor-specific commands and test command execution
            $commands = $this->getVendorCommands($olt);
            $result = $connection->exec($commands['version']);
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

            return [ // This is inside the testConnection method, but $oltId is not defined. Using $olt->id instead.
                'success' => false,
                'message' => $e->getMessage(),
                'latency' => (int) ((microtime(true) - $startTime) * 1000),
            ];
        }
    }

    /**
     * Test connection to OLT.
     */
    public function testConnection(Olt $olt): array
    {
        $startTime = microtime(true);

        try {
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

            // Get vendor-specific commands and test command execution
            $commands = $this->getVendorCommands($olt);
            $result = $connection->exec($commands['version']);
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
            Log::error("Error testing connection to OLT {$olt->id}: " . $e->getMessage());

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
        $sshConnectionCreated = false;

        try {
            $olt = Olt::findOrFail($oltId);

            // Try SNMP discovery first if SNMP is configured
            $oltSnmpService = app(\App\Services\OltSnmpService::class);

            if ($olt->snmp_community && $olt->snmp_version) {
                Log::info('Attempting SNMP-based ONU discovery', [
                    'olt_id' => $oltId,
                    'vendor' => $olt->brand ?? $olt->model,
                ]);

                $onus = $oltSnmpService->discoverOnusViaSNMP($olt);

                if (!empty($onus)) {
                    Log::info('Successfully discovered ONUs via SNMP', [
                        'olt_id' => $oltId,
                        'count' => count($onus),
                    ]);

                    return $onus;
                }

                Log::warning('SNMP discovery returned no results, falling back to SSH', [
                    'olt_id' => $oltId,
                ]);
            } else {
                Log::info('SNMP not configured, using SSH-based discovery', [
                    'olt_id' => $oltId,
                ]);
            }

            // Fallback to SSH-based discovery
            $wasAlreadyConnected = isset($this->connections[$oltId]);

            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $sshConnectionCreated = !$wasAlreadyConnected;

            $connection = $this->connections[$oltId];
            $commands = $this->getVendorCommands($olt);
            $onus = [];

            // Execute ONU state command (vendor-specific)
            $output = $connection->exec($commands['onu_state']);

            if ($output === false) {
                throw new RuntimeException('Failed to execute discovery command');
            }

            Log::debug("ONU discovery output", ['output' => substr($output, 0, 500)]);

            // Parse output based on vendor
            $onus = $this->parseOnuListOutput($output, $olt);

            Log::info('Discovered ' . count($onus) . " ONUs on OLT {$oltId} via SSH");

            return $onus;
        } catch (\Exception $e) {
            Log::error("Error discovering ONUs on OLT {$oltId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Only clean up connection if we created it in this method call
            if ($sshConnectionCreated && isset($this->connections[$oltId])) {
                $this->disconnect($oltId);
            }

            return [];
        }
    }

    /**
     * Check if OLT supports SNMP discovery.
     */
    private function canUseSNMP(Olt $olt): bool
    {
        return !empty($olt->ip_address)
            && !empty($olt->snmp_community)
            && !empty($olt->snmp_version)
            && in_array(strtolower($olt->management_protocol ?? ''), ['snmp', 'both']);
    }

    /**
     * Sync ONUs from OLT to database.
     */
    public function syncOnus(int $oltId): array
    {
        try {
            $olt = Olt::findOrFail($oltId);

            Log::info("Starting ONU sync for OLT {$oltId}", [
                'olt_name' => $olt->name,
            ]);

            $discoveredOnus = $this->discoverOnus($oltId);

            if (empty($discoveredOnus)) {
                Log::warning("No ONUs discovered on OLT {$oltId}");
                return ['synced' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0];
            }

            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;
            $failedCount = 0;

            // Process in batches to avoid memory issues with large OLT configurations
            $batchSize = 100;
            $batches = array_chunk($discoveredOnus, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                Log::debug("Processing batch " . ($batchIndex + 1) . " of " . count($batches));

                try {
                    DB::transaction(function () use ($olt, $batch, &$syncedCount, &$updatedCount, &$createdCount, &$failedCount): void {
                        foreach ($batch as $onuData) {
                            try {
                                // Validate serial number
                                if (empty($onuData['serial_number']) || strlen($onuData['serial_number']) < 8) {
                                    Log::warning("Skipping ONU with invalid serial number", [
                                        'serial_number' => $onuData['serial_number'] ?? 'empty',
                                        'pon_port' => $onuData['pon_port'] ?? 'unknown',
                                    ]);
                                    $failedCount++;
                                    continue;
                                }

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
                                        'distance' => $onuData['distance'] ?? null,
                                        'model' => $onuData['model'] ?? null,
                                        'hw_version' => $onuData['hw_version'] ?? null,
                                        'sw_version' => $onuData['sw_version'] ?? null,
                                        'last_seen_at' => now(),
                                        'last_sync_at' => now(),
                                        'tenant_id' => $olt->tenant_id,
                                    ]
                                );

                                if ($onu->wasRecentlyCreated) {
                                    $createdCount++;
                                } else {
                                    $updatedCount++;
                                }

                                $syncedCount++;
                            } catch (\Exception $e) {
                                Log::error("Error syncing individual ONU", [
                                    'serial_number' => $onuData['serial_number'] ?? 'unknown',
                                    'pon_port' => $onuData['pon_port'] ?? 'unknown',
                                    'olt_id' => $olt->id,
                                    'error' => $e->getMessage(),
                                ]);
                                $failedCount++;
                                // Continue with next ONU instead of failing entire batch
                            }
                        }
                    });
                } catch (\Exception $e) {
                    Log::error("Error processing batch " . ($batchIndex + 1), [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("ONU sync completed for OLT {$oltId}", [
                'synced' => $syncedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'failed' => $failedCount,
            ]);

            return ['synced' => $syncedCount, 'new' => $createdCount, 'updated' => $updatedCount, 'failed' => $failedCount];
        } catch (\Exception $e) {
            Log::error("Error syncing ONUs from OLT {$oltId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // In case of a total failure, return a zeroed-out result that matches the interface
            return ['synced' => 0, 'new' => 0, 'updated' => 0, 'failed' => count($discoveredOnus ?? [])];
        }
    }

    /**
     * Get detailed ONU status.
     */
    public function getOnuStatus(int $onuId): array
    {
        $sshConnectionCreated = false;

        try {
            $onu = Onu::with('olt')->findOrFail($onuId);

            // Try SNMP first if configured
            if ($this->canUseSNMP($onu->olt)) {
                $snmpService = app(OltSnmpService::class);

                Log::info('Attempting SNMP-based ONU status retrieval', [
                    'onu_id' => $onuId,
                    'olt_id' => $onu->olt_id,
                ]);

                $snmpStatus = $snmpService->getOnuOpticalPower($onu);

                if ($snmpStatus['rx_power'] !== null || $snmpStatus['tx_power'] !== null) {
                    Log::debug('Retrieved ONU status via SNMP', [
                        'onu_id' => $onuId,
                        'rx_power' => $snmpStatus['rx_power'],
                        'tx_power' => $snmpStatus['tx_power'],
                    ]);

                    return [
                        'status' => $onu->status,
                        'signal_rx' => $snmpStatus['rx_power'],
                        'signal_tx' => $snmpStatus['tx_power'],
                        'distance' => $snmpStatus['distance'],
                        'uptime' => null,
                        'last_update' => now()->toIso8601String(),
                        'method' => 'snmp',
                    ];
                }

                Log::warning('SNMP status retrieval returned no data, falling back to SSH', [
                    'onu_id' => $onuId,
                ]);
            }

            // Fallback to SSH-based status retrieval
            $wasAlreadyConnected = isset($this->connections[$onu->olt_id]);

            if (! $this->ensureConnected($onu->olt_id)) {
                Log::error("Failed to connect to OLT via SSH for ONU status", [
                    'onu_id' => $onuId,
                    'olt_id' => $onu->olt_id,
                ]);
                throw new RuntimeException("Failed to connect to OLT {$onu->olt_id}");
            }

            $sshConnectionCreated = !$wasAlreadyConnected;

            $connection = $this->connections[$onu->olt_id];
            $commands = $this->getVendorCommands($onu->olt);

            // Execute ONU status command (vendor-specific)
            $command = $this->replaceCommandPlaceholders($commands['onu_detail'], [
                'port' => $onu->pon_port,
                'id' => $onu->onu_id,
            ]);
            $output = $connection->exec($command);

            if ($output === false) {
                throw new RuntimeException('Failed to execute status command');
            }

            // Parse the detailed output from the SSH command
            $status = $this->parseOnuDetailOutput($output, $onu->olt);
            $status['method'] = 'ssh';
            $status['last_update'] = now()->toIso8601String();

            return $status;
        } catch (\Exception $e) {
            Log::error("Error getting ONU {$onuId} status: " . $e->getMessage());

            // Only clean up connection if we created it in this method call
            if ($sshConnectionCreated && isset($onu) && isset($onu->olt_id) && isset($this->connections[$onu->olt_id])) {
                $this->disconnect($onu->olt_id);
            }

            return [
                'status' => 'unknown',
                'signal_rx' => null,
                'signal_tx' => null,
                'distance' => null,
                'uptime' => null,
                'last_update' => now()->toIso8601String(),
                'method' => 'error',
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
            $commands = $this->getVendorCommands($onu->olt);

            // Execute authorization command (vendor-specific)
            $command = $this->replaceCommandPlaceholders($commands['authorize'], [
                'port' => $onu->pon_port,
                'id' => $onu->onu_id,
            ]);
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
            $commands = $this->getVendorCommands($onu->olt);

            // Execute unauthorization command (vendor-specific)
            $command = $this->replaceCommandPlaceholders($commands['unauthorize'], [
                'port' => $onu->pon_port,
                'id' => $onu->onu_id,
            ]);
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
            $commands = $this->getVendorCommands($onu->olt);

            // Execute reboot command (vendor-specific)
            $command = $this->replaceCommandPlaceholders($commands['reboot'], [
                'port' => $onu->pon_port,
                'id' => $onu->onu_id,
            ]);
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
        $sshConnectionCreated = false;

        try {
            $olt = Olt::findOrFail($oltId);

            $wasAlreadyConnected = isset($this->connections[$oltId]);

            if (! $this->ensureConnected($oltId)) {
                throw new RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $sshConnectionCreated = !$wasAlreadyConnected;

            $connection = $this->connections[$oltId];
            $commands = $this->getVendorCommands($olt);

            // Execute backup command (vendor-specific)
            $output = $connection->exec($commands['backup']);

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
            if (!Storage::put($filepath, $output)) {
                Log::error("Failed to save backup file: {$filepath}");
                throw new RuntimeException("Failed to save backup file");
            }

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

            Log::info("Created backup for OLT {$oltId}: {$filename}", [
                'size' => $fileSize,
                'path' => $filepath,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating backup for OLT {$oltId}: " . $e->getMessage());

            // Only clean up connection if we created it in this method call
            if ($sshConnectionCreated && isset($this->connections[$oltId])) {
                $this->disconnect($oltId);
            }

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

            // Check if backup file exists in storage
            if (! Storage::exists($backup->file_path)) {
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
    public function getOltStatistics(Olt $olt): array
    {
        try {
            // Eager load counts if they are not already loaded
            if (! $olt->relationLoaded('onus_count')) {
                $olt->loadCount([
                    'onus',
                    'onus as online_onus_count' => fn ($query) => $query->where('status', 'online'),
                    'onus as offline_onus_count' => fn ($query) => $query->where('status', 'offline'),
                ]);
            }

            if (! $this->ensureConnected($olt->id)) {
                throw new RuntimeException("Failed to connect to OLT {$olt->id}");
            }

            $connection = $this->connections[$olt->id];

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
            Log::error("Error getting statistics for OLT {$olt->id}: " . $e->getMessage());

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
     * Get OLT statistics.
     */
    public function getOltStatistics(Olt $olt): array
    {
        try {
            // Eager load counts if they are not already loaded
            if (! $olt->relationLoaded('onus_count')) {
                $olt->loadCount([
                'onus',
                'onus as online_onus_count' => function ($query) {
                    $query->where('status', 'online');
                },
                'onus as offline_onus_count' => function ($query) {
                    $query->where('status', 'offline');
                },
}
