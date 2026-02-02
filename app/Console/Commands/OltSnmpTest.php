<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Olt;
use App\Contracts\SnmpClientInterface;
use Illuminate\Console\Command;

class OltSnmpTest extends Command
{
    protected $signature = 'olt:snmp-test {--olt-id=} {--host=} {--community=} {--version=}';

    protected $description = 'Run quick SNMP checks against an OLT to see which vendor OIDs are available.';

    public function handle(SnmpClientInterface $snmp)
    {
        $oltId = $this->option('olt-id');
        $host = $this->option('host');
        $community = $this->option('community');
        $version = $this->option('version');

        if ($oltId) {
            $olt = Olt::find($oltId);
            if (! $olt) {
                $this->error("OLT with id {$oltId} not found.");
                return 1;
            }

            $host = $olt->ip_address;
            $community = $community ?? $olt->snmp_community;
            $version = $version ?? $olt->snmp_version;

            $this->info("Running SNMP tests for OLT: {$olt->name} ({$host})");
        } else {
            if (! $host) {
                $this->error('Provide --olt-id or --host');
                return 1;
            }

            $this->info("Running SNMP tests for host: {$host}");
        }

        // Helper to test an OID: try walk() then get()
        $testOid = function (string $oid) use ($snmp, $host, $community) {
            try {
                $results = $snmp->walk(new class($host, $community) {
                    public $ip_address;
                    public $snmp_community;
                    public function __construct($ip, $community)
                    {
                        $this->ip_address = $ip;
                        $this->snmp_community = $community;
                    }
                }, $oid);

                if (! empty($results)) {
                    return ['found', $results];
                }
            } catch (\Throwable $e) {
                // ignore and try get
            }

            try {
                $value = $snmp->get(new class($host, $community) {
                    public $ip_address;
                    public $snmp_community;
                    public function __construct($ip, $community)
                    {
                        $this->ip_address = $ip;
                        $this->snmp_community = $community;
                    }
                }, $oid);

                if ($value !== null && $value !== false) {
                    return ['found', [$value]];
                }
            } catch (\Throwable $e) {
                // ignore
            }

            return ['missing', []];
        };

        // OIDs to check (vendor -> [label => oid])
        $checks = [
            'V-SOL' => [
                'EPON standard: onu_list' => '.1.3.6.1.2.1.155.1.4.1.5.1',
                'FD-OLT: nmsEponOltPonTable' => '.1.3.6.1.4.1.11606.10.101.6.1',
                'V1600D: onu_list_fallback' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3',
            ],
            'BDCOM' => [
                'BDCOM: onu_list (mac/serial)' => '.1.3.6.1.4.1.3320.101.11.1.1.2',
                'BDCOM: onu_status' => '.1.3.6.1.4.1.3320.101.11.4.1.5',
            ],
            'Huawei' => [
                'Huawei GPON: onu_list_gpon' => '.1.3.6.1.4.1.2011.5.104.1.1.1',
            ],
            'MikroTik' => [
                'MIKROTIK: mtxrOpticalRxPower' => 'MIKROTIK-MIB::mtxrOpticalRxPower',
            ],
        ];

        foreach ($checks as $vendor => $oids) {
            $this->line("\n=== {$vendor} ===");

            foreach ($oids as $label => $oid) {
                [$status, $values] = $testOid($oid);

                if ($status === 'found') {
                    $this->info("[FOUND] {$label} ({$oid}) - sample: " . (is_array($values) && count($values) ? substr((string) json_encode(array_slice($values, 0, 3)), 0, 300) : 'N/A'));
                } else {
                    $this->warn("[MISSING] {$label} ({$oid})");
                }
            }
        }

        $this->line('');
        $this->info('SNMP check completed.');

        return 0;
    }
}
