<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Support\Facades\Log;

/**
 * OLT SNMP Multi-Vendor Service
 *
 * Provides SNMP-based discovery and monitoring for multiple OLT vendors:
 * - VSOL
 * - Huawei
 * - ZTE
 * - BDCOM
 */
class OltSnmpService
{
    /**
     * Vendor-specific OID mappings for ONU discovery and monitoring.
     */
    private const VENDOR_OIDS = [
        'vsol' => [
            'onu_list' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.4', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.6', // TX power
            'onu_distance' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.7', // Distance
        ],
        'huawei' => [
            'onu_list' => '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15', // ONU run state
            'onu_rx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4', // RX optical power
            'onu_tx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6', // TX optical power
            'onu_distance' => '.1.3.6.1.4.1.2011.6.128.1.1.2.53.1.1', // Distance
        ],
        'zte' => [
            'onu_list' => '.1.3.6.1.4.1.3902.1012.3.28.1.1.5', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.5', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.9', // TX power
            'onu_distance' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.9', // Distance
        ],
        'bdcom' => [
            'onu_list' => '.1.3.6.1.4.1.3320.101.11.1.1.3', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.3320.101.11.1.1.7', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.9', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.10', // TX power
            'onu_distance' => '.1.3.6.1.4.1.3320.101.11.1.1.8', // Distance
        ],
    ];

