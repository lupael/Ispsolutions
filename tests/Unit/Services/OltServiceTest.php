<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\OltServiceInterface;
use App\Models\Olt;
use App\Models\OltBackup;
use App\Models\Onu;
use App\Services\OltService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OltServiceTest extends TestCase
{
    use RefreshDatabase;

    private OltServiceInterface $oltService;

    private Olt $olt;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->oltService = new OltService;

        // Create a test OLT
        $this->olt = Olt::create([
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.1',
            'port' => 22,
            'management_protocol' => 'ssh',
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
            'health_status' => 'healthy',
        ]);
    }

    public function test_test_connection_with_invalid_olt(): void
    {
        $olt = Olt::create([
            'name' => 'Invalid OLT',
            'ip_address' => 'invalid-ip',
            'port' => 22,
            'management_protocol' => 'ssh',
            'username' => '',
            'password' => '',
            'status' => 'active',
            'health_status' => 'healthy',
        ]);

        $result = $this->oltService->testConnection($olt->id);

        $this->assertFalse($result['success']);
        $this->assertIsString($result['message']);
        $this->assertIsInt($result['latency']);
    }

    public function test_discover_onus_returns_array(): void
    {
        // This will fail to connect but should return empty array
        $result = $this->oltService->discoverOnus($this->olt->id);

        $this->assertIsArray($result);
    }

    public function test_sync_onus_returns_count(): void
    {
        $result = $this->oltService->syncOnus($this->olt->id);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function test_get_onu_status_returns_array(): void
    {
        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'online',
        ]);

        $result = $this->oltService->getOnuStatus($onu->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('signal_rx', $result);
        $this->assertArrayHasKey('signal_tx', $result);
        $this->assertArrayHasKey('distance', $result);
        $this->assertArrayHasKey('uptime', $result);
        $this->assertArrayHasKey('last_update', $result);
    }

    public function test_refresh_onu_status(): void
    {
        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'online',
        ]);

        $result = $this->oltService->refreshOnuStatus($onu->id);

        $this->assertIsBool($result);
    }

    public function test_authorize_onu(): void
    {
        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'offline',
        ]);

        $result = $this->oltService->authorizeOnu($onu->id);

        $this->assertIsBool($result);
    }

    public function test_unauthorize_onu(): void
    {
        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'online',
        ]);

        $result = $this->oltService->unauthorizeOnu($onu->id);

        $this->assertIsBool($result);
    }

    public function test_reboot_onu(): void
    {
        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'online',
        ]);

        $result = $this->oltService->rebootOnu($onu->id);

        $this->assertIsBool($result);
    }

    public function test_create_backup(): void
    {
        $result = $this->oltService->createBackup($this->olt->id);

        $this->assertIsBool($result);
    }

    public function test_get_backup_list_returns_array(): void
    {
        $result = $this->oltService->getBackupList($this->olt->id);

        $this->assertIsArray($result);
    }

    public function test_get_backup_list_with_existing_backup(): void
    {
        $backup = OltBackup::create([
            'olt_id' => $this->olt->id,
            'file_path' => 'backups/olts/1/test_backup.cfg',
            'file_size' => 1024,
            'backup_type' => 'manual',
        ]);

        $result = $this->oltService->getBackupList($this->olt->id);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($backup->id, $result[0]['id']);
    }

    public function test_export_backup_with_nonexistent_backup(): void
    {
        $result = $this->oltService->exportBackup($this->olt->id, '999');

        $this->assertNull($result);
    }

    public function test_apply_configuration(): void
    {
        $config = [
            'interface gpon 0/1',
            'description Test Configuration',
        ];

        $result = $this->oltService->applyConfiguration($this->olt->id, $config);

        $this->assertIsBool($result);
    }

    public function test_get_olt_statistics_returns_array(): void
    {
        // Create some ONUs
        Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/1',
            'onu_id' => 1,
            'serial_number' => 'TEST12345678',
            'status' => 'online',
        ]);

        Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port' => '1/1/2',
            'onu_id' => 2,
            'serial_number' => 'TEST87654321',
            'status' => 'offline',
        ]);

        $result = $this->oltService->getOltStatistics($this->olt->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('uptime', $result);
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('cpu_usage', $result);
        $this->assertArrayHasKey('memory_usage', $result);
        $this->assertArrayHasKey('total_onus', $result);
        $this->assertArrayHasKey('online_onus', $result);
        $this->assertArrayHasKey('offline_onus', $result);
    }

    public function test_get_port_utilization_returns_array(): void
    {
        $result = $this->oltService->getPortUtilization($this->olt->id);

        $this->assertIsArray($result);
    }

    public function test_get_bandwidth_usage_returns_array(): void
    {
        $result = $this->oltService->getBandwidthUsage($this->olt->id, 'hourly');

        $this->assertIsArray($result);
    }

    public function test_get_bandwidth_usage_with_different_periods(): void
    {
        $periods = ['hourly', 'daily', 'weekly', 'monthly'];

        foreach ($periods as $period) {
            $result = $this->oltService->getBandwidthUsage($this->olt->id, $period);

            $this->assertIsArray($result);
        }
    }

    public function test_connect_returns_boolean(): void
    {
        $result = $this->oltService->connect($this->olt->id);

        $this->assertIsBool($result);
    }

    public function test_disconnect_returns_boolean(): void
    {
        $result = $this->oltService->disconnect($this->olt->id);

        $this->assertIsBool($result);
    }

    public function test_olt_backup_model_get_size(): void
    {
        $backup = OltBackup::create([
            'olt_id' => $this->olt->id,
            'file_path' => 'backups/olts/1/test_backup.cfg',
            'file_size' => 1024,
            'backup_type' => 'manual',
        ]);

        $size = $backup->getSize();

        $this->assertIsString($size);
        $this->assertStringContainsString('KB', $size);
    }

    public function test_olt_backup_model_exists(): void
    {
        $backup = OltBackup::create([
            'olt_id' => $this->olt->id,
            'file_path' => 'backups/olts/1/test_backup.cfg',
            'file_size' => 1024,
            'backup_type' => 'manual',
        ]);

        $exists = $backup->exists();

        $this->assertIsBool($exists);
    }
}
