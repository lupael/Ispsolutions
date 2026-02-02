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
     * SNMP client instance (abstracted for testability)
     */
    private \App\Contracts\SnmpClientInterface $snmpClient;

    /**
     * Vendor-specific OID mappings for ONU discovery and monitoring.
     */
    private const VENDOR_OIDS = [

        'vsol' => [
            // Based on librenms MIBs (vsolution / V1600D) and RFC EPON MIBs.
            // Use EPON standard OID for ONU list when available, keep V1600D numeric as fallback.
            'onu_list' => '.1.3.6.1.2.1.155.1.4.1.5.1', // EPON: OID for ONU list (preferred)
            'onu_list_fallback' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3', // V1600D numeric fallback
            'onu_status' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.4', // ONU status (numeric fallback)
            'onu_rx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5', // RX power (numeric fallback)
            'onu_tx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.6', // TX power (numeric fallback)
            'onu_distance' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.7', // Distance (numeric fallback)

            // V‑SOL enterprise tree and commonly used FD-OLT MIB entries
            'nms_epon_olt_pon_table' => '.1.3.6.1.4.1.11606.10.101.6.1', // nmsEponOltPonTable (PON port table)
            'onu_llid_sequence' => '.1.3.6.1.4.1.11606.10.101.6.1.1.2', // ONU LLID sequence
            'onu_auth_method' => '.1.3.6.1.4.1.11606.10.101.6.1.1.3', // Authentication method
            'onu_mac_check' => '.1.3.6.1.4.1.11606.10.101.6.1.1.4', // Check ONU MAC presence

            // Additional useful OIDs from V1600D MIB (placeholders / to verify):
            'onu_model' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.8', // ONU model
            'onu_hw_version' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.9', // Hardware version
            'onu_sw_version' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.10', // Software version
            // Symbolic names: keep as optional helpers if local MIBs are installed
            'onu_list_symbolic' => 'V1600D::vsolOnuSerial',
            'onu_status_symbolic' => 'V1600D::vsolOnuRunState',
            'onu_rx_power_symbolic' => 'V1600D::vsolOnuOpticalRxPower',
            'onu_tx_power_symbolic' => 'V1600D::vsolOnuOpticalTxPower',
            'fd_olt_mib_symbolic' => 'FD-OLT-MIB::nmsEponOltPonTable', // FD-OLT-MIB symbolic fallback
        ],
        'huawei' => [
            'onu_list' => '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3', // ONU serial numbers
            'onu_list_gpon' => '.1.3.6.1.4.1.2011.5.104.1.1.1',     // GPON ONU table (alternate)
            'onu_status' => '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15', // ONU run state
            'onu_rx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4', // RX optical power
            'onu_tx_power' => '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6', // TX optical power
            'onu_distance' => '.1.3.6.1.4.1.2011.6.128.1.1.2.53.1.1', // Distance
        ],

        'mikrotik' => [
            // Based on MIKROTIK-MIB in librenms: https://github.com/librenms/librenms/blob/master/mibs/mikrotik/MIKROTIK-MIB
            // MikroTik commonly exposes optical SFP/SFP+ info via mtxrOpticalTable when present.
            // Use symbolic MIB names when local MIBs are installed; numeric fallbacks may vary by platform.
            'version' => 'MIKROTIK-MIB::mtxrFirmwareVersion.0', // firmware version (symbolic)
            'onu_list' => null, // Mikrotik doesn't have a standard GPON ONU table; platform-specific
            'onu_status' => null,
            // Optical power - use symbolic names when possible
            'onu_rx_power' => 'MIKROTIK-MIB::mtxrOpticalRxPower',
            'onu_tx_power' => 'MIKROTIK-MIB::mtxrOpticalTxPower',
            'onu_distance' => null,
            // Keep numeric placeholders only as fallback for very specific platforms (kept empty by default)
            'onu_rx_power_fallback' => '.1.3.6.1.4.1.14988.1.1.9.1.3',
            'onu_tx_power_fallback' => '.1.3.6.1.4.1.14988.1.1.9.1.4',
        ],
        'zte' => [
            'onu_list' => '.1.3.6.1.4.1.3902.1012.3.28.1.1.5', // ONU serial numbers
            'onu_status' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.5', // ONU status
            'onu_rx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10', // RX power
            'onu_tx_power' => '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.9', // TX power
            'onu_distance' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.9', // Distance
        ],
        'bdcom' => [
            // Based on librenms BDCOM MIBs: https://github.com/librenms/librenms/tree/master/mibs/bdcom
            // Use authoritative BDCOM OIDs provided for status and serial/mac
            'onu_list' => '.1.3.6.1.4.1.3320.101.11.1.1.2', // ONU MAC / Serial numbers (as provided)
            'onu_status' => '.1.3.6.1.4.1.3320.101.11.4.1.5', // ONU status (1=Online, 2=Offline)
            'onu_rx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.9', // RX power (numeric fallback)
            'onu_tx_power' => '.1.3.6.1.4.1.3320.101.108.1.1.10', // TX power (numeric fallback)
            'onu_distance' => '.1.3.6.1.4.1.3320.101.11.1.1.8', // Distance
            // Additional BDCOM OIDs that may be useful
            'onu_manufacturer' => '.1.3.6.1.4.1.3320.101.11.1.1.4',
            'onu_sw_version' => '.1.3.6.1.4.1.3320.101.108.1.1.11',
            // Symbolic names (useful when local MIBs are present):
            'onu_count_symbolic' => 'NMS-EPON-OLT-PON::activeOnuNum',
            'pon_index_symbolic' => 'NMS-EPON-OLT-PON::ponIfIndex',
            'optical_rx_symbolic' => 'NMS-OPTICAL-PORT-MIB::opIfRxPower',
            'optical_tx_symbolic' => 'NMS-OPTICAL-PORT-MIB::opIfTxPowerCurr',
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

            // Perform SNMP walk for ONU list: try preferred EPON/GPON OIDs, then vendor hint symbolics, then numeric fallbacks.
            $onuSerials = [];

            // Try preferred explicit keys first
            if (!empty($oids['onu_list'])) {
                $onuSerials = $this->snmpClient->walk($olt, $oids['onu_list']);
            }

            // For Huawei GPON try specific GPON table if present
            if (empty($onuSerials) && !empty($oids['onu_list_gpon'])) {
                try {
                    $onuSerials = $this->snmpClient->walk($olt, $oids['onu_list_gpon']);
                } catch (\Exception $e) {
                    // ignore
                }
            }

            // Try symbolic OID from MIB if still empty
            if (empty($onuSerials) && !empty($oids['onu_list_symbolic'])) {
                try {
                    $onuSerials = $this->snmpClient->walk($olt, $oids['onu_list_symbolic']);
                } catch (\Exception $e) {
                    // ignore symbolic lookup failure
                }
            }

            // Try V‑SOL enterprise PON table (nmsEponOltPonTable) if present
            if (empty($onuSerials) && !empty($oids['nms_epon_olt_pon_table'])) {
                try {
                    $onuSerials = $this->snmpClient->walk($olt, $oids['nms_epon_olt_pon_table']);
                } catch (\Exception $e) {
                    // ignore V-SOL enterprise table failure
                }
            }

            // Try od list fallback for vendors that supply a vendor-specific replacement
            if (empty($onuSerials) && !empty($oids['onu_list_fallback'])) {
                try {
                    $onuSerials = $this->snmpClient->walk($olt, $oids['onu_list_fallback']);
                } catch (\Exception $e) {
                    // ignore fallback
                }
            }

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
                        $status = null;

                        if (!empty($oids['onu_status'])) {
                            $status = $this->snmpClient->get($olt, $oids['onu_status'] . '.' . $index);
                        }

                        // Try symbolic alternative if present and primary returned nothing
                        if (($status === null || $status === false) && !empty($oids['onu_status_symbolic'])) {
                            $status = $this->snmpClient->get($olt, $oids['onu_status_symbolic'] . '.' . $index);
                        }

                        if ($status !== null && $status !== false) {
                            $onu['status'] = $this->parseOnuStatus($status, $vendor);
                        }
                    } catch (\Exception $e) {
                        // Continue without status
                    }
