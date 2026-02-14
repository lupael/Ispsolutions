<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Olt;
use Illuminate\Support\Facades\Log;

class OltSnmpService
{
    private bool $isSnmpAvailable;

    public function __construct()
    {
        $this->isSnmpAvailable = extension_loaded('snmp');
    }

    private const VENDOR_OIDS = [
        'vsol' => [
            'onu_list'          => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3', 
            'onu_status'        => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.9',
            'onu_rx_power'      => '.1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.3', 
            'onu_tx_power'      => '.1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.7', 
            'onu_distance'      => '.1.3.6.1.4.1.37950.1.1.5.12.2.1.4.1.2', 
            'onu_model'         => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5', 
        ],
    ];

    public function discoverOnusViaSNMP(Olt $olt): array
    {
        if (!$this->isSnmpAvailable || !$olt->snmp_community) return [];

        try {
            // Decrypt the community string ("billing")
            $community = decrypt($olt->snmp_community);
            $vendor = strtolower($olt->brand ?? 'vsol'); 
            $oids = self::VENDOR_OIDS[$vendor] ?? self::VENDOR_OIDS['vsol'];

            Log::info("Polling OLT {$olt->ip_address} with community: {$community}");

            $onuSerials = @snmp2_real_walk($olt->ip_address, $community, $oids['onu_list'], 1000000, 3);

            if (!$onuSerials) {
                Log::warning("SNMP Walk returned no data for {$olt->ip_address}. Check OLT Access Lists.");
                return [];
            }

            $onus = [];
            foreach ($onuSerials as $index => $serial) {
                $parts = explode('.', $index);
                $onuId = (int) end($parts);
                $port = (string) ($parts[count($parts) - 2] ?? '0');

                $onus[] = [
                    'serial_number' => $this->cleanValue($serial),
                    'pon_port'      => $port,
                    'onu_id'        => $onuId,
                    'status'        => 'online',
                    'signal_rx'     => $this->convertPower(@snmp2_get($olt->ip_address, $community, $oids['onu_rx_power'].'.'.$index)),
                    'signal_tx'     => $this->convertPower(@snmp2_get($olt->ip_address, $community, $oids['onu_tx_power'].'.'.$index)),
                    'distance'      => (int) @snmp2_get($olt->ip_address, $community, $oids['onu_distance'].'.'.$index),
                    'model'         => $this->cleanValue((string)@snmp2_get($olt->ip_address, $community, $oids['onu_model'].'.'.$index)),
                ];
            }
            return $onus;
        } catch (\Exception $e) {
            Log::error("SNMP Discovery Error: " . $e->getMessage());
            return [];
        }
    }

    private function cleanValue($val): string {
        return strtoupper(str_replace(['STRING: ', 'Hex-STRING: ', ' ', '"'], '', (string)$val));
    }

    private function convertPower($value): ?float {
        if ($value === false || $value === null) return null;
        $val = (float) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        if (!$val || $val == 65535) return null;
        // V-SOL scaling logic
        return ($val > 32767) ? ($val - 65536) / 100 : $val / 100;
    }
}