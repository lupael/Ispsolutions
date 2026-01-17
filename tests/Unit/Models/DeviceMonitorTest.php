<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\DeviceMonitor;
use App\Models\MikrotikRouter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_monitor_belongs_to_monitorable(): void
    {
        $router = MikrotikRouter::factory()->create();
        $monitor = DeviceMonitor::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'status' => 'online',
            'last_check_at' => now(),
        ]);

        $this->assertInstanceOf(MikrotikRouter::class, $monitor->monitorable);
        $this->assertEquals($router->id, $monitor->monitorable->id);
    }

    public function test_scope_online_filters_online_devices(): void
    {
        DeviceMonitor::factory()->create(['status' => 'online']);
        DeviceMonitor::factory()->create(['status' => 'offline']);
        DeviceMonitor::factory()->create(['status' => 'online']);

        $online = DeviceMonitor::online()->get();

        $this->assertCount(2, $online);
        $this->assertTrue($online->every(fn($m) => $m->status === 'online'));
    }

    public function test_scope_offline_filters_offline_devices(): void
    {
        DeviceMonitor::factory()->create(['status' => 'online']);
        DeviceMonitor::factory()->create(['status' => 'offline']);
        DeviceMonitor::factory()->create(['status' => 'offline']);

        $offline = DeviceMonitor::offline()->get();

        $this->assertCount(2, $offline);
        $this->assertTrue($offline->every(fn($m) => $m->status === 'offline'));
    }

    public function test_is_online_returns_correct_status(): void
    {
        $onlineMonitor = DeviceMonitor::factory()->create(['status' => 'online']);
        $offlineMonitor = DeviceMonitor::factory()->create(['status' => 'offline']);

        $this->assertTrue($onlineMonitor->isOnline());
        $this->assertFalse($offlineMonitor->isOnline());
    }

    public function test_is_offline_returns_correct_status(): void
    {
        $onlineMonitor = DeviceMonitor::factory()->create(['status' => 'online']);
        $offlineMonitor = DeviceMonitor::factory()->create(['status' => 'offline']);

        $this->assertFalse($onlineMonitor->isOffline());
        $this->assertTrue($offlineMonitor->isOffline());
    }

    public function test_get_uptime_human_formats_correctly(): void
    {
        $monitor = DeviceMonitor::factory()->create(['uptime' => 90061]); // 1d 1h 1m 1s

        $uptime = $monitor->getUptimeHuman();

        $this->assertStringContainsString('1d', $uptime);
        $this->assertStringContainsString('1h', $uptime);
        $this->assertStringContainsString('1m', $uptime);
    }

    public function test_get_uptime_human_returns_null_for_no_uptime(): void
    {
        $monitor = DeviceMonitor::factory()->create(['uptime' => null]);

        $this->assertNull($monitor->getUptimeHuman());
    }

    public function test_scope_device_type_filters_by_type(): void
    {
        DeviceMonitor::factory()->create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
        ]);
        DeviceMonitor::factory()->create([
            'monitorable_type' => 'App\\Models\\Olt',
        ]);

        $routers = DeviceMonitor::deviceType('App\\Models\\MikrotikRouter')->get();

        $this->assertCount(1, $routers);
        $this->assertEquals('App\\Models\\MikrotikRouter', $routers->first()->monitorable_type);
    }
}
