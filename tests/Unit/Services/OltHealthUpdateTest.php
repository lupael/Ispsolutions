<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Olt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test to verify OLT health status can be updated
 * This addresses the bug where health_status, last_backup_at, and last_health_check_at
 * were not in the fillable array
 */
class OltHealthUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_olt_health_status_can_be_updated(): void
    {
        $olt = Olt::create([
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.1',
            'port' => 22,
            'management_protocol' => 'ssh',
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        // Update health status
        $olt->update([
            'health_status' => 'healthy',
            'last_health_check_at' => now(),
        ]);

        $this->assertEquals('healthy', $olt->fresh()->health_status);
        $this->assertNotNull($olt->fresh()->last_health_check_at);
    }

    public function test_olt_backup_timestamp_can_be_updated(): void
    {
        $olt = Olt::create([
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.1',
            'port' => 22,
            'management_protocol' => 'ssh',
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $backupTime = now();
        
        // Update backup timestamp
        $olt->update([
            'last_backup_at' => $backupTime,
        ]);

        $this->assertNotNull($olt->fresh()->last_backup_at);
        $this->assertEquals($backupTime->timestamp, $olt->fresh()->last_backup_at->timestamp);
    }

    public function test_olt_can_update_all_health_fields_at_once(): void
    {
        $olt = Olt::create([
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.1',
            'port' => 22,
            'management_protocol' => 'ssh',
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $backupTime = now();
        $healthCheckTime = now();

        // Update all health-related fields
        $olt->update([
            'health_status' => 'healthy',
            'last_backup_at' => $backupTime,
            'last_health_check_at' => $healthCheckTime,
        ]);

        $fresh = $olt->fresh();
        $this->assertEquals('healthy', $fresh->health_status);
        $this->assertNotNull($fresh->last_backup_at);
        $this->assertNotNull($fresh->last_health_check_at);
    }
}
