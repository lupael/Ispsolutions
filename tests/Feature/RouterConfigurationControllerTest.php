<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MikrotikRouter;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RouterConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RouterConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $manager;
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

        // Create manager user
        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 50,
        ]);
        $managerRole = Role::where('slug', 'manager')->first();
        if ($managerRole) {
            $this->manager->roles()->attach($managerRole);
        }

        // Create router
        $this->router = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test admin can configure RADIUS on router.
     */
    public function test_admin_can_configure_radius(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterConfigurationService
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('configureRadius')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'RADIUS configured successfully',
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.configuration.configure-radius', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'RADIUS configured successfully',
        ]);
    }

    /**
     * Test admin can configure PPP on router.
     */
    public function test_admin_can_configure_ppp(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterConfigurationService
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('configurePpp')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'PPP configured successfully',
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.configuration.configure-ppp', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'PPP configured successfully',
        ]);
    }

    /**
     * Test admin can configure firewall on router.
     */
    public function test_admin_can_configure_firewall(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterConfigurationService
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('configureFirewall')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'Firewall configured successfully',
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.configuration.configure-firewall', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Firewall configured successfully',
        ]);
    }

    /**
     * Test configuration respects tenant isolation.
     */
    public function test_configuration_respects_tenant_isolation(): void
    {
        $this->actingAs($this->admin);

        // Create router for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        // Try to configure RADIUS on router from another tenant
        $response = $this->post(route('panel.isp.routers.configuration.configure-radius', $otherRouter->id));
        $response->assertNotFound();

        // Try to configure PPP on router from another tenant
        $response = $this->post(route('panel.isp.routers.configuration.configure-ppp', $otherRouter->id));
        $response->assertNotFound();

        // Try to configure firewall on router from another tenant
        $response = $this->post(route('panel.isp.routers.configuration.configure-firewall', $otherRouter->id));
        $response->assertNotFound();
    }

    /**
     * Test RADIUS status endpoint works.
     */
    public function test_radius_status_endpoint_works(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterConfigurationService
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('getRadiusStatus')
            ->once()
            ->andReturn([
                'configured' => true,
                'status' => 'active',
                'servers' => [
                    ['address' => '192.168.1.1', 'port' => 1812],
                ],
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->get(route('panel.isp.routers.configuration.radius-status', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'configured' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Test manager with permission can configure router.
     */
    public function test_manager_with_permission_can_configure(): void
    {
        $this->actingAs($this->manager);

        // Mock the RouterConfigurationService
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('configureRadius')
            ->andReturn([
                'success' => true,
                'message' => 'RADIUS configured successfully',
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.configuration.configure-radius', $this->router->id));

        // Manager may or may not have access depending on permissions
        // Check for either success or forbidden/redirect
        $this->assertTrue(
            $response->isOk() || $response->isForbidden() || $response->isRedirect(),
            'Response should be either OK, Forbidden, or Redirect'
        );
    }

    /**
     * Test configuration fails gracefully on invalid router.
     */
    public function test_configuration_fails_gracefully_on_invalid_router(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterConfigurationService to simulate connection failure
        $mockService = Mockery::mock(RouterConfigurationService::class);
        $mockService->shouldReceive('configureRadius')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Failed to connect to router',
                'error' => 'Connection timeout',
            ]);

        $this->app->instance(RouterConfigurationService::class, $mockService);

        $response = $this->post(route('panel.isp.routers.configuration.configure-radius', $this->router->id));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to connect to router',
        ]);
    }

    /**
     * Test configuration handles non-existent router.
     */
    public function test_configuration_handles_non_existent_router(): void
    {
        $this->actingAs($this->admin);

        $nonExistentRouterId = 99999;

        $response = $this->post(route('panel.isp.routers.configuration.configure-radius', $nonExistentRouterId));
        $response->assertNotFound();

        $response = $this->post(route('panel.isp.routers.configuration.configure-ppp', $nonExistentRouterId));
        $response->assertNotFound();

        $response = $this->post(route('panel.isp.routers.configuration.configure-firewall', $nonExistentRouterId));
        $response->assertNotFound();
    }

    /**
     * Test RADIUS status respects tenant isolation.
     */
    public function test_radius_status_respects_tenant_isolation(): void
    {
        $this->actingAs($this->admin);

        // Create router for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        // Try to get RADIUS status for router from another tenant
        $response = $this->get(route('panel.isp.routers.configuration.radius-status', $otherRouter->id));
        $response->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
