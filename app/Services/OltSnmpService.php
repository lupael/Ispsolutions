<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Support\Facades\Log;
use SNMP;

/**
 * OLT SNMP Service
 *
 * Implements SNMP-based ONU discovery and monitoring for multiple OLT vendors:
 * - VSOL
 * - Huawei
 * - ZTE
 * - BDCOM
 * - Fiberhome
 *
 * This service provides real-time RX/TX power levels and ONU status via SNMP OID walks.
 */
class OltSnmpService
{
    /**
     * SNMP OIDs for different OLT vendors.
     */
    private const VENDOR_OIDS = [
        'vsol' => [
            'onu_list' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.9',
            'onu_serial' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5',
            'onu_status' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.15',
            'onu_rx_power' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.7',
            'onu_tx_power' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.8',
            'onu_distance' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.9',
        ],
        'huawei' => [
            'onu_list' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
            'onu_serial' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
            'onu_status' => '1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15',
            'onu_rx_power' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4',
            'onu_tx_power' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6',
            'onu_distance' => '1.3.6.1.4.1.2011.6.128.1.1.2.53.1.3',
        ],
        'zte' => [
            'onu_list' => '1.3.6.1.4.1.3902.1012.3.28.1.1.2',
            'onu_serial' => '1.3.6.1.4.1.3902.1012.3.28.1.1.5',
            'onu_status' => '1.3.6.1.4.1.3902.1012.3.28.1.1.4',
            'onu_rx_power' => '1.3.6.1.4.1.3902.1012.3.50.12.1.1.10',
            'onu_tx_power' => '1.3.6.1.4.1.3902.1012.3.50.12.1.1.14',
            'onu_distance' => '1.3.6.1.4.1.3902.1012.3.28.2.1.5',
        ],
        'bdcom' => [
            'onu_list' => '1.3.6.1.4.1.3320.101.11.1.1.3',
            'onu_serial' => '1.3.6.1.4.1.3320.101.11.1.1.3',
            'onu_status' => '1.3.6.1.4.1.3320.101.11.1.1.7',
            'onu_rx_power' => '1.3.6.1.4.1.3320.101.11.1.1.22',
            'onu_tx_power' => '1.3.6.1.4.1.3320.101.11.1.1.23',
            'onu_distance' => '1.3.6.1.4.1.3320.101.11.1.1.8',
        ],
        'fiberhome' => [
            'onu_list' => '1.3.6.1.4.1.5875.800.3.27.1.1.1',
            'onu_serial' => '1.3.6.1.4.1.5875.800.3.27.1.1.5',
            'onu_status' => '1.3.6.1.4.1.5875.800.3.27.1.1.4',
            'onu_rx_power' => '1.3.6.1.4.1.5875.800.3.27.1.1.23',
            'onu_tx_power' => '1.3.6.1.4.1.5875.800.3.27.1.1.24',
            'onu_distance' => '1.3.6.1.4.1.5875.800.3.27.1.1.7',
        ],
    ];

