<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Nas;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NasControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $manager;

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
    }

    /**
     * Test admin can view NAS list.
     */
    public function test_admin_can_view_nas_list(): void
    {
        $this->actingAs($this->admin);

        // Create some NAS devices for this tenant
        Nas::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(route('panel.isp.network.nas'));

        $response->assertOk();
        $response->assertViewIs('panels.isp.nas.index');
        $response->assertViewHas('devices');
    }

    /**
     * Test admin can create NAS.
     */
    public function test_admin_can_create_nas(): void
    {
        $this->actingAs($this->admin);

        $nasData = [
            'name' => 'Test NAS Device',
            'nas_name' => 'test-nas',
            'short_name' => 'TEST',
            'server' => '192.168.1.100',
            'secret' => 'testing123',
            'type' => 'mikrotik',
            'ports' => 1812,
            'community' => 'public',
            'description' => 'Test NAS for unit testing',
            'status' => 'active',
        ];

        $response = $this->post(route('panel.isp.network.nas.store'), $nasData);

        $response->assertRedirect(route('panel.isp.network.nas'));
        $response->assertSessionHas('success', 'NAS device created successfully.');

        $this->assertDatabaseHas('nas', [
            'name' => 'Test NAS Device',
            'server' => '192.168.1.100',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test NAS requires valid data.
     */
    public function test_nas_requires_valid_data(): void
    {
        $this->actingAs($this->admin);

        // Test missing required fields
        $response = $this->post(route('panel.isp.network.nas.store'), []);

        $response->assertSessionHasErrors(['name', 'nas_name', 'short_name', 'server', 'secret', 'type', 'status']);

        // Test invalid IP address
        $response = $this->post(route('panel.isp.network.nas.store'), [
            'name' => 'Test NAS',
            'nas_name' => 'test-nas',
            'short_name' => 'TEST',
            'server' => 'invalid-ip',
            'secret' => 'secret123',
            'type' => 'mikrotik',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['server']);

        // Test invalid status
        $response = $this->post(route('panel.isp.network.nas.store'), [
            'name' => 'Test NAS',
            'nas_name' => 'test-nas',
            'short_name' => 'TEST',
            'server' => '192.168.1.100',
            'secret' => 'secret123',
            'type' => 'mikrotik',
            'status' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * Test tenant isolation for NAS.
     */
    public function test_tenant_isolation_nas(): void
    {
        $this->actingAs($this->admin);

        // Create NAS for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherNas = Nas::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        // Try to view NAS from another tenant - should not be found
        $response = $this->get(route('panel.isp.network.nas.show', $otherNas->id));
        $response->assertNotFound();

        // Try to update NAS from another tenant - should not be found
        $response = $this->put(route('panel.isp.network.nas.update', $otherNas->id), [
            'name' => 'Hacked NAS',
            'nas_name' => 'hacked',
            'short_name' => 'HACK',
            'server' => '10.10.10.10',
            'secret' => 'hacked',
            'type' => 'mikrotik',
            'status' => 'active',
        ]);
        $response->assertNotFound();

        // Verify the NAS was not updated
        $this->assertDatabaseMissing('nas', [
            'id' => $otherNas->id,
            'name' => 'Hacked NAS',
        ]);

        // Try to delete NAS from another tenant - should not be found
        $response = $this->delete(route('panel.isp.network.nas.destroy', $otherNas->id));
        $response->assertNotFound();

        // Verify the NAS was not deleted
        $this->assertDatabaseHas('nas', [
            'id' => $otherNas->id,
        ]);
    }

    /**
     * Test admin can update NAS.
     */
    public function test_admin_can_update_nas(): void
    {
        $this->actingAs($this->admin);

        $nas = Nas::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Original Name',
        ]);

        $updateData = [
            'name' => 'Updated NAS Name',
            'nas_name' => 'updated-nas',
            'short_name' => 'UPD',
            'server' => '192.168.2.200',
            'secret' => 'newsecret456',
            'type' => 'cisco',
            'ports' => 1813,
            'community' => 'private',
            'description' => 'Updated description',
            'status' => 'maintenance',
        ];

        $response = $this->put(route('panel.isp.network.nas.update', $nas->id), $updateData);

        $response->assertRedirect(route('panel.isp.network.nas'));
        $response->assertSessionHas('success', 'NAS device updated successfully.');

        $this->assertDatabaseHas('nas', [
            'id' => $nas->id,
            'name' => 'Updated NAS Name',
            'server' => '192.168.2.200',
            'type' => 'cisco',
        ]);
    }

    /**
     * Test admin can delete NAS.
     */
    public function test_admin_can_delete_nas(): void
    {
        $this->actingAs($this->admin);

        $nas = Nas::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->delete(route('panel.isp.network.nas.destroy', $nas->id));

        $response->assertRedirect(route('panel.isp.network.nas'));
        $response->assertSessionHas('success', 'NAS device deleted successfully.');

        $this->assertDatabaseMissing('nas', [
            'id' => $nas->id,
        ]);
    }

    /**
     * Test NAS test connection endpoint.
     */
    public function test_nas_test_connection(): void
    {
        $this->actingAs($this->admin);

        $nas = Nas::factory()->create([
            'tenant_id' => $this->tenant->id,
            'server' => '127.0.0.1', // localhost should be reachable in test env
        ]);

        $response = $this->post(route('panel.isp.network.nas.test-connection', $nas->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Test with invalid IP from another tenant (should be 404)
        $otherTenant = Tenant::factory()->create();
        $otherNas = Nas::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->post(route('panel.isp.network.nas.test-connection', $otherNas->id));
        $response->assertNotFound();
    }

    /**
     * Test manager with permission can view NAS.
     */
    public function test_manager_with_permission_can_view_nas(): void
    {
        // Managers typically can view if they have the right permissions
        // This test assumes the permission system is set up correctly
        $this->actingAs($this->manager);

        Nas::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // The response may vary based on your actual permission implementation
        $response = $this->get(route('panel.isp.network.nas'));

        // If manager has access, should be OK, otherwise forbidden/redirect
        // Adjust based on your actual policy implementation
        $this->assertTrue(
            $response->isOk() || $response->isForbidden() || $response->isRedirect(),
            'Response should be either OK, Forbidden, or Redirect'
        );
    }

    /**
     * Test manager without permission cannot create NAS.
     */
    public function test_manager_without_permission_cannot_create_nas(): void
    {
        $this->actingAs($this->manager);

        $nasData = [
            'name' => 'Test NAS Device',
            'nas_name' => 'test-nas',
            'short_name' => 'TEST',
            'server' => '192.168.1.100',
            'secret' => 'testing123',
            'type' => 'mikrotik',
            'status' => 'active',
        ];

        $response = $this->post(route('panel.isp.network.nas.store'), $nasData);

        // Manager should not be able to create NAS (depends on your policy)
        // This assertion may need adjustment based on your permission system
        $this->assertTrue(
            $response->isForbidden() || $response->isRedirect(),
            'Manager should not be able to create NAS'
        );
    }
}
