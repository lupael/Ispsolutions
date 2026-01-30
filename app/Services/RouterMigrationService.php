<?php

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\Radius\Radcheck;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class RouterMigrationService
{
    protected $mikrotik;
    
    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }
    
    private function createClient(MikrotikRouter $router): Client
    {
        $config = (new Config())
            ->set('host', $router->ip_address)
            ->set('user', $router->username)
            ->set('pass', $router->password)
            ->set('port', $router->api_port)
            ->set('timeout', config('services.mikrotik.timeout', 60));
        
        return new Client($config);
    }
    
    public function verifyRadiusConnectivity(MikrotikRouter $router): bool
    {
        try {
            $client = $this->createClient($router);
            
            // Check if RADIUS server is configured by querying router
            $query = new Query('/radius/print');
            $radiusServers = $client->query($query)->read();
            
            foreach ($radiusServers as $server) {
                if (isset($server['address']) && $server['address'] === config('radius.server')) {
                    // Test connectivity by pinging RADIUS server
                    $pingQuery = (new Query('/ping'))
                        ->equal('address', config('radius.server'))
                        ->equal('count', '3');
                    
                    $pingResult = $client->query($pingQuery)->read();
                    
                    if (!empty($pingResult)) {
                        foreach ($pingResult as $result) {
                            if (isset($result['received']) && (int)$result['received'] > 0) {
                                return true;
                            }
                        }
                    }
                }
            }
            
            return false;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to verify RADIUS connectivity for router {$router->id} ({$router->name}): " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to verify RADIUS connectivity for router {$router->id} ({$router->name}): " . $e->getMessage());
            return false;
        }
    }
    
    public function backupPppSecrets(MikrotikRouter $router): string
    {
        try {
            $client = $this->createClient($router);
            
            // Get all PPP secrets
            $query = new Query('/ppp/secret/print');
            $secrets = $client->query($query)->read();
            
            // Create backup file
            $timestamp = now()->format('Y-m-d_His');
            $filename = "router_{$router->id}_ppp_secrets_{$timestamp}.json";
            $path = "backups/router-migrations/{$filename}";
            
            Storage::put($path, json_encode($secrets, JSON_PRETTY_PRINT));
            
            // Also store rollback info in cache
            cache()->put("router:{$router->id}:migration:backup", $path, now()->addDays(7));
            
            return $path;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to backup PPP secrets for router {$router->id} ({$router->name}): " . $e->getMessage());
            // Return empty path on failure
            return '';
        } catch (\Exception $e) {
            Log::error("Failed to backup PPP secrets for router {$router->id} ({$router->name}): " . $e->getMessage());
            return '';
        }
    }
    
    public function configureRadiusAuth(MikrotikRouter $router): bool
    {
        try {
            $client = $this->createClient($router);
            
            // Check if RADIUS is already configured
            $query = new Query('/radius/print');
            $radiusServers = $client->query($query)->read();
            
            $radiusExists = false;
            
            foreach ($radiusServers as $server) {
                if (isset($server['address']) && $server['address'] === config('radius.server')) {
                    $radiusExists = true;
                    break;
                }
            }
            
            // Add RADIUS server if not exists
            if (!$radiusExists) {
                $addQuery = (new Query('/radius/add'))
                    ->equal('address', config('radius.server'))
                    ->equal('secret', config('radius.secret'))
                    ->equal('service', 'ppp')
                    ->equal('authentication-port', (string)config('radius.auth_port', 1812))
                    ->equal('accounting-port', (string)config('radius.acct_port', 1813));
                
                $client->query($addQuery)->read();
            }
            
            // Enable RADIUS for PPP
            $aaaQuery = (new Query('/ppp/aaa/set'))
                ->equal('use-radius', 'yes');
            
            $client->query($aaaQuery)->read();
            
            return true;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to configure RADIUS auth for router {$router->id} ({$router->name}): " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to configure RADIUS auth for router {$router->id} ({$router->name}): " . $e->getMessage());
            return false;
        }
    }
    
    public function testRadiusAuth(MikrotikRouter $router, string $username): bool
    {
        try {
            // Check if the user exists in radcheck
            $user = Radcheck::where('username', $username)->first();
            
            return $user !== null;
        } catch (\Exception $e) {
            Log::error("Failed to test RADIUS auth: " . $e->getMessage());
            return false;
        }
    }
    
    public function disableLocalSecrets(MikrotikRouter $router): int
    {
        try {
            $client = $this->createClient($router);
            
            $query = new Query('/ppp/secret/print');
            $secrets = $client->query($query)->read();
            
            $count = 0;
            
            foreach ($secrets as $secret) {
                if (isset($secret['.id'])) {
                    try {
                        $disableQuery = (new Query('/ppp/secret/disable'))
                            ->equal('.id', $secret['.id']);
                        
                        $client->query($disableQuery)->read();
                        $count++;
                    } catch (\Exception $e) {
                        Log::warning("Failed to disable secret {$secret['.id']} on router {$router->id} ({$router->name}): " . $e->getMessage());
                    }
                }
            }
            
            return $count;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to disable local secrets for router {$router->id} ({$router->name}): " . $e->getMessage());
            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to disable local secrets for router {$router->id} ({$router->name}): " . $e->getMessage());
            return 0;
        }
    }
    
    public function disconnectActiveSessions(MikrotikRouter $router): int
    {
        try {
            $client = $this->createClient($router);
            
            $query = new Query('/ppp/active/print');
            $activeSessions = $client->query($query)->read();
            
            $count = 0;
            
            foreach ($activeSessions as $session) {
                if (isset($session['.id'])) {
                    try {
                        $removeQuery = (new Query('/ppp/active/remove'))
                            ->equal('.id', $session['.id']);
                        
                        $client->query($removeQuery)->read();
                        $count++;
                    } catch (\Exception $e) {
                        Log::warning("Failed to disconnect session {$session['.id']} on router {$router->id} ({$router->name}): " . $e->getMessage());
                    }
                }
            }
            
            return $count;
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to disconnect active sessions for router {$router->id} ({$router->name}): " . $e->getMessage());
            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to disconnect active sessions for router {$router->id} ({$router->name}): " . $e->getMessage());
            return 0;
        }
    }
    
    public function verifyMigration(MikrotikRouter $router): array
    {
        try {
            $client = $this->createClient($router);
            
            // Check RADIUS is enabled
            $query = new Query('/ppp/aaa/print');
            $aaa = $client->query($query)->read();
            
            if (!isset($aaa[0]['use-radius']) || $aaa[0]['use-radius'] !== 'true') {
                return [
                    'success' => false,
                    'message' => 'RADIUS is not enabled for PPP'
                ];
            }
            
            // Count active sessions
            $sessionsQuery = new Query('/ppp/active/print');
            $activeSessions = $client->query($sessionsQuery)->read();
            
            return [
                'success' => true,
                'active_sessions' => count($activeSessions),
            ];
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            return [
                'success' => false,
                'message' => "Connection failed for router {$router->id} ({$router->name}): " . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Error for router {$router->id} ({$router->name}): " . $e->getMessage(),
            ];
        }
    }
    
    public function rollback(MikrotikRouter $router): bool
    {
        try {
            // Get backup path
            $backupPath = cache()->get("router:{$router->id}:migration:backup");
            
            if (!$backupPath) {
                throw new \Exception("No backup found for router {$router->id}");
            }
            
            $client = $this->createClient($router);
            
            // Restore PPP secrets from backup
            $secrets = json_decode(Storage::get($backupPath), true);
            
            foreach ($secrets as $secret) {
                if (isset($secret['.id'])) {
                    try {
                        // Re-enable disabled secrets
                        $enableQuery = (new Query('/ppp/secret/enable'))
                            ->equal('.id', $secret['.id']);
                        
                        $client->query($enableQuery)->read();
                    } catch (\Exception $e) {
                        Log::warning("Failed to re-enable secret {$secret['.id']} on router {$router->id} ({$router->name}): " . $e->getMessage());
                    }
                }
            }
            
            // Disable RADIUS
            $aaaQuery = (new Query('/ppp/aaa/set'))
                ->equal('use-radius', 'no');
            
            $client->query($aaaQuery)->read();
            
            Log::info("Successfully rolled back router {$router->id} ({$router->name}) migration");
            return true;
            
        } catch (\RouterOS\Exceptions\ConnectException $e) {
            Log::error("Failed to rollback router {$router->id} ({$router->name}) migration: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to rollback router {$router->id} ({$router->name}) migration: " . $e->getMessage());
            return false;
        }
    }
}
