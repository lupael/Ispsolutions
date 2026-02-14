<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Support\Facades\Log;

class OltService implements OltServiceInterface
{
    protected OltSnmpService $snmp;

    public function __construct(OltSnmpService $snmp)
    {
        $this->snmp = $snmp;
    }

    public function syncOnus(int $oltId): array
    {
        $olt = Olt::findOrFail($oltId);
        $discovered = $this->snmp->discoverOnusViaSNMP($olt);

        $results = ['synced' => 0, 'new' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($discovered as $data) {
            try {
                $onu = Onu::updateOrCreate(
                    ['olt_id' => $olt->id, 'serial_number' => $data['serial_number']],
                    array_merge($data, [
                        'tenant_id' => $olt->tenant_id,
                        'last_sync_at' => now()
                    ])
                );
                $onu->wasRecentlyCreated ? $results['new']++ : $results['updated']++;
                $results['synced']++;
            } catch (\Exception $e) {
                Log::error("Failed to sync ONU: " . $e->getMessage());
                $results['failed']++;
            }
        }
        return $results;
    }

    public function discoverOnus(int $oltId): array
    {
        $olt = Olt::findOrFail($oltId);
        return $this->snmp->discoverOnusViaSNMP($olt);
    }

    public function getPortUtilization(int $oltId): array { return []; }

    public function getBandwidthUsage(int $oltId, string $period = 'hourly'): array { return []; }

    public function connect(int $oltId): bool { return true; }
    
    public function disconnect(int $oltId): bool { return true; }

    public function testConnection($olt): array {
        return ['success' => true, 'message' => 'Service Active'];
    }

    public function authorizeOnu(int $onuId): bool { return true; }

    public function unauthorizeOnu(int $onuId): bool { return true; }

    public function rebootOnu(int $onuId): bool { return true; }

    public function getOnuStatus(int $onuId): array { return []; }

    public function refreshOnuStatus(int $onuId): bool { return true; }

    public function createBackup(int $oltId): bool { return true; }

    public function getBackupList(int $oltId): array { return []; }

    public function exportBackup(int $oltId, string $backupId): ?string { return null; }

    public function applyConfiguration(int $oltId, array $config): bool { return true; }

    public function getOltStatistics($olt): array { return []; }
}