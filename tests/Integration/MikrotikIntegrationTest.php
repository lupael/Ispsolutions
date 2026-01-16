<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MikrotikIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private MikrotikService $mikrotikService;
    private MikrotikRouter $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mikrotikService = app(MikrotikService::class);

        $this->router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);
    }

    public function test_full_pppoe_user_lifecycle(): void
    {
        Http::fake([
            'localhost:8728/*' => function ($request) {
                // Mock different endpoints
                if (str_contains($request->url(), '/health')) {
                    return Http::response(['status' => 'ok'], 200);
                }
                if (str_contains($request->url(), '/api/ppp/secret/add')) {
                    return Http::response(['success' => true, 'id' => 'testuser'], 201);
                }
                if (str_contains($request->url(), '/api/ppp/secret/set')) {
                    return Http::response(['success' => true], 200);
                }
                if (str_contains($request->url(), '/api/ppp/secret/remove')) {
                    return Http::response(['success' => true], 200);
                }
                return Http::response([], 404);
            },
        ]);

        // Create user
        $result = $this->mikrotikService->createPppoeUser([
            'router_id' => $this->router->id,
            'username' => 'testuser',
            'password' => 'testpass',
            'service' => 'pppoe',
        ]);
        $this->assertTrue($result);

        // Verify user in database
        $this->assertDatabaseHas('mikrotik_pppoe_users', [
            'username' => 'testuser',
            'status' => 'synced',
        ]);

        // Update user
        $result = $this->mikrotikService->updatePppoeUser('testuser', [
            'password' => 'newpass',
        ]);
        $this->assertTrue($result);

        // Delete user
        $result = $this->mikrotikService->deletePppoeUser('testuser');
        $this->assertTrue($result);

        // Verify user status
        $this->assertDatabaseHas('mikrotik_pppoe_users', [
            'username' => 'testuser',
            'status' => 'inactive',
        ]);
    }

    public function test_session_management(): void
    {
        Http::fake([
            'localhost:8728/health' => Http::response(['status' => 'ok'], 200),
            'localhost:8728/api/ppp/active/print' => Http::response([
                'success' => true,
                'sessions' => [
                    [
                        'id' => '*1',
                        'name' => 'user1',
                        'address' => '10.0.0.1',
                        'uptime' => '1h',
                    ],
                ],
            ], 200),
            'localhost:8728/api/ppp/active/remove' => Http::response([
                'success' => true,
            ], 200),
        ]);

        // Connect to router
        $result = $this->mikrotikService->connectRouter($this->router->id);
        $this->assertTrue($result);

        // Get active sessions
        $sessions = $this->mikrotikService->getActiveSessions($this->router->id);
        $this->assertCount(1, $sessions);

        // Disconnect session
        $result = $this->mikrotikService->disconnectSession('*1');
        $this->assertTrue($result);
    }

    public function test_multiple_users_on_same_router(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/secret/add' => Http::response(['success' => true], 201),
        ]);

        // Create multiple users
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->mikrotikService->createPppoeUser([
                'router_id' => $this->router->id,
                'username' => "user{$i}",
                'password' => "pass{$i}",
                'service' => 'pppoe',
            ]);
            $this->assertTrue($result);
        }

        // Verify all users in database
        $users = \App\Models\MikrotikPppoeUser::where('router_id', $this->router->id)
            ->where('status', 'synced')
            ->count();
        $this->assertEquals(3, $users);
    }

    public function test_error_handling_on_api_failure(): void
    {
        Http::fake([
            'localhost:8728/api/ppp/secret/add' => Http::response([
                'error' => 'User already exists',
            ], 409),
        ]);

        $result = $this->mikrotikService->createPppoeUser([
            'router_id' => $this->router->id,
            'username' => 'testuser',
            'password' => 'testpass',
            'service' => 'pppoe',
        ]);

        $this->assertFalse($result);

        // Verify user not in database
        $this->assertDatabaseMissing('mikrotik_pppoe_users', [
            'username' => 'testuser',
        ]);
    }

    public function test_connection_retry_logic(): void
    {
        Http::fake([
            'localhost:8728/health' => Http::sequence()
                ->push(['status' => 'error'], 500)
                ->push(['status' => 'ok'], 200),
        ]);

        // First attempt should fail
        $result = $this->mikrotikService->connectRouter($this->router->id);
        $this->assertFalse($result);

        // Second attempt should succeed
        $result = $this->mikrotikService->connectRouter($this->router->id);
        $this->assertTrue($result);
    }
}
