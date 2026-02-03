<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MikrotikRouter;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RouterRadiusFailoverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RouterFailoverControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private MikrotikRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20,
        ]);
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $this->admin->roles()->attach($adminRole);
        }

        // Create router
        $this->router = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test admin can configure failover.
     */
    public function test_admin_can_configure_failover(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('configureFailover')
            ->once()
            ->with(Mockery::on(fn ($r) => $r->id === $this->router->id))
            ->andReturn(true);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.configure', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Failover configured successfully.',
        ]);
    }

    /**
     * Test admin can switch to RADIUS mode.
     */
    public function test_admin_can_switch_to_radius_mode(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('switchToRadiusMode')
            ->once()
            ->with(Mockery::on(fn ($r) => $r->id === $this->router->id))
            ->andReturn(true);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.switch-radius', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Switched to RADIUS authentication mode.',
        ]);
    }

    /**
     * Test admin can switch to router mode.
     */
    public function test_admin_can_switch_to_router_mode(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('switchToRouterMode')
            ->once()
            ->with(Mockery::on(fn ($r) => $r->id === $this->router->id))
            ->andReturn(true);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.switch-router', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Switched to local authentication mode.',
        ]);
    }

    /**
     * Test failover status endpoint works.
     */
    public function test_failover_status_endpoint_works(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('getRadiusStatus')
            ->once()
            ->with(Mockery::on(fn ($r) => $r->id === $this->router->id))
            ->andReturn([
                'mode' => 'radius',
                'radius_configured' => true,
                'radius_reachable' => true,
                'local_accounts' => 5,
                'radius_accounts' => 20,
            ]);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->get(route('panel.isp.routers.failover.status', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'mode' => 'radius',
            'radius_configured' => true,
            'radius_reachable' => true,
        ]);
    }

    /**
     * Test failover respects tenant isolation.
     */
    public function test_failover_respects_tenant_isolation(): void
    {
        $this->actingAs($this->admin);

        // Create router for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        // Try to configure failover on router from another tenant
        $response = $this->post(route('panel.isp.routers.failover.configure', $otherRouter->id));
        $response->assertNotFound();

        // Try to switch to RADIUS mode on router from another tenant
        $response = $this->post(route('panel.isp.routers.failover.switch-radius', $otherRouter->id));
        $response->assertNotFound();

        // Try to switch to router mode on router from another tenant
        $response = $this->post(route('panel.isp.routers.failover.switch-router', $otherRouter->id));
        $response->assertNotFound();

        // Try to get status on router from another tenant
        $response = $this->get(route('panel.isp.routers.failover.status', $otherRouter->id));
        $response->assertNotFound();
    }

    /**
     * Test failover configuration handles failures gracefully.
     */
    public function test_failover_configuration_handles_failures_gracefully(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService to return failure
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('configureFailover')
            ->once()
            ->andReturn(false);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.configure', $this->router->id));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to configure failover.',
        ]);
    }

    /**
     * Test switch to RADIUS mode handles failures.
     */
    public function test_switch_to_radius_mode_handles_failures(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService to return failure
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('switchToRadiusMode')
            ->once()
            ->andReturn(false);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.switch-radius', $this->router->id));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to switch to RADIUS mode.',
        ]);
    }

    /**
     * Test switch to router mode handles failures.
     */
    public function test_switch_to_router_mode_handles_failures(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService to return failure
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('switchToRouterMode')
            ->once()
            ->andReturn(false);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.switch-router', $this->router->id));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to switch to local mode.',
        ]);
    }

    /**
     * Test connection test endpoint works.
     */
    public function test_connection_test_endpoint_works(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('testRadiusConnection')
            ->once()
            ->with(Mockery::on(fn ($r) => $r->id === $this->router->id))
            ->andReturn(true);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.test-connection', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'RADIUS server is reachable.',
        ]);
    }

    /**
     * Test connection test handles unreachable RADIUS server.
     */
    public function test_connection_test_handles_unreachable_radius_server(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterRadiusFailoverService
        $mockService = Mockery::mock(RouterRadiusFailoverService::class);
        $mockService->shouldReceive('testRadiusConnection')
            ->once()
            ->andReturn(false);

        $this->app->instance(RouterRadiusFailoverService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.failover.test-connection', $this->router->id));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'RADIUS server is unreachable.',
        ]);
    }

    /**
     * Test non-existent router returns 404.
     */
    public function test_non_existent_router_returns_404(): void
    {
        $this->actingAs($this->admin);

        $nonExistentRouterId = 99999;

        $response = $this->post(route('panel.isp.routers.failover.configure', $nonExistentRouterId));
        $response->assertNotFound();

        $response = $this->post(route('panel.isp.routers.failover.switch-radius', $nonExistentRouterId));
        $response->assertNotFound();

        $response = $this->post(route('panel.isp.routers.failover.switch-router', $nonExistentRouterId));
        $response->assertNotFound();

        $response = $this->get(route('panel.isp.routers.failover.status', $nonExistentRouterId));
        $response->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
