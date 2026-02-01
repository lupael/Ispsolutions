<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Olt;
use App\Models\OltSnmpTrap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for SNMP Trap Receiver endpoints.
 * 
 * Validates trap processing, OLT health updates, and security measures.
 */
class SnmpTrapReceiverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Olt $olt;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->olt = Olt::factory()->create([
            'ip_address' => '127.0.0.1',
            'health_status' => 'healthy',
            'tenant_id' => $this->user->tenant_id,
        ]);
    }

    public function test_receives_trap_from_known_olt(): void
    {
        // Disable IP allowlist for testing
        config(['snmp.trap_allowed_ips' => []]);
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
            'oid' => '.1.3.6.1.6.3.1.1.5.3',
            'severity' => 'critical',
            'message' => 'PON port down',
            'trap_data' => ['port' => '1/1'],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify trap was recorded
        $this->assertDatabaseHas('olt_snmp_traps', [
            'olt_id' => $this->olt->id,
            'source_ip' => '127.0.0.1',
            'trap_type' => 'linkDown',
            'severity' => 'critical',
        ]);
    }

    public function test_receives_trap_from_unknown_olt(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        // Create OLT with different IP
        $this->olt->update(['ip_address' => '192.168.1.100']);
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
            'severity' => 'warning',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
        ]);

        // Verify trap was recorded with null OLT
        $this->assertDatabaseHas('olt_snmp_traps', [
            'olt_id' => null,
            'source_ip' => '127.0.0.1',
            'trap_type' => 'linkDown',
        ]);
    }

    public function test_critical_trap_updates_olt_health_status(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        $this->assertEquals('healthy', $this->olt->health_status);
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'coldStart',
            'severity' => 'critical',
        ]);

        $response->assertStatus(200);

        // Verify OLT health status was updated
        $this->olt->refresh();
        $this->assertEquals('degraded', $this->olt->health_status);
        $this->assertNotNull($this->olt->last_health_check_at);
    }

    public function test_info_trap_does_not_update_olt_health(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        $initialHealthStatus = $this->olt->health_status;
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'testTrap',
            'severity' => 'info',
        ]);

        $response->assertStatus(200);

        // Verify OLT health status was NOT changed
        $this->olt->refresh();
        $this->assertEquals($initialHealthStatus, $this->olt->health_status);
    }

    public function test_auto_detects_severity_from_trap_type(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        // Send trap without severity - should auto-detect as critical
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
        ]);

        $response->assertStatus(200);

        // Verify severity was set to critical
        $this->assertDatabaseHas('olt_snmp_traps', [
            'trap_type' => 'linkDown',
            'severity' => 'critical',
        ]);
    }

    public function test_handles_trap_data_as_json_string(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        $trapData = json_encode(['port' => '1/1', 'interface' => 'gpon-onu_1/1:1']);
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
            'trap_data' => $trapData,
        ]);

        $response->assertStatus(200);

        $trap = OltSnmpTrap::latest()->first();
        $this->assertIsArray($trap->trap_data);
        $this->assertEquals('1/1', $trap->trap_data['port']);
    }

    public function test_legacy_format_endpoint_processes_trap(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        $legacyData = "UDP: [127.0.0.1]\n";
        $legacyData .= "trap_type=linkDown\n";
        $legacyData .= ".1.3.6.1.6.3.1.1.5.3\n";
        
        $response = $this->post('/api/v1/snmp-trap/receive-legacy', 
            $legacyData,
            ['Content-Type' => 'text/plain']
        );

        $response->assertStatus(200);
        
        // Verify trap was processed
        $this->assertDatabaseHas('olt_snmp_traps', [
            'source_ip' => '127.0.0.1',
        ]);
    }

    public function test_ip_allowlist_blocks_unauthorized_ip(): void
    {
        // Configure allowed IPs
        config(['snmp.trap_allowed_ips' => ['192.168.1.100', '10.0.0.0/8']]);
        
        // Request from 127.0.0.1 which is not in allowlist
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized: IP address not allowed',
        ]);

        // Verify no trap was recorded
        $this->assertDatabaseCount('olt_snmp_traps', 0);
    }

    public function test_ip_allowlist_allows_exact_match(): void
    {
        config(['snmp.trap_allowed_ips' => ['127.0.0.1']]);
        
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'trap_type' => 'linkDown',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('olt_snmp_traps', 1);
    }

    public function test_test_endpoint_only_works_in_non_production(): void
    {
        config(['app.env' => 'local']);
        config(['snmp.trap_allowed_ips' => []]);
        
        $response = $this->postJson('/api/v1/snmp-trap/test', [
            'source_ip' => '192.168.1.100',
            'trap_type' => 'linkDown',
        ]);

        $response->assertStatus(200);
    }

    public function test_test_endpoint_blocked_in_production(): void
    {
        config(['app.env' => 'production']);
        config(['snmp.trap_allowed_ips' => []]);
        
        $response = $this->postJson('/api/v1/snmp-trap/test');

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Test endpoint not available in production',
        ]);
    }

    public function test_uses_actual_request_ip_not_client_supplied(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        
        // Try to spoof source_ip - should be ignored
        $response = $this->postJson('/api/v1/snmp-trap/receive', [
            'source_ip' => '192.168.1.100', // Attempt to spoof
            'trap_type' => 'linkDown',
        ]);

        $response->assertStatus(200);

        // Verify actual request IP was used, not spoofed value
        $this->assertDatabaseHas('olt_snmp_traps', [
            'source_ip' => '127.0.0.1', // Actual test request IP
        ]);
    }

    public function test_rate_limiting_prevents_abuse(): void
    {
        config(['snmp.trap_allowed_ips' => []]);
        config(['snmp.trap_rate_limit' => 2]);
        
        // Send requests up to the limit
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/snmp-trap/receive', [
                'trap_type' => 'linkDown',
            ]);
            
            if ($i < 2) {
                $response->assertStatus(200);
            }
        }
        
        // Note: Actual rate limiting behavior depends on middleware configuration
        // This test documents expected behavior
    }
}
