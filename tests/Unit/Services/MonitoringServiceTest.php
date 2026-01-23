<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\MikrotikServiceInterface;
use App\Contracts\OltServiceInterface;
use App\Models\BandwidthUsage;
use App\Models\DeviceMonitor;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private MonitoringService $service;

    private MikrotikServiceInterface $mikrotikService;

    private OltServiceInterface $oltService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mikrotikService = $this->createMock(MikrotikServiceInterface::class);
        $this->oltService = $this->createMock(OltServiceInterface::class);
        $this->service = new MonitoringService($this->mikrotikService, $this->oltService);
    }

    public function test_monitor_router_creates_device_monitor_record(): void
    {
        $router = MikrotikRouter::factory()->create([
            'status' => 'active',
        ]);

        $this->mikrotikService->expects($this->once())
            ->method('getResources')
            ->willReturn([
                'cpu-load' => 25.5,
                'free-memory' => 512000000,
                'total-memory' => 1024000000,
                'uptime' => '1w2d3h',
            ]);

        $metrics = $this->service->monitorDevice('router', $router->id);

        $this->assertEquals('online', $metrics['status']);
        $this->assertEquals(25.5, $metrics['cpu_usage']);
        $this->assertNotNull($metrics['memory_usage']);
        $this->assertDatabaseHas('device_monitors', [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'status' => 'online',
        ]);
    }

    public function test_monitor_device_handles_offline_router(): void
    {
        $router = MikrotikRouter::factory()->create([
            'status' => 'active',
        ]);

        $this->mikrotikService->expects($this->once())
            ->method('getResources')
            ->willThrowException(new \Exception('Connection failed'));

        $metrics = $this->service->monitorDevice('router', $router->id);

        $this->assertEquals('offline', $metrics['status']);
        $this->assertDatabaseHas('device_monitors', [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'status' => 'offline',
        ]);
    }

    public function test_get_device_status_returns_monitoring_data(): void
    {
        $router = MikrotikRouter::factory()->create();

        DeviceMonitor::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'status' => 'online',
            'cpu_usage' => 30.5,
            'memory_usage' => 45.2,
            'uptime' => 86400,
            'last_check_at' => now(),
        ]);

        $status = $this->service->getDeviceStatus('router', $router->id);

        $this->assertEquals('online', $status['status']);
        $this->assertEquals(30.5, $status['cpu_usage']);
        $this->assertEquals(45.2, $status['memory_usage']);
        $this->assertEquals(86400, $status['uptime']);
    }

    public function test_record_bandwidth_usage_creates_record(): void
    {
        $router = MikrotikRouter::factory()->create();

        $result = $this->service->recordBandwidthUsage(
            'router',
            $router->id,
            1024000,
            2048000
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('bandwidth_usages', [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'upload_bytes' => 1024000,
            'download_bytes' => 2048000,
            'total_bytes' => 3072000,
            'period_type' => 'raw',
        ]);
    }

    public function test_get_bandwidth_usage_returns_usage_data(): void
    {
        $router = MikrotikRouter::factory()->create();

        BandwidthUsage::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'timestamp' => Carbon::now()->subHours(1),
            'upload_bytes' => 1024000,
            'download_bytes' => 2048000,
            'total_bytes' => 3072000,
            'period_type' => 'hourly',
        ]);

        BandwidthUsage::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'timestamp' => Carbon::now(),
            'upload_bytes' => 2048000,
            'download_bytes' => 4096000,
            'total_bytes' => 6144000,
            'period_type' => 'hourly',
        ]);

        $usage = $this->service->getBandwidthUsage(
            'router',
            $router->id,
            'hourly',
            Carbon::now()->subDay(),
            Carbon::now()
        );

        $this->assertEquals('router', $usage['device_type']);
        $this->assertEquals($router->id, $usage['device_id']);
        $this->assertCount(2, $usage['data']);
        $this->assertEquals(3072000, $usage['summary']['total_upload']);
        $this->assertEquals(6144000, $usage['summary']['total_download']);
    }

    public function test_get_bandwidth_graph_returns_chart_data(): void
    {
        $router = MikrotikRouter::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            BandwidthUsage::create([
                'monitorable_type' => 'App\\Models\\MikrotikRouter',
                'monitorable_id' => $router->id,
                'timestamp' => Carbon::now()->subHours($i),
                'upload_bytes' => 1048576 * ($i + 1),
                'download_bytes' => 2097152 * ($i + 1),
                'total_bytes' => 3145728 * ($i + 1),
                'period_type' => 'hourly',
            ]);
        }

        $graph = $this->service->getBandwidthGraph('router', $router->id, 'hourly');

        $this->assertArrayHasKey('labels', $graph);
        $this->assertArrayHasKey('datasets', $graph);
        $this->assertCount(3, $graph['datasets']); // Upload, Download, Total
        $this->assertCount(5, $graph['labels']);
    }

    public function test_aggregate_hourly_data_groups_raw_data(): void
    {
        $router = MikrotikRouter::factory()->create();
        $baseTime = Carbon::now()->subHours(3);

        // Create raw data points
        for ($i = 0; $i < 10; $i++) {
            BandwidthUsage::create([
                'monitorable_type' => 'App\\Models\\MikrotikRouter',
                'monitorable_id' => $router->id,
                'timestamp' => $baseTime->copy()->addMinutes($i * 5),
                'upload_bytes' => 1024000,
                'download_bytes' => 2048000,
                'total_bytes' => 3072000,
                'period_type' => 'raw',
            ]);
        }

        $processed = $this->service->aggregateHourlyData();

        $this->assertGreaterThan(0, $processed);
        $this->assertDatabaseHas('bandwidth_usages', [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'period_type' => 'hourly',
        ]);
    }

    public function test_aggregate_daily_data_groups_hourly_data(): void
    {
        $router = MikrotikRouter::factory()->create();
        $baseTime = Carbon::now()->subDays(3)->startOfDay();

        // Create hourly data points
        for ($i = 0; $i < 24; $i++) {
            BandwidthUsage::create([
                'monitorable_type' => 'App\\Models\\MikrotikRouter',
                'monitorable_id' => $router->id,
                'timestamp' => $baseTime->copy()->addHours($i),
                'upload_bytes' => 1024000,
                'download_bytes' => 2048000,
                'total_bytes' => 3072000,
                'period_type' => 'hourly',
            ]);
        }

        $processed = $this->service->aggregateDailyData();

        $this->assertGreaterThan(0, $processed);
        $this->assertDatabaseHas('bandwidth_usages', [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'period_type' => 'daily',
        ]);
    }

    public function test_get_all_device_statuses_returns_summary(): void
    {
        $router1 = MikrotikRouter::factory()->create();
        $router2 = MikrotikRouter::factory()->create();
        $olt = Olt::factory()->create();

        DeviceMonitor::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router1->id,
            'status' => 'online',
            'last_check_at' => now(),
        ]);

        DeviceMonitor::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router2->id,
            'status' => 'offline',
            'last_check_at' => now(),
        ]);

        DeviceMonitor::create([
            'monitorable_type' => 'App\\Models\\Olt',
            'monitorable_id' => $olt->id,
            'status' => 'online',
            'last_check_at' => now(),
        ]);

        $statuses = $this->service->getAllDeviceStatuses();

        $this->assertArrayHasKey('routers', $statuses);
        $this->assertArrayHasKey('olts', $statuses);
        $this->assertArrayHasKey('summary', $statuses);
        $this->assertEquals(3, $statuses['summary']['total']);
        $this->assertEquals(2, $statuses['summary']['online']);
        $this->assertEquals(1, $statuses['summary']['offline']);
    }
}
