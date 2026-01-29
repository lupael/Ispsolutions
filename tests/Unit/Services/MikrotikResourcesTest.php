<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test to verify MikroTik getResources method works correctly
 * This addresses the bug where getResources was missing from MikrotikService
 */
class MikrotikResourcesTest extends TestCase
{
    use RefreshDatabase;

    private MikrotikService $mikrotikService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mikrotikService = app(MikrotikService::class);
    }

    public function test_get_resources_returns_data_successfully(): void
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
            'localhost:8728/api/system/resource' => Http::response([
                'cpu-load' => 15,
                'free-memory' => 536870912,
                'total-memory' => 1073741824,
                'uptime' => '1w2d3h4m5s',
            ], 200),
        ]);

        $result = $this->mikrotikService->getResources($router->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('cpu-load', $result);
        $this->assertArrayHasKey('free-memory', $result);
        $this->assertArrayHasKey('total-memory', $result);
        $this->assertArrayHasKey('uptime', $result);
    }

    public function test_get_resources_handles_connection_failure(): void
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
            'localhost:8728/api/system/resource' => Http::response([], 500),
        ]);

        $result = $this->mikrotikService->getResources($router->id);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_resources_updates_router_status_on_success(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
            'api_status' => 'unknown',
        ]);

        Http::fake([
            'localhost:8728/api/system/resource' => Http::response([
                'cpu-load' => 15,
                'free-memory' => 536870912,
                'total-memory' => 1073741824,
            ], 200),
        ]);

        $this->mikrotikService->getResources($router->id);

        $fresh = $router->fresh();
        $this->assertEquals('online', $fresh->api_status);
        $this->assertNotNull($fresh->last_checked_at);
    }

    public function test_get_resources_updates_router_status_on_failure(): void
    {
        $router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
            'api_status' => 'online',
        ]);

        Http::fake([
            'localhost:8728/api/system/resource' => Http::response([], 500),
        ]);

        $this->mikrotikService->getResources($router->id);

        $fresh = $router->fresh();
        $this->assertEquals('offline', $fresh->api_status);
        $this->assertNotNull($fresh->last_checked_at);
        $this->assertNotNull($fresh->last_error);
    }

    public function test_get_resources_with_nonexistent_router(): void
    {
        $result = $this->mikrotikService->getResources(99999);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
