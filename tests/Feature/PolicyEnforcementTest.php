<?php

namespace Tests\Feature;

use App\Models\IpPool;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Models\Package;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $admin;

    protected User $operator;

    protected User $staff;

    protected User $manager;

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
        $this->admin->roles()->attach($adminRole);

        // Create operator user
        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 30,
        ]);
        $operatorRole = Role::where('slug', 'operator')->first();
        $this->operator->roles()->attach($operatorRole);

        // Create staff user
        $this->staff = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 80,
        ]);
        $staffRole = Role::where('slug', 'staff')->first();
        $this->staff->roles()->attach($staffRole);

        // Create manager user
        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 50,
        ]);
        $managerRole = Role::where('slug', 'manager')->first();
        $this->manager->roles()->attach($managerRole);
    }

    /** @test */
    public function admin_can_view_packages()
    {
        $this->assertTrue($this->admin->can('viewAny', Package::class));
    }

    /** @test */
    public function admin_can_create_packages()
    {
        $this->assertTrue($this->admin->can('create', Package::class));
    }

    /** @test */
    public function operator_can_view_packages()
    {
        $this->assertTrue($this->operator->can('viewAny', Package::class));
    }

    /** @test */
    public function operator_cannot_create_packages()
    {
        $this->assertFalse($this->operator->can('create', Package::class));
    }

    /** @test */
    public function staff_without_permission_cannot_view_packages()
    {
        $this->assertFalse($this->staff->can('viewAny', Package::class));
    }

    /** @test */
    public function admin_can_view_network_devices()
    {
        $this->assertTrue($this->admin->can('viewAny', MikrotikRouter::class));
        $this->assertTrue($this->admin->can('viewAny', Olt::class));
        $this->assertTrue($this->admin->can('viewAny', IpPool::class));
    }

    /** @test */
    public function admin_can_create_network_devices()
    {
        $this->assertTrue($this->admin->can('create', MikrotikRouter::class));
        $this->assertTrue($this->admin->can('create', Olt::class));
        $this->assertTrue($this->admin->can('create', IpPool::class));
    }

    /** @test */
    public function operator_cannot_view_network_devices()
    {
        $this->assertFalse($this->operator->can('viewAny', MikrotikRouter::class));
        $this->assertFalse($this->operator->can('viewAny', Olt::class));
    }

    /** @test */
    public function operator_cannot_create_network_devices()
    {
        $this->assertFalse($this->operator->can('create', MikrotikRouter::class));
        $this->assertFalse($this->operator->can('create', Olt::class));
        $this->assertFalse($this->operator->can('create', IpPool::class));
    }

    /** @test */
    public function staff_without_permission_cannot_view_network_devices()
    {
        $this->assertFalse($this->staff->can('viewAny', MikrotikRouter::class));
        $this->assertFalse($this->staff->can('viewAny', Olt::class));
    }

    /** @test */
    public function manager_without_permission_cannot_create_network_devices()
    {
        $this->assertFalse($this->manager->can('create', MikrotikRouter::class));
        $this->assertFalse($this->manager->can('create', Olt::class));
    }

    /** @test */
    public function package_profile_mapping_index_requires_view_permission()
    {
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Admin can access
        $response = $this->actingAs($this->admin)
            ->get(route('panel.admin.packages.mappings.index', $package));
        $response->assertOk();

        // Operator can access (they can view packages)
        $response = $this->actingAs($this->operator)
            ->get(route('panel.admin.packages.mappings.index', $package));
        $response->assertOk();

        // Staff without permission cannot access
        $response = $this->actingAs($this->staff)
            ->get(route('panel.admin.packages.mappings.index', $package));
        $response->assertForbidden();
    }

    /** @test */
    public function package_profile_mapping_create_requires_create_permission()
    {
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Admin can access
        $response = $this->actingAs($this->admin)
            ->get(route('panel.admin.packages.mappings.create', $package));
        $response->assertOk();

        // Operator cannot access
        $response = $this->actingAs($this->operator)
            ->get(route('panel.admin.packages.mappings.create', $package));
        $response->assertForbidden();

        // Staff without permission cannot access
        $response = $this->actingAs($this->staff)
            ->get(route('panel.admin.packages.mappings.create', $package));
        $response->assertForbidden();
    }

    /** @test */
    public function package_profile_mapping_store_requires_create_permission()
    {
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $router = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $mappingData = [
            'router_id' => $router->id,
            'profile_name' => 'test-profile',
            'speed_control_method' => 'simple_queue',
        ];

        // Admin can create
        $response = $this->actingAs($this->admin)
            ->post(route('panel.admin.packages.mappings.store', $package), $mappingData);
        $response->assertRedirect();

        // Operator cannot create
        $response = $this->actingAs($this->operator)
            ->post(route('panel.admin.packages.mappings.store', $package), $mappingData);
        $response->assertForbidden();
    }

    /** @test */
    public function olt_api_index_requires_view_permission()
    {
        // Admin can access
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/olt');
        $response->assertOk();

        // Operator cannot access
        $response = $this->actingAs($this->operator)
            ->getJson('/api/v1/olt');
        $response->assertForbidden();

        // Staff without permission cannot access
        $response = $this->actingAs($this->staff)
            ->getJson('/api/v1/olt');
        $response->assertForbidden();
    }

    /** @test */
    public function mikrotik_create_profile_requires_create_permission()
    {
        $router = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $profileData = [
            'router_id' => $router->id,
            'name' => 'test-profile',
            'rate_limit' => '10M/10M',
        ];

        // Admin can create
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/mikrotik/profiles', $profileData);
        // May fail if API connection is not available, but should not be forbidden
        $this->assertNotEquals(403, $response->status());

        // Operator cannot create
        $response = $this->actingAs($this->operator)
            ->postJson('/api/v1/mikrotik/profiles', $profileData);
        $response->assertForbidden();
    }
}