    /**
     * Discover ONUs via SNMP OID walk.
     *
     * @return array Array of discovered ONUs with their details
     */
    public function discoverOnusViaSNMP(Olt $olt): array
    {
        try {
            if (! $this->canUseSNMP($olt)) {
                throw new \RuntimeException('SNMP is not configured for this OLT');
            }

            $vendor = $this->detectVendor($olt);
            $oids = self::VENDOR_OIDS[$vendor] ?? null;

            if (! $oids) {
                throw new \RuntimeException("Unsupported vendor: {$vendor}");
            }

            // Perform SNMP walk for ONU list
            $onuSerials = $this->snmpWalk($olt, $oids['onu_list']);

            if (empty($onuSerials)) {
                Log::warning('No ONUs discovered via SNMP', [
                    'olt_id' => $olt->id,
                    'vendor' => $vendor,
                ]);

                return [];
            }

            $discoveredOnus = [];

            foreach ($onuSerials as $index => $serial) {
                $onu = [
                    'serial_number' => $this->cleanSerialNumber($serial),
                    'pon_port' => $this->extractPonPort($index, $vendor),
                    'onu_id' => $this->extractOnuId($index, $vendor),
                    'status' => 'discovered',
                    'signal_rx' => null,
                    'signal_tx' => null,
                    'distance' => null,
                ];

                // Try to get additional details
                try {
                    $status = $this->snmpGet($olt, $oids['onu_status'] . '.' . $index);
                    $onu['status'] = $this->parseOnuStatus($status, $vendor);
                } catch (\Exception $e) {
                    // Continue without status
                }

                $discoveredOnus[] = $onu;
            }

            Log::info('Discovered ONUs via SNMP', [
                'olt_id' => $olt->id,
                'vendor' => $vendor,
                'count' => count($discoveredOnus),
            ]);

            return $discoveredOnus;

        } catch (\Exception $e) {
            Log::error('Error discovering ONUs via SNMP', [
                'olt_id' => $olt->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get real-time RX/TX power levels for an ONU.
     *
     * @return array Array with rx_power, tx_power, and distance
     */
    public function getOnuOpticalPower(Onu $onu): array
    {
        try {
            $olt = $onu->olt;

            if (! $this->canUseSNMP($olt)) {
                throw new \RuntimeException('SNMP is not configured for this OLT');
            }

            $vendor = $this->detectVendor($olt);
            $oids = self::VENDOR_OIDS[$vendor] ?? null;

            if (! $oids) {
                throw new \RuntimeException("Unsupported vendor: {$vendor}");
            }

            // Build SNMP index from PON port and ONU ID
            $index = $this->buildSnmpIndex($onu->pon_port, $onu->onu_id, $vendor);

            $result = [
                'rx_power' => null,
                'tx_power' => null,
                'distance' => null,
            ];

            // Get RX power
            try {
                $rxPower = $this->snmpGet($olt, $oids['onu_rx_power'] . '.' . $index);
                $result['rx_power'] = $this->convertOpticalPower($rxPower, $vendor);
            } catch (\Exception $e) {
                Log::warning('Failed to get RX power', [
                    'onu_id' => $onu->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Get TX power
            try {
                $txPower = $this->snmpGet($olt, $oids['onu_tx_power'] . '.' . $index);
                $result['tx_power'] = $this->convertOpticalPower($txPower, $vendor);
            } catch (\Exception $e) {
                Log::warning('Failed to get TX power', [
                    'onu_id' => $onu->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Get distance
            try {
                $distance = $this->snmpGet($olt, $oids['onu_distance'] . '.' . $index);
                $result['distance'] = $this->convertDistance($distance, $vendor);
            } catch (\Exception $e) {
                Log::warning('Failed to get distance', [
                    'onu_id' => $onu->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Error getting ONU optical power', [
                'onu_id' => $onu->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'rx_power' => null,
                'tx_power' => null,
                'distance' => null,
            ];
        }
    }

    /**
     * Check if SNMP can be used for this OLT.
     */
    private function canUseSNMP(Olt $olt): bool
    {
        return ! empty($olt->snmp_community)
            && ! empty($olt->snmp_version)
            && in_array(strtolower($olt->management_protocol ?? ''), ['snmp', 'both']);
    }

    /**
     * Detect OLT vendor from brand or model.
     *
     * @return string Vendor identifier (vsol, huawei, zte, bdcom)
     */
    private function detectVendor(Olt $olt): string
    {
        $searchText = strtolower(($olt->brand ?? '') . ' ' . ($olt->model ?? '') . ' ' . ($olt->name ?? ''));

        if (str_contains($searchText, 'vsol') || str_contains($searchText, 'v-sol')) {
            return 'vsol';
        } elseif (str_contains($searchText, 'huawei')) {
            return 'huawei';
        } elseif (str_contains($searchText, 'zte')) {
            return 'zte';
        } elseif (str_contains($searchText, 'bdcom')) {
            return 'bdcom';
        }

        // Default to Huawei (most common)
        return 'huawei';
    }

    /**
     * Perform SNMP walk.
     * This is a placeholder - real implementation would use PHP SNMP functions.
     */
    private function snmpWalk(Olt $olt, string $oid): array
    {
        // Placeholder implementation
        // Real implementation would use: snmp2_real_walk() or snmp3_real_walk()

        Log::info('SNMP walk placeholder', [
            'olt_id' => $olt->id,
            'oid' => $oid,
        ]);

        // Return empty array for now
        return [];
    }

    /**
     * Perform SNMP get.
     * This is a placeholder - real implementation would use PHP SNMP functions.
     */
    private function snmpGet(Olt $olt, string $oid): mixed
    {
        // Placeholder implementation
        // Real implementation would use: snmp2_get() or snmp3_get()

        Log::info('SNMP get placeholder', [
            'olt_id' => $olt->id,
            'oid' => $oid,
        ]);

        return null;
    }

    /**
     * Clean serial number from SNMP response.
     */
    private function cleanSerialNumber(string $serial): string
    {
        // Remove common prefixes and clean the serial
        $serial = trim($serial);
        $serial = preg_replace('/^(STRING: |HEX-STRING: )/', '', $serial);
        $serial = str_replace(' ', '', $serial);

        return strtoupper($serial);
    }

    /**
     * Extract PON port from SNMP index.
     */
    private function extractPonPort(string $index, string $vendor): string
    {
        // Vendor-specific index parsing
        // This is simplified - real implementation varies by vendor

        $parts = explode('.', $index);

        return match ($vendor) {
            'vsol' => $parts[0] ?? '0/0',
            'huawei' => ($parts[0] ?? '0') . '/' . ($parts[1] ?? '0'),
            'zte' => $parts[0] ?? '0/0',
            'bdcom' => $parts[0] ?? '0/0',
            default => '0/0',
        };
    }

    /**
     * Extract ONU ID from SNMP index.
     */
    private function extractOnuId(string $index, string $vendor): int
    {
        $parts = explode('.', $index);

        return (int) ($parts[2] ?? 0);
    }

    /**
     * Build SNMP index from PON port and ONU ID.
     */
    private function buildSnmpIndex(string $ponPort, int $onuId, string $vendor): string
    {
        $parts = explode('/', $ponPort);

        return match ($vendor) {
            'vsol' => "{$parts[0]}.{$parts[1]}.{$onuId}",
            'huawei' => "{$parts[0]}.{$parts[1]}.{$onuId}",
            'zte' => "{$parts[0]}.{$parts[1]}.{$onuId}",
            'bdcom' => "{$parts[0]}.{$parts[1]}.{$onuId}",
            default => "{$parts[0]}.{$parts[1]}.{$onuId}",
        };
    }

    /**
     * Parse ONU status from SNMP response.
     *
     * @param mixed $status
     */
    private function parseOnuStatus($status, string $vendor): string
    {
        if ($status === null) {
            return 'unknown';
        }

        // Vendor-specific status code parsing
        return match ($vendor) {
            'vsol' => $status == 1 ? 'online' : 'offline',
            'huawei' => $status == 1 ? 'online' : 'offline',
            'zte' => $status == 1 ? 'online' : 'offline',
            'bdcom' => $status == 1 ? 'online' : 'offline',
            default => 'unknown',
        };
    }

    /**
     * Convert optical power value to dBm.
     *
     * @param mixed $value
     */
    private function convertOpticalPower($value, string $vendor): ?float
    {
        if ($value === null) {
            return null;
        }

        // Vendor-specific power conversion
        // Most vendors return power in 0.01 dBm units
        return (float) $value / 100;
    }

    /**
     * Convert distance value to meters.
     *
     * @param mixed $value
     */
    private function convertDistance($value, string $vendor): ?int
    {
        if ($value === null) {
            return null;
        }

        // Vendor-specific distance conversion
        return (int) $value;
    }
}
