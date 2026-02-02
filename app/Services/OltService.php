// (Merge these method implementations into your existing App\Services\OltService class)

use App\Services\Transport\TelnetClient;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Log;

private const VENDOR_OIDS = [
    'bdcom' => [
        'onu_status' => '1.3.6.1.4.1.3320.101.11.4.1.5',
        'onu_mac_sn' => '1.3.6.1.4.1.3320.101.11.1.1.2',
    ],
    'vsol' => [
        'onu_list' => '1.3.6.1.2.1.155.1.4.1.5.1',
    ],
    'huawei' => [
        'gpon_onu' => '1.3.6.1.4.1.2011.5.104.1.1.1',
        'onu_sn' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
    ],
];

private function createConnection(Olt $olt)
{
    $protocol = $olt->management_protocol ?? 'ssh';

    return match ($protocol) {
        'ssh' => new SSH2($olt->ip_address, $olt->port ?: 22),
        'telnet' => new TelnetClient($olt->ip_address, $olt->port ?: 23),
        default => throw new \RuntimeException("Unsupported protocol: {$protocol}"),
    };
}

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

        // SNMP quick check
        if ($olt->management_protocol === 'snmp') {
            $community = $olt->snmp_community ?: 'public';
            $ip = $olt->ip_address;
            $port = $olt->snmp_port ?: 161;

            $sysOid = '1.3.6.1.2.1.1.1.0';
            try {
                $sys = @snmpget($ip . ':' . $port, $community, $sysOid);
                if ($sys === false) {
                    return [
                        'success' => false,
                        'message' => 'SNMP request failed',
                        'latency' => (int) ((microtime(true) - $startTime) * 1000),
                    ];
                }

                $latency = (int) ((microtime(true) - $startTime) * 1000);
                $olt->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

                return ['success' => true, 'message' => 'SNMP OK', 'latency' => $latency];
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'SNMP error: ' . $e->getMessage(), 'latency' => 0];
            }
        }

        $connection = $this->createConnection($olt);

        if ($olt->management_protocol === 'ssh') {
            /** @var SSH2 $connection */
            if (! $connection->login($olt->username, $olt->password)) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed',
                    'latency' => (int) ((microtime(true) - $startTime) * 1000),
                ];
            }

            $commands = $this->getVendorCommands($olt);
            $result = $connection->exec($commands['version']);
            $connection->disconnect();

            $latency = (int) ((microtime(true) - $startTime) * 1000);

            if ($result === false) {
                return ['success' => false, 'message' => 'Command execution failed', 'latency' => $latency];
            }

            $olt->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

            return ['success' => true, 'message' => 'SSH OK', 'latency' => $latency];
        }

        if ($olt->management_protocol === 'telnet') {
            /** @var TelnetClient $connection */
            if (! $connection->connect()) {
                return ['success' => false, 'message' => "Cannot connect to {$olt->ip_address}:{$olt->port}", 'latency' => 0];
            }

            // Attempt login if credentials present
            $banner = $connection->read(1024);
            if (! empty($olt->username)) {
                $connection->write($olt->username . "\r\n");
                usleep(200_000);
                $connection->write($olt->password . "\r\n");
                usleep(200_000);
            }

            $out = $connection->exec('show version', 0.3);
            $connection->disconnect();

            $latency = (int) ((microtime(true) - $startTime) * 1000);

            if (empty($out)) {
                return ['success' => false, 'message' => 'Telnet command failed or empty response', 'latency' => $latency];
            }

            $olt->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);

            return ['success' => true, 'message' => 'Telnet OK', 'latency' => $latency];
        }

        return ['success' => false, 'message' => 'Unsupported management protocol', 'latency' => 0];
    } catch (\Exception $e) {
        Log::error('Error testing OLT connection: ' . $e->getMessage());

        return ['success' => false, 'message' => $e->getMessage(), 'latency' => 0];
    }
}

public function discoverOnus(int $oltId): array
{
    try {
        $olt = Olt::findOrFail($oltId);

        if ($olt->management_protocol === 'snmp') {
            return $this->snmpDiscoverOnus($olt);
        }

        // For SSH/Telnet we attempt vendor command output parsing. Keep original approach; this is a simplified stub.
        if ($olt->management_protocol === 'ssh') {
            if (! $this->ensureConnected($oltId)) {
                throw new \RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $connection = $this->connections[$oltId] ?? $this->createConnection($olt);
            $commands = $this->getVendorCommands($olt);
            $out = $connection->exec($commands['show_onus']);

            // TODO: parse $out vendor-specific
            return []; // implement parsing as needed
        }

        if ($olt->management_protocol === 'telnet') {
            if (! $this->ensureConnected($oltId)) {
                throw new \RuntimeException("Failed to connect to OLT {$oltId}");
            }

            $telnet = $this->connections[$oltId] ?? $this->createConnection($olt);
            $out = $telnet->exec('show onus');

            // TODO: parse $out vendor-specific
            return [];
        }

        return [];
    } catch (\Exception $e) {
        Log::error('Error discovering ONUs: ' . $e->getMessage());

        return [];
    }
}

private function snmpDiscoverOnus(Olt $olt): array
{
    $ip = $olt->ip_address;
    $community = $olt->snmp_community ?: 'public';
    $port = $olt->snmp_port ?: 161;
    $results = [];

    // BDCOM discovery using provided OIDs
    $bdcomStatusOid = self::VENDOR_OIDS['bdcom']['onu_status'];
    $bdcomMacOid = self::VENDOR_OIDS['bdcom']['onu_mac_sn'];

    try {
        $statusEntries = @snmprealwalk($ip . ':' . $port, $community, $bdcomStatusOid);
        $macEntries = @snmprealwalk($ip . ':' . $port, $community, $bdcomMacOid);

        if (is_array($macEntries)) {
            foreach ($macEntries as $oid => $val) {
                $index = (int) substr($oid, strrpos($oid, '.') + 1);
                $mac = trim(str_replace('"', '', $val));
                $statusKey = $bdcomStatusOid . '.' . $index;
                $status = $statusEntries[$statusKey] ?? null;

                $results[] = [
                    'serial_number' => $mac,
                    'onu_id' => $index,
                    'status' => ($status == 1) ? 'online' : 'offline',
                    'pon_port' => 'unknown',
                ];
            }
        }
    } catch (\Throwable $e) {
        Log::warning('SNMP BDCOM discovery failed: ' . $e->getMessage());
    }

    // V-SOL and Huawei OID parsing would be similar (use provided OIDs)
    return $results;
}