    /**
     * Discover ONUs using SNMP OID walks.
     *
     * @return array<int, array{pon_port: string, onu_id: int, serial_number: string, status: string, signal_rx: float|null, signal_tx: float|null, distance: int|null}>
     */
    public function discoverOnusViaSNMP(Olt $olt): array
    {
        if (! $this->canUseSNMP($olt)) {
            Log::warning('SNMP configuration incomplete for OLT', [
                'olt_id' => $olt->id,
            ]);

            return [];
        }

        try {
            $vendor = $this->detectVendor($olt);
            $oids = self::VENDOR_OIDS[$vendor] ?? null;

            if (! $oids) {
                Log::warning('Unknown or unsupported OLT vendor for SNMP', [
                    'olt_id' => $olt->id,
                    'vendor' => $vendor,
                ]);

                return [];
            }

            // Create SNMP session
            $snmp = $this->createSnmpSession($olt);
            $onus = [];

            // Walk ONU list to get all ONUs
            $onuListData = @$snmp->walk($oids['onu_list']);

            if (! $onuListData) {
                Log::warning('Failed to walk ONU list via SNMP', [
                    'olt_id' => $olt->id,
                    'oid' => $oids['onu_list'],
                ]);

                return [];
            }

            // Parse each ONU
            foreach ($onuListData as $index => $value) {
                $onu = $this->parseOnuData($snmp, $oids, $index, $value, $vendor);

                if ($onu) {
                    $onus[] = $onu;
                }
            }

            Log::info('Discovered ONUs via SNMP', [
                'olt_id' => $olt->id,
                'vendor' => $vendor,
                'count' => count($onus),
            ]);

            return $onus;
        } catch (\Exception $e) {
            Log::error('Error discovering ONUs via SNMP', [
                'olt_id' => $olt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get real-time ONU signal levels via SNMP.
     *
     * @return array{rx_power: float|null, tx_power: float|null, distance: int|null, status: string}|null
     */
    public function getOnuSignalLevels(Olt $olt, Onu $onu): ?array
    {
        if (! $this->canUseSNMP($olt)) {
            return null;
        }

        try {
            $vendor = $this->detectVendor($olt);
            $oids = self::VENDOR_OIDS[$vendor] ?? null;

            if (! $oids) {
                return null;
            }

            $snmp = $this->createSnmpSession($olt);

            // Build OID index from PON port and ONU ID
            $index = $this->buildOnuIndex($onu, $vendor);

            // Get RX power
            $rxPower = $this->getSnmpValue($snmp, $oids['onu_rx_power'].'.'.$index);

            // Get TX power
            $txPower = $this->getSnmpValue($snmp, $oids['onu_tx_power'].'.'.$index);

            // Get distance
            $distance = $this->getSnmpValue($snmp, $oids['onu_distance'].'.'.$index);

            // Get status
            $statusRaw = $this->getSnmpValue($snmp, $oids['onu_status'].'.'.$index);

            $status = $this->parseOnuStatus($statusRaw, $vendor);

            // Convert power values from dBm*100 to dBm for some vendors
            $rxPowerDbm = $this->convertPowerValue($rxPower, $vendor);
            $txPowerDbm = $this->convertPowerValue($txPower, $vendor);
            $distanceMeters = $this->convertDistanceValue($distance, $vendor);

            return [
                'rx_power' => $rxPowerDbm,
                'tx_power' => $txPowerDbm,
                'distance' => $distanceMeters,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting ONU signal levels via SNMP', [
                'olt_id' => $olt->id,
                'onu_id' => $onu->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Test SNMP connectivity to OLT.
     */
    public function testSnmpConnection(Olt $olt): array
    {
        if (! $this->canUseSNMP($olt)) {
            return [
                'success' => false,
                'message' => 'SNMP configuration incomplete',
            ];
        }

        try {
            $snmp = $this->createSnmpSession($olt);

            // Try to get system description (standard OID)
            $sysDescr = @$snmp->get('1.3.6.1.2.1.1.1.0');

            if ($sysDescr !== false) {
                return [
                    'success' => true,
                    'message' => 'SNMP connection successful',
                    'system_description' => $sysDescr,
                ];
            }

            return [
                'success' => false,
                'message' => 'SNMP connection failed',
            ];
        } catch (\Exception $e) {
            Log::error('Error testing SNMP connection', [
                'olt_id' => $olt->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'SNMP connection error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check if OLT has SNMP configuration.
     */
    private function canUseSNMP(Olt $olt): bool
    {
        return ! empty($olt->ip_address)
            && ! empty($olt->snmp_community)
            && ! empty($olt->snmp_version);
    }

    /**
     * Detect OLT vendor from model or name.
     */
    private function detectVendor(Olt $olt): string
    {
        $searchText = strtolower(($olt->model ?? '').' '.($olt->name ?? '').' '.($olt->brand ?? ''));

        if (str_contains($searchText, 'vsol') || str_contains($searchText, 'v-sol')) {
            return 'vsol';
        }

        if (str_contains($searchText, 'huawei')) {
            return 'huawei';
        }

        if (str_contains($searchText, 'zte')) {
            return 'zte';
        }

        if (str_contains($searchText, 'bdcom')) {
            return 'bdcom';
        }

        if (str_contains($searchText, 'fiberhome') || str_contains($searchText, 'fiber home')) {
            return 'fiberhome';
        }

        // Default to Huawei (most common)
        return 'huawei';
    }

    /**
     * Create SNMP session for OLT.
     */
    private function createSnmpSession(Olt $olt): SNMP
    {
        $version = match ($olt->snmp_version) {
            'v1', '1' => SNMP::VERSION_1,
            'v2c', 'v2', '2c', '2' => SNMP::VERSION_2c,
            'v3', '3' => SNMP::VERSION_3,
            default => SNMP::VERSION_2c,
        };

        $snmpPort = $olt->snmp_port ?? 161;

        $snmp = new SNMP($version, $olt->ip_address.':'.$snmpPort, $olt->snmp_community);
        $snmp->valueretrieval = SNMP_VALUE_PLAIN;
        $snmp->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
        $snmp->quick_print = true;

        return $snmp;
    }

    /**
     * Parse ONU data from SNMP walk results.
     */
    private function parseOnuData(SNMP $snmp, array $oids, string $index, mixed $value, string $vendor): ?array
    {
        try {
            // Get serial number
            $serialOid = $oids['onu_serial'].'.'.$index;
            $serial = $this->getSnmpValue($snmp, $serialOid);

            if (! $serial) {
                return null;
            }

            // Get status
            $statusOid = $oids['onu_status'].'.'.$index;
            $statusRaw = $this->getSnmpValue($snmp, $statusOid);
            $status = $this->parseOnuStatus($statusRaw, $vendor);

            // Get RX/TX power
            $rxPowerRaw = $this->getSnmpValue($snmp, $oids['onu_rx_power'].'.'.$index);
            $txPowerRaw = $this->getSnmpValue($snmp, $oids['onu_tx_power'].'.'.$index);

            $rxPower = $this->convertPowerValue($rxPowerRaw, $vendor);
            $txPower = $this->convertPowerValue($txPowerRaw, $vendor);

            // Get distance
            $distanceRaw = $this->getSnmpValue($snmp, $oids['onu_distance'].'.'.$index);
            $distance = $this->convertDistanceValue($distanceRaw, $vendor);

            // Parse PON port and ONU ID from index
            $ponInfo = $this->parseIndexToPonInfo($index, $vendor);

            return [
                'pon_port' => $ponInfo['pon_port'],
                'onu_id' => $ponInfo['onu_id'],
                'serial_number' => $serial,
                'status' => $status,
                'signal_rx' => $rxPower,
                'signal_tx' => $txPower,
                'distance' => $distance,
            ];
        } catch (\Exception $e) {
            Log::debug('Error parsing ONU data', [
                'index' => $index,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get SNMP value safely.
     */
    private function getSnmpValue(SNMP $snmp, string $oid): mixed
    {
        try {
            return @$snmp->get($oid);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse ONU status from SNMP value.
     */
    private function parseOnuStatus(mixed $statusRaw, string $vendor): string
    {
        if ($statusRaw === null || $statusRaw === false) {
            return 'unknown';
        }

        $statusInt = (int) $statusRaw;

        // Different vendors use different status codes
        return match ($vendor) {
            'vsol' => match ($statusInt) {
                1 => 'online',
                2 => 'offline',
                3 => 'dying_gasp',
                default => 'unknown',
            },
            'huawei' => match ($statusInt) {
                1 => 'online',
                2 => 'offline',
                3 => 'dying_gasp',
                default => 'unknown',
            },
            'zte' => match ($statusInt) {
                1 => 'online',
                2 => 'offline',
                default => 'unknown',
            },
            'bdcom' => match ($statusInt) {
                1 => 'online',
                2 => 'offline',
                default => 'unknown',
            },
            'fiberhome' => match ($statusInt) {
                1 => 'online',
                2 => 'offline',
                default => 'unknown',
            },
            default => 'unknown',
        };
    }

    /**
     * Convert power value to dBm.
     */
    private function convertPowerValue(mixed $value, string $vendor): ?float
    {
        if ($value === null || $value === false) {
            return null;
        }

        $powerValue = (float) $value;

        // Some vendors return power * 100, others return raw dBm
        return match ($vendor) {
            'vsol', 'huawei' => round($powerValue / 100, 2),
            'zte', 'bdcom', 'fiberhome' => round($powerValue / 10, 2),
            default => round($powerValue, 2),
        };
    }

    /**
     * Convert distance value to meters.
     */
    private function convertDistanceValue(mixed $value, string $vendor): ?int
    {
        if ($value === null || $value === false) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Build ONU index for SNMP queries.
     */
    private function buildOnuIndex(Onu $onu, string $vendor): string
    {
        // Parse PON port format (e.g., "0/1/2" or "1/1" or "2")
        $parts = explode('/', $onu->pon_port);

        return match ($vendor) {
            'vsol' => implode('.', $parts).'.'.$onu->onu_id,
            'huawei' => implode('.', array_pad($parts, 3, 0)).'.'.$onu->onu_id,
            'zte' => implode('.', $parts).'.'.$onu->onu_id,
            'bdcom' => implode('.', $parts).'.'.$onu->onu_id,
            'fiberhome' => implode('.', $parts).'.'.$onu->onu_id,
            default => implode('.', $parts).'.'.$onu->onu_id,
        };
    }

    /**
     * Parse SNMP index to PON port and ONU ID.
     */
    private function parseIndexToPonInfo(string $index, string $vendor): array
    {
        $parts = explode('.', $index);

        return match ($vendor) {
            'vsol' => [
                'pon_port' => implode('/', array_slice($parts, 0, -1)),
                'onu_id' => (int) end($parts),
            ],
            'huawei' => [
                'pon_port' => implode('/', array_slice($parts, 0, 3)),
                'onu_id' => (int) end($parts),
            ],
            default => [
                'pon_port' => implode('/', array_slice($parts, 0, -1)),
                'onu_id' => (int) end($parts),
            ],
        };
    }
}
