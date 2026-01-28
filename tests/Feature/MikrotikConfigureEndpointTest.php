<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MikrotikRouter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MikrotikConfigureEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MikrotikRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role and user
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator',
        ]);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole->id);

        // Create test router
        $this->router = MikrotikRouter::create([
            'tenant_id' => $this->admin->tenant_id,
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);
    }

    public function test_configure_endpoint_returns_success_with_valid_config(): void
    {
        Http::fake([
            'http://localhost:8728/api/configure' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'config_type' => 'pppoe',
                'settings' => [
                    'interface' => 'ether1',
                    'service_name' => 'pppoe-service',
                    'default_profile' => 'default',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'router' => $this->router->name,
                'config_type' => 'pppoe',
            ]);

        // Verify configuration was stored
        $this->assertDatabaseHas('router_configurations', [
            'router_id' => $this->router->id,
            'config_type' => 'one-click',
            'status' => 'applied',
        ]);
    }

    public function test_configure_endpoint_returns_400_when_router_connection_fails(): void
    {
        Http::fake([
            'http://localhost:8728/api/configure' => Http::response(['error' => 'Connection failed'], 500),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'config_type' => 'ippool',
                'settings' => [
                    'pool_name' => 'default-pool',
                    'ip_range' => '192.168.1.2-192.168.1.254',
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_configure_endpoint_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'settings' => [
                    'interface' => 'ether1',
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['config_type']);
    }

    public function test_configure_endpoint_validates_config_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'config_type' => 'invalid_type',
                'settings' => [
                    'interface' => 'ether1',
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['config_type']);
    }

    public function test_configure_endpoint_requires_authentication(): void
    {
        $response = $this->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
            'config_type' => 'pppoe',
            'settings' => [
                'interface' => 'ether1',
            ],
        ]);

        $response->assertStatus(302); // Redirect to login
    }

    public function test_configure_endpoint_requires_tenant_scoped_router(): void
    {
        // Create router for different tenant
        $otherRouter = MikrotikRouter::create([
            'tenant_id' => 999,
            'name' => 'Other Tenant Router',
            'ip_address' => '192.168.1.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $otherRouter->id), [
                'config_type' => 'pppoe',
                'settings' => [
                    'interface' => 'ether1',
                ],
            ]);

        $response->assertStatus(404);
    }

    public function test_configure_endpoint_handles_firewall_config(): void
    {
        Http::fake([
            'http://localhost:8728/api/configure' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'config_type' => 'firewall',
                'settings' => [
                    'chain' => 'forward',
                    'action' => 'accept',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'config_type' => 'firewall',
            ]);
    }

    public function test_configure_endpoint_handles_queue_config(): void
    {
        Http::fake([
            'http://localhost:8728/api/configure' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $this->router->id), [
                'config_type' => 'queue',
                'settings' => [
                    'queue_name' => 'default-queue',
                    'max_limit' => '10M/10M',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'config_type' => 'queue',
            ]);
    }

    public function test_configure_endpoint_blocks_ssrf_to_private_ips(): void
    {
        // Create router with private IP
        $privateRouter = MikrotikRouter::create([
            'tenant_id' => $this->admin->tenant_id,
            'name' => 'Private IP Router',
            'ip_address' => '192.168.1.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $privateRouter->id), [
                'config_type' => 'pppoe',
                'settings' => [
                    'interface' => 'ether1',
                ],
            ]);

        // Should fail with 400 due to SSRF protection
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_configure_endpoint_blocks_ssrf_to_metadata_service(): void
    {
        // Create router pointing to AWS metadata service
        $metadataRouter = MikrotikRouter::create([
            'tenant_id' => $this->admin->tenant_id,
            'name' => 'Metadata Router',
            'ip_address' => '169.254.169.254',
            'api_port' => 80,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('panel.admin.mikrotik.configure', $metadataRouter->id), [
                'config_type' => 'pppoe',
                'settings' => [
                    'interface' => 'ether1',
                ],
            ]);

        // Should fail with 400 due to SSRF protection
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
}
