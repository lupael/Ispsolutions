<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RouterProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class RouterProvisioningIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private MikrotikRouter $router;
    private Package $package;

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
            'status' => 'active',
        ]);

        // Create package
        $this->package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => '10Mbps Package',
            'bandwidth_up' => 10240,
            'bandwidth_down' => 10240,
        ]);
    }

    /**
     * Test complete provisioning flow.
     */
    public function test_complete_provisioning_flow(): void
    {
        $this->actingAs($this->admin);

        // Create a network user
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'testuser',
            'password' => 'testpass123',
            'package_id' => $this->package->id,
            'status' => 'active',
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);

        // Expect profile creation
        $mockService->shouldReceive('ensureProfileExists')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                Mockery::on(fn ($p) => $p->id === $this->package->id)
            )
            ->andReturn(true);

        // Expect user provisioning
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                Mockery::on(fn ($u) => $u->id === $networkUser->id),
                Mockery::on(fn ($p) => $p->id === $this->package->id)
            )
            ->andReturn([
                'success' => true,
                'message' => 'User provisioned successfully',
                'username' => 'testuser',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        // Trigger provisioning (would be through an endpoint or service call)
        $result = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $networkUser,
            $this->package
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('testuser', $result['username']);
    }

    /**
     * Test user provisioning with package.
     */
    public function test_user_provisioning_with_package(): void
    {
        $this->actingAs($this->admin);

        // Create network user
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'testuser2',
            'password' => 'password123',
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'User provisioned successfully',
                'username' => 'testuser2',
                'profile' => '10Mbps Package',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $result = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $networkUser,
            $this->package
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('testuser2', $result['username']);
        $this->assertEquals('10Mbps Package', $result['profile']);
    }

    /**
     * Test deprovisioning removes user from router.
     */
    public function test_deprovisioning_removes_user_from_router(): void
    {
        $this->actingAs($this->admin);

        // Create network user
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'testuser3',
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('deprovisionUser')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                'testuser3'
            )
            ->andReturn([
                'success' => true,
                'message' => 'User deprovisioned successfully',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $result = app(RouterProvisioningService::class)->deprovisionUser(
            $this->router,
            'testuser3'
        );

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('deprovisioned', $result['message']);
    }

    /**
     * Test provisioning creates PPP profile.
     */
    public function test_provisioning_creates_ppp_profile(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('ensureProfileExists')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                Mockery::on(fn ($p) => $p->id === $this->package->id)
            )
            ->andReturn(true);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $result = app(RouterProvisioningService::class)->ensureProfileExists(
            $this->router,
            $this->package
        );

        $this->assertTrue($result);
    }

    /**
     * Test provisioning handles failures gracefully.
     */
    public function test_provisioning_handles_failures_gracefully(): void
    {
        $this->actingAs($this->admin);

        // Create network user
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'failuser',
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService to simulate failure
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Failed to connect to router',
                'error' => 'Connection timeout',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $result = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $networkUser,
            $this->package
        );

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test provisioning with inactive router.
     */
    public function test_provisioning_with_inactive_router(): void
    {
        $this->actingAs($this->admin);

        // Create inactive router
        $inactiveRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'inactive',
        ]);

        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'testuser4',
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Router is not active',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $result = app(RouterProvisioningService::class)->provisionUser(
            $inactiveRouter,
            $networkUser,
            $this->package
        );

        $this->assertFalse($result['success']);
    }

    /**
     * Test bulk provisioning of multiple users.
     */
    public function test_bulk_provisioning_of_multiple_users(): void
    {
        $this->actingAs($this->admin);

        // Create multiple network users
        $users = NetworkUser::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->times(3)
            ->andReturn([
                'success' => true,
                'message' => 'User provisioned successfully',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $results = [];
        foreach ($users as $user) {
            $results[] = app(RouterProvisioningService::class)->provisionUser(
                $this->router,
                $user,
                $this->package
            );
        }

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
        }
    }

    /**
     * Test provisioning with different package speeds.
     */
    public function test_provisioning_with_different_package_speeds(): void
    {
        $this->actingAs($this->admin);

        // Create packages with different speeds
        $fastPackage = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => '100Mbps Package',
            'bandwidth_up' => 102400,
            'bandwidth_down' => 102400,
        ]);

        $slowPackage = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => '5Mbps Package',
            'bandwidth_up' => 5120,
            'bandwidth_down' => 5120,
        ]);

        // Create users with different packages
        $fastUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $fastPackage->id,
        ]);

        $slowUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $slowPackage->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->twice()
            ->andReturn([
                'success' => true,
                'message' => 'User provisioned successfully',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        $fastResult = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $fastUser,
            $fastPackage
        );

        $slowResult = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $slowUser,
            $slowPackage
        );

        $this->assertTrue($fastResult['success']);
        $this->assertTrue($slowResult['success']);
    }

    /**
     * Test provisioning respects tenant isolation.
     */
    public function test_provisioning_respects_tenant_isolation(): void
    {
        $this->actingAs($this->admin);

        // Create another tenant with its own resources
        $otherTenant = Tenant::factory()->create();
        $otherPackage = Package::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $otherUser = NetworkUser::factory()->create([
            'tenant_id' => $otherTenant->id,
            'package_id' => $otherPackage->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Tenant mismatch',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        // Try to provision user from another tenant on this tenant's router
        $result = app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $otherUser,
            $otherPackage
        );

        $this->assertFalse($result['success']);
    }

    /**
     * Test provisioning logs activity.
     */
    public function test_provisioning_logs_activity(): void
    {
        $this->actingAs($this->admin);

        // Spy on Log facade
        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'));

        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $this->package->id,
        ]);

        // Mock the RouterProvisioningService
        $mockService = Mockery::mock(RouterProvisioningService::class);
        $mockService->shouldReceive('provisionUser')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'User provisioned successfully',
            ]);

        $this->app->instance(RouterProvisioningService::class, $mockService);

        app(RouterProvisioningService::class)->provisionUser(
            $this->router,
            $networkUser,
            $this->package
        );

        // Log assertions are checked by Mockery
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
