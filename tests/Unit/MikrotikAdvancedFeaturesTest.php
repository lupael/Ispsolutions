<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\MikrotikIpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikQueue;
use App\Models\MikrotikRouter;
use App\Models\MikrotikVpnAccount;
use App\Models\RouterConfiguration;
use App\Services\MikrotikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MikrotikAdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private MikrotikService $mikrotikService;

    private MikrotikRouter $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mikrotikService = new MikrotikService;

        $this->router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);
    }

    public function test_create_ppp_profile_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/profile/add' => Http::response(['success' => true], 200),
        ]);

        $profileData = [
            'name' => 'test-profile',
            'local_address' => '192.168.1.1',
            'remote_address' => '192.168.1.0/24',
            'rate_limit' => '10M/10M',
            'session_timeout' => 3600,
            'idle_timeout' => 600,
        ];

        $result = $this->mikrotikService->createPppProfile($this->router->id, $profileData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('mikrotik_profiles', [
            'router_id' => $this->router->id,
            'name' => 'test-profile',
        ]);
    }

    public function test_sync_profiles_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/profile/print' => Http::response([
                'profiles' => [
                    [
                        'name' => 'profile1',
                        'local-address' => '192.168.1.1',
                        'remote-address' => '192.168.1.0/24',
                        'rate-limit' => '5M/5M',
                    ],
                    [
                        'name' => 'profile2',
                        'local-address' => '192.168.2.1',
                        'remote-address' => '192.168.2.0/24',
                        'rate-limit' => '10M/10M',
                    ],
                ],
            ], 200),
        ]);

        $count = $this->mikrotikService->syncProfiles($this->router->id);

        $this->assertEquals(2, $count);
        $this->assertDatabaseHas('mikrotik_profiles', [
            'router_id' => $this->router->id,
            'name' => 'profile1',
        ]);
        $this->assertDatabaseHas('mikrotik_profiles', [
            'router_id' => $this->router->id,
            'name' => 'profile2',
        ]);
    }

    public function test_create_ip_pool_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ip/pool/add' => Http::response(['success' => true], 200),
        ]);

        $poolData = [
            'name' => 'test-pool',
            'ranges' => ['192.168.1.10-192.168.1.100', '192.168.1.200-192.168.1.254'],
        ];

        $result = $this->mikrotikService->createIpPool($this->router->id, $poolData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('mikrotik_ip_pools', [
            'router_id' => $this->router->id,
            'name' => 'test-pool',
        ]);
    }

    public function test_sync_ip_pools_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ip/pool/print' => Http::response([
                'pools' => [
                    [
                        'name' => 'pool1',
                        'ranges' => ['10.0.0.10-10.0.0.100'],
                    ],
                ],
            ], 200),
        ]);

        $count = $this->mikrotikService->syncIpPools($this->router->id);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('mikrotik_ip_pools', [
            'router_id' => $this->router->id,
            'name' => 'pool1',
        ]);
    }

    public function test_sync_secrets_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/secret/print' => Http::response([
                'secrets' => [
                    [
                        'name' => 'user1',
                        'password' => 'pass1',
                        'service' => 'pppoe',
                        'profile' => 'default',
                    ],
                ],
            ], 200),
        ]);

        $count = $this->mikrotikService->syncSecrets($this->router->id);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('mikrotik_pppoe_users', [
            'router_id' => $this->router->id,
            'username' => 'user1',
        ]);
    }

    public function test_configure_router_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/configure' => Http::response(['success' => true], 200),
        ]);

        $config = [
            'ppp' => true,
            'pools' => true,
            'firewall' => true,
        ];

        $result = $this->mikrotikService->configureRouter($this->router->id, $config);

        $this->assertTrue($result);
        $this->assertDatabaseHas('router_configurations', [
            'router_id' => $this->router->id,
            'config_type' => 'one-click',
            'status' => 'applied',
        ]);
    }

    public function test_create_vpn_account_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/secret/add' => Http::response(['success' => true], 200),
        ]);

        $vpnData = [
            'username' => 'vpnuser1',
            'password' => 'vpnpass123',
            'profile' => 'default',
            'enabled' => true,
        ];

        $result = $this->mikrotikService->createVpnAccount($this->router->id, $vpnData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('mikrotik_vpn_accounts', [
            'router_id' => $this->router->id,
            'username' => 'vpnuser1',
        ]);
    }

    public function test_get_vpn_status_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/interface/l2tp-server/print' => Http::response([
                'servers' => [
                    ['name' => 'l2tp-server1', 'enabled' => true],
                ],
            ], 200),
        ]);

        $status = $this->mikrotikService->getVpnStatus($this->router->id);

        $this->assertIsArray($status);
        $this->assertCount(1, $status);
    }

    public function test_create_queue_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/queue/simple/add' => Http::response(['success' => true], 200),
        ]);

        $queueData = [
            'name' => 'test-queue',
            'target' => '192.168.1.10/32',
            'max_limit' => '10M/10M',
            'burst_limit' => '15M/15M',
            'priority' => 5,
        ];

        $result = $this->mikrotikService->createQueue($this->router->id, $queueData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('mikrotik_queues', [
            'router_id' => $this->router->id,
            'name' => 'test-queue',
        ]);
    }

    public function test_get_queues_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/queue/simple/print' => Http::response([
                'queues' => [
                    ['name' => 'queue1', 'target' => '192.168.1.10'],
                ],
            ], 200),
        ]);

        $queues = $this->mikrotikService->getQueues($this->router->id);

        $this->assertIsArray($queues);
        $this->assertCount(1, $queues);
    }

    public function test_add_firewall_rule_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ip/firewall/filter/add' => Http::response(['success' => true], 200),
        ]);

        $ruleData = [
            'chain' => 'forward',
            'action' => 'accept',
            'protocol' => 'tcp',
            'dst-port' => '80',
        ];

        $result = $this->mikrotikService->addFirewallRule($this->router->id, $ruleData);

        $this->assertTrue($result);
    }

    public function test_get_firewall_rules_successfully(): void
    {
        Http::fake([
            'localhost:8728/api/ip/firewall/filter/print' => Http::response([
                'rules' => [
                    ['chain' => 'forward', 'action' => 'accept'],
                ],
            ], 200),
        ]);

        $rules = $this->mikrotikService->getFirewallRules($this->router->id);

        $this->assertIsArray($rules);
        $this->assertCount(1, $rules);
    }
}
