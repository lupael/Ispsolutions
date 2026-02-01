<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SyncOnusJob;
use App\Models\Olt;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OltSyncOnusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Tenant $tenant;

    protected Olt $olt;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create a test user with tenant
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20, // Admin
        ]);

        // Attach admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $this->user->roles()->attach($adminRole);
        }

        // Create an OLT for testing
        $this->olt = Olt::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.1',
            'status' => 'active',
        ]);
    }

    public function test_sync_onus_endpoint_dispatches_job()
    {
        Queue::fake();

        $this->actingAs($this->user);

        $response = $this->postJson("/api/v1/olt/{$this->olt->id}/sync-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'ONU sync started in background. This may take several minutes for large OLTs.',
                'queued' => true,
            ]);

        Queue::assertPushed(SyncOnusJob::class, function ($job) {
            return $job->oltId === $this->olt->id;
        });
    }

    public function test_sync_onus_endpoint_prevents_duplicate_jobs()
    {
        Queue::fake();

        $this->actingAs($this->user);

        // Dispatch job twice
        $this->postJson("/api/v1/olt/{$this->olt->id}/sync-onus")->assertStatus(200);
        $this->postJson("/api/v1/olt/{$this->olt->id}/sync-onus")->assertStatus(200);

        // Should only queue once due to ShouldBeUnique
        Queue::assertPushed(SyncOnusJob::class, 2);
    }

    public function test_sync_onus_endpoint_returns_404_for_nonexistent_olt()
    {
        Queue::fake();

        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/olt/99999/sync-onus');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'OLT not found.',
                'queued' => false,
            ]);

        Queue::assertNothingPushed();
    }

    public function test_sync_onus_endpoint_prevents_cross_tenant_access()
    {
        Queue::fake();

        // Create another tenant and OLT
        $otherTenant = Tenant::factory()->create();
        $otherOlt = Olt::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant OLT',
            'ip_address' => '192.168.2.1',
            'status' => 'active',
        ]);

        // Try to sync OLT from different tenant
        $this->actingAs($this->user);

        $response = $this->postJson("/api/v1/olt/{$otherOlt->id}/sync-onus");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'OLT not found.',
                'queued' => false,
            ]);

        Queue::assertNothingPushed();
    }

    public function test_sync_onus_endpoint_requires_authentication()
    {
        Queue::fake();

        $response = $this->postJson("/api/v1/olt/{$this->olt->id}/sync-onus");

        $response->assertStatus(401);

        Queue::assertNothingPushed();
    }
}
