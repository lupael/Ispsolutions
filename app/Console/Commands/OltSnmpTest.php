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
        $testOid = function (string $oid) use ($snmp, $host, $community, $port) {
            try {
                $results = $snmp->walk(new class($host, $community, $port) {
                    public string $ip_address;
                    public string $snmp_community;
                    public int $snmp_port;
                    public function __construct($host, $community, $port)
                    {
                        $this->ip_address = $host;
                        $this->snmp_community = $community;
                        $this->snmp_port = $port;
                    }
                }, $oid);

                if (!empty($results)) {
                    $this->info("  [SUCCESS] OID walk successful. Found " . count($results) . " records.");
                    return true;
                }

                $value = $snmp->get(new class($host, $community, $port) {
                    public string $ip_address;
                    public string $snmp_community;
                    public int $snmp_port;
                    public function __construct($host, $community, $port)
                    {
                        $this->ip_address = $host;
                        $this->snmp_community = $community;
                        $this->snmp_port = $port;
                    }
                }, $oid);

                if ($value !== null && $value !== false) {
                    $this->info("  [SUCCESS] OID get successful. Value: " . $value);
                    return true;
                }
            } catch (\Exception $e) {
                $this->error("  [ERROR] Exception: {$e->getMessage()}");
            }

            $this->warn("  [FAIL] No data returned for this OID.");
            return false;
        };

        // OIDs to check (vendor -> [label => oid])
        $vendorOids = [
            'vsol' => [
                'onu_list' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3',
                'onu_status' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.4',
                'onu_online_status' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.19',
            ],
            'huawei' => [
                'onu_list' => '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
                'onu_status' => '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15',
            ],
            'zte' => [
                'onu_list' => '.1.3.6.1.4.1.3902.1012.3.28.1.1.5',
                'onu_status' => '.1.3.6.1.4.1.3902.1012.3.28.2.1.5',
            ],
            'bdcom' => [
                'onu_list' => '.1.3.6.1.4.1.3320.101.11.1.1.3',
                'onu_status' => '.1.3.6.1.4.1.3320.101.11.1.1.7',
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
