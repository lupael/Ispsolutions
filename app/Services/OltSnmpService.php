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
            'onu_list' => '.1.3.6.1.4.1.13464.1.11.4.1.1.2',      // ontSN
            'onu_status' => '.1.3.6.1.4.1.13464.1.11.4.1.1.3',      // ontStatus
            'onu_online_status' => '.1.3.6.1.4.1.13464.1.11.4.1.1.3', // ontStatus
            'onu_rx_power' => '.1.3.6.1.4.1.13464.1.11.4.1.1.22',     // ontReceivedOpticalPower
            'onu_tx_power' => '.1.3.6.1.4.1.13464.1.11.4.1.1.23',     // ontMeanOpticalLaunchPower
            'onu_distance' => '.1.3.6.1.4.1.13464.1.11.4.1.1.32',     // ontDistance
            'onu_model' => '.1.3.6.1.4.1.17409.2.3.4.1.1.10',        // onuChipVendor
            'onu_hw_version' => '.1.3.6.1.4.1.17409.2.3.4.1.1.12',     // onuChipVersion
            'onu_sw_version' => '.1.3.6.1.4.1.17409.2.3.4.1.1.13',     // onuSoftwareVersion
        ],
        'huawei' => [
            'onu_list' => '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15', // ONU run state
            'onu_rx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4', // RX optical power
            'onu_tx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6', // TX optical power
            'onu_distance' => '.1.3.6.1.4.1.2011.6.128.1.1.2.53.1.1', // Distance
            'onu_model' => '.1.3.6.1.4.1.2011.5.104.1.1.1.1.2',     // hwGonuEquipId
            'onu_hw_version' => '.1.3.6.1.4.1.2011.5.104.1.1.1.1.14',    // hwGonuChipVendor
            'onu_sw_version' => '.1.3.6.1.4.1.2011.5.104.1.1.1.1.8',     // hwGonuSwVersion (assumption)
        ],
        'zte' => [
            'onu_list' => '.1.3.6.1.4.1.3902.1012.3.28.1.1.5', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.5', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.9', // TX power
            'onu_distance' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.9', // Distance
            'onu_model' => '.1.3.6.1.4.1.3902.1082.120.1.1.1.1.2',    // zxAnPonRmOnuModel
            'onu_hw_version' => '.1.3.6.1.4.1.3902.1082.120.1.1.1.1.3',    // zxAnPonRmOnuHwVersion
            'onu_sw_version' => '.1.3.6.1.4.1.3902.1082.120.1.1.1.1.4',    // zxAnPonRmOnuSwVersion
        ],
        'bdcom' => [
            'onu_list' => '.1.3.6.1.4.1.3320.101.11.1.1.3', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.3320.101.11.1.1.7', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.9', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.10', // TX power
            'onu_distance' => '.1.3.6.1.4.1.3320.101.11.1.1.8', // Distance
            'onu_model' => '.1.3.6.1.4.1.3320.101.10.1.1.2',     // ONU Model ID
            'onu_hw_version' => '.1.3.6.1.4.1.3320.101.10.1.1.1',     // ONU Vendor ID
            'onu_sw_version' => null, // Not available
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

            $vendor = \App\Helpers\OltVendorDetector::detect($olt);
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
                    'model' => null,
                    'hw_version' => null,
                    'sw_version' => null,
                ];

                // Try to get additional details
                try {
                    $status = $this->snmpGet($olt, $oids['onu_online_status'] . '.' . $index);
                    $onu['status'] = $this->parseOnuStatus($status, $vendor);
                } catch (\Exception $e) {
                    // Continue without status
                }
                
                // Try to get optical power levels during discovery
                try {
                    $rxPower = $this->snmpGet($olt, $oids['onu_rx_power'] . '.' . $index);
                    if ($rxPower !== null && $rxPower !== false) {
                        $onu['signal_rx'] = $this->convertOpticalPower($rxPower, $vendor);
                    }
                } catch (\Exception $e) {
                    // Continue without RX power
                }
                
                try {
                    $txPower = $this->snmpGet($olt, $oids['onu_tx_power'] . '.' . $index);
                    if ($txPower !== null && $txPower !== false) {
                        $onu['signal_tx'] = $this->convertOpticalPower($txPower, $vendor);
                    }
                } catch (\Exception $e) {
                    // Continue without TX power
                }
                
                try {
                    $distance = $this->snmpGet($olt, $oids['onu_distance'] . '.' . $index);
                    if ($distance !== null && $distance !== false) {
                        $onu['distance'] = $this->convertDistance($distance, $vendor);
                    }
                } catch (\Exception $e) {
                    // Continue without distance
                }

                // Get vendor specific details
                try {
                    if (isset($oids['onu_model'])) {
                        $model = $this->snmpGet($olt, $oids['onu_model'] . '.' . $index);
                        if ($model !== null && $model !== false) {
                            $onu['model'] = $this->cleanSnmpString($model);
                        }
                    }
                } catch (\Exception $e) {
                    // Continue without model
                }

                try {
                    if (isset($oids['onu_hw_version'])) {
                        $hwVersion = $this->snmpGet($olt, $oids['onu_hw_version'] . '.' . $index);
                        if ($hwVersion !== null && $hwVersion !== false) {
                            $onu['hw_version'] = $this->cleanSnmpString($hwVersion);
                        }
                    }
                } catch (\Exception $e) {
                    // Continue without hw_version
                }

                try {
                    if (isset($oids['onu_sw_version'])) {
                        $swVersion = $this->snmpGet($olt, $oids['onu_sw_version'] . '.' . $index);
                        if ($swVersion !== null && $swVersion !== false) {
                            $onu['sw_version'] = $this->cleanSnmpString($swVersion);
                        }
                    }
                } catch (\Exception $e) {
                    // Continue without sw_version
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
     * Get ONU signal levels (alias for getOnuOpticalPower for backward compatibility).
     *
     * Note: The $olt parameter is kept for backward compatibility with code that
     * passes it, but is not used. The OLT relationship is accessed via $onu->olt.
     *
     * @param Olt $olt OLT instance (unused, kept for backward compatibility)
     * @param Onu $onu ONU instance
     * @return array Array with status, rx_power, tx_power, and distance
     */
    public function getOnuSignalLevels(Olt $olt, Onu $onu): array
    {
        $powerData = $this->getOnuOpticalPower($onu);
        
        return [
            'status' => $onu->status ?? 'unknown',
            'rx_power' => $powerData['rx_power'],
            'tx_power' => $powerData['tx_power'],
            'distance' => $powerData['distance'],
        ];
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

            $vendor = \App\Helpers\OltVendorDetector::detect($olt);
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
     * Perform SNMP walk.
     *
     * NOTE: This requires the PHP SNMP extension to be installed.
     * If the extension is not available, this will return an empty array.
     */
    private function snmpWalk(Olt $olt, string $oid): array
    {
        // Check if SNMP extension is available
        if (! extension_loaded('snmp')) {
            Log::warning('SNMP extension not loaded, cannot perform SNMP walk', [
                'olt_id' => $olt->id,
                'oid' => $oid,
            ]);

            return [];
        }

        try {
            $version = strtolower($olt->snmp_version ?? 'v2c');
            $community = $olt->snmp_community;
            $port = $olt->snmp_port ?? 161;

            $result = match ($version) {
                'v1' => @snmprealwalk($olt->ip_address, $community, $oid, 1000000, 3),
                'v2c', 'v2' => @snmp2_real_walk($olt->ip_address, $community, $oid, 1000000, 3),
                'v3' => [], // SNMPv3 requires additional parameters - not implemented yet
                default => [],
            };

            if ($result === false) {
                Log::warning('SNMP walk failed', [
                    'olt_id' => $olt->id,
                    'oid' => $oid,
                    'error' => error_get_last()['message'] ?? 'Unknown error',
                ]);

                return [];
            }

            return $result ?: [];
        } catch (\Exception $e) {
            Log::error('SNMP walk exception', [
                'olt_id' => $olt->id,
                'oid' => $oid,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Perform SNMP get.
     *
     * NOTE: This requires the PHP SNMP extension to be installed.
     * If the extension is not available, this will return null.
     */
    private function snmpGet(Olt $olt, string $oid): mixed
    {
        // Check if SNMP extension is available
        if (! extension_loaded('snmp')) {
            Log::warning('SNMP extension not loaded, cannot perform SNMP get', [
                'olt_id' => $olt->id,
                'oid' => $oid,
            ]);

            return null;
        }

        try {
            $version = strtolower($olt->snmp_version ?? 'v2c');
            $community = $olt->snmp_community;
            $port = $olt->snmp_port ?? 161;

            $result = match ($version) {
                'v1' => @snmpget($olt->ip_address, $community, $oid, 1000000, 3),
                'v2c', 'v2' => @snmp2_get($olt->ip_address, $community, $oid, 1000000, 3),
                'v3' => null, // SNMPv3 requires additional parameters - not implemented yet
                default => null,
            };

            if ($result === false) {
                Log::warning('SNMP get failed', [
                    'olt_id' => $olt->id,
                    'oid' => $oid,
                    'error' => error_get_last()['message'] ?? 'Unknown error',
                ]);

                return null;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('SNMP get exception', [
                'olt_id' => $olt->id,
                'oid' => $oid,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
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
            'vsol' => ($parts[2] ?? '0') . '/' . ($parts[3] ?? '0'),
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
     * 
     * @throws \RuntimeException If PON port format is invalid and cannot be parsed
     */
    private function buildSnmpIndex(string $ponPort, int $onuId, string $vendor): string
    {
        // Ensure ponPort is a string and has the expected format
        $ponPort = (string) $ponPort;
        $parts = explode('/', $ponPort);
        
        // Validate port format (should have at least 2 parts: slot/port or chassis/slot/port)
        if (count($parts) < 2) {
            $message = "Invalid PON port format for SNMP index: {$ponPort}. Expected format: slot/port (e.g., '0/1')";
            Log::error($message, [
                'pon_port' => $ponPort,
                'vendor' => $vendor,
                'onu_id' => $onuId,
            ]);
            throw new \RuntimeException($message);
        }

        return match ($vendor) {
            'vsol' => "{$parts[0]}/{$parts[1]}/{$onuId}",
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
    private function cleanSnmpString(string $string): string
    {
        // Remove common prefixes and clean the string
        $string = trim($string);
        $string = preg_replace('/^(STRING: |HEX-STRING: )/', '', $string);
        $string = str_replace('"', '', $string);

        return trim($string);
    }
}
