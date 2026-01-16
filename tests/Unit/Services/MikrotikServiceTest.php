<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\MikrotikPppoeUser;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MikrotikServiceTest extends TestCase
{
    use RefreshDatabase;

    private MikrotikService $mikrotikService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mikrotikService = new MikrotikService;
    }

    public function test_connect_router_successfully(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        Http::fake([
            'localhost:8728/health' => Http::response(['status' => 'ok'], 200),
        ]);

        $result = $this->mikrotikService->connectRouter($router->id);

        $this->assertTrue($result);
    }

    public function test_connect_nonexistent_router_fails(): void
    {
        $result = $this->mikrotikService->connectRouter(99999);

        $this->assertFalse($result);
    }

    public function test_connect_router_with_connection_error(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        Http::fake([
            'localhost:8728/health' => Http::response([], 500),
        ]);

        $result = $this->mikrotikService->connectRouter($router->id);

        $this->assertFalse($result);
    }

    public function test_create_pppoe_user_successfully(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        Http::fake([
            'localhost:8728/api/ppp/secret/add' => Http::response([
                'success' => true,
                'id' => 'testuser',
            ], 201),
        ]);

        $userData = [
            'router_id' => $router->id,
            'username' => 'testuser',
            'password' => 'testpass',
            'service' => 'pppoe',
            'profile' => 'default',
        ];

        $result = $this->mikrotikService->createPppoeUser($userData);

        $this->assertTrue($result);

        // Verify user stored in local database
        $this->assertDatabaseHas('mikrotik_pppoe_users', [
            'router_id' => $router->id,
            'username' => 'testuser',
            'status' => 'synced',
        ]);
    }

    public function test_update_pppoe_user_successfully(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $pppoeUser = MikrotikPppoeUser::create([
            'router_id' => $router->id,
            'username' => 'testuser',
            'password' => 'oldpass',
            'service' => 'pppoe',
            'status' => 'synced',
        ]);

        Http::fake([
            'localhost:8728/api/ppp/secret/set' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $result = $this->mikrotikService->updatePppoeUser('testuser', [
            'password' => 'newpass',
        ]);

        $this->assertTrue($result);

        // Verify user updated in local database
        $pppoeUser->refresh();
        $this->assertEquals('newpass', $pppoeUser->password);
    }

    public function test_delete_pppoe_user_successfully(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $pppoeUser = MikrotikPppoeUser::create([
            'router_id' => $router->id,
            'username' => 'testuser',
            'password' => 'testpass',
            'service' => 'pppoe',
            'status' => 'synced',
        ]);

        Http::fake([
            'localhost:8728/api/ppp/secret/remove' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $result = $this->mikrotikService->deletePppoeUser('testuser');

        $this->assertTrue($result);

        // Verify user status updated
        $pppoeUser->refresh();
        $this->assertEquals('inactive', $pppoeUser->status);
    }

    public function test_get_active_sessions(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        Http::fake([
            'localhost:8728/api/ppp/active/print' => Http::response([
                'success' => true,
                'sessions' => [
                    [
                        'id' => '*1',
                        'name' => 'user1',
                        'address' => '10.0.0.1',
                        'uptime' => '1h',
                    ],
                    [
                        'id' => '*2',
                        'name' => 'user2',
                        'address' => '10.0.0.2',
                        'uptime' => '30m',
                    ],
                ],
            ], 200),
        ]);

        $sessions = $this->mikrotikService->getActiveSessions($router->id);

        $this->assertCount(2, $sessions);
        $this->assertEquals('user1', $sessions[0]['name']);
        $this->assertEquals('user2', $sessions[1]['name']);
    }

    public function test_disconnect_session(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        Http::fake([
            'localhost:8728/health' => Http::response(['status' => 'ok'], 200),
            'localhost:8728/api/ppp/active/remove' => Http::response([
                'success' => true,
            ], 200),
        ]);

        // Connect first
        $this->mikrotikService->connectRouter($router->id);

        // Disconnect session
        $result = $this->mikrotikService->disconnectSession('*1');

        $this->assertTrue($result);
    }
}
