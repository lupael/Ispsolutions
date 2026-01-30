<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltMonitorOnusTest extends TestCase
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

    public function test_monitor_onus_endpoint_works_with_no_onus()
    {
        $this->actingAs($this->user);

        $response = $this->getJson("/api/v1/olt/{$this->olt->id}/monitor-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'olt' => [
                        'id' => $this->olt->id,
                        'name' => $this->olt->name,
                    ],
                    'summary' => [
                        'total' => 0,
                        'online' => 0,
                        'offline' => 0,
                        'average_signal_rx' => null, // Should be null when no ONUs
                    ],
                ],
            ]);
    }

    public function test_monitor_onus_endpoint_works_with_onus_with_null_signal()
    {
        $this->actingAs($this->user);

        // Create ONUs with null signal_rx
        Onu::factory()->count(3)->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => null,
            'status' => 'online',
        ]);

        $response = $this->getJson("/api/v1/olt/{$this->olt->id}/monitor-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total' => 3,
                        'online' => 3,
                        'offline' => 0,
                        'average_signal_rx' => null, // Should be null when all signals are null
                    ],
                ],
            ]);
    }

    public function test_monitor_onus_endpoint_calculates_average_signal_correctly()
    {
        $this->actingAs($this->user);

        // Create ONUs with signal_rx values
        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => -24.5,
            'status' => 'online',
        ]);

        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => -25.5,
            'status' => 'online',
        ]);

        // Create one with null signal (should be excluded from average)
        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => null,
            'status' => 'offline',
        ]);

        $response = $this->getJson("/api/v1/olt/{$this->olt->id}/monitor-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total' => 3,
                        'online' => 2,
                        'offline' => 1,
                        'average_signal_rx' => -25.0, // Average of -24.5 and -25.5
                    ],
                ],
            ]);
    }

    public function test_monitor_onus_endpoint_handles_mixed_status()
    {
        $this->actingAs($this->user);

        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => -23.0,
            'status' => 'online',
        ]);

        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => -27.0,
            'status' => 'offline',
        ]);

        $response = $this->getJson("/api/v1/olt/{$this->olt->id}/monitor-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total' => 2,
                        'online' => 1,
                        'offline' => 1,
                        'average_signal_rx' => -25.0,
                    ],
                ],
            ]);
    }

    public function test_monitor_onus_endpoint_handles_zero_average()
    {
        $this->actingAs($this->user);

        // Create ONUs with signal values that average to zero
        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => -10.0,
            'status' => 'online',
        ]);

        Onu::factory()->create([
            'olt_id' => $this->olt->id,
            'tenant_id' => $this->tenant->id,
            'signal_rx' => 10.0,
            'status' => 'online',
        ]);

        $response = $this->getJson("/api/v1/olt/{$this->olt->id}/monitor-onus");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total' => 2,
                        'online' => 2,
                        'offline' => 0,
                        'average_signal_rx' => 0.0, // Should be 0.0, not null
                    ],
                ],
            ]);
    }
}
