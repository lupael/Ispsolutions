<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\BandwidthUsage;
use App\Models\MikrotikRouter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BandwidthUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_bandwidth_usage_belongs_to_monitorable(): void
    {
        $router = MikrotikRouter::factory()->create();
        $usage = BandwidthUsage::create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router->id,
            'timestamp' => now(),
            'upload_bytes' => 1024000,
            'download_bytes' => 2048000,
            'total_bytes' => 3072000,
            'period_type' => 'raw',
        ]);

        $this->assertInstanceOf(MikrotikRouter::class, $usage->monitorable);
        $this->assertEquals($router->id, $usage->monitorable->id);
    }

    public function test_scope_device_filters_by_type_and_id(): void
    {
        $router1 = MikrotikRouter::factory()->create();
        $router2 = MikrotikRouter::factory()->create();

        BandwidthUsage::factory()->create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router1->id,
        ]);
        BandwidthUsage::factory()->create([
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => $router2->id,
        ]);

        $usages = BandwidthUsage::device('App\\Models\\MikrotikRouter', $router1->id)->get();

        $this->assertCount(1, $usages);
        $this->assertEquals($router1->id, $usages->first()->monitorable_id);
    }

    public function test_scope_period_type_filters_correctly(): void
    {
        BandwidthUsage::factory()->create(['period_type' => 'raw']);
        BandwidthUsage::factory()->create(['period_type' => 'hourly']);
        BandwidthUsage::factory()->create(['period_type' => 'daily']);

        $hourly = BandwidthUsage::hourly()->get();
        $daily = BandwidthUsage::daily()->get();

        $this->assertCount(1, $hourly);
        $this->assertCount(1, $daily);
        $this->assertEquals('hourly', $hourly->first()->period_type);
        $this->assertEquals('daily', $daily->first()->period_type);
    }

    public function test_scope_date_range_filters_by_date(): void
    {
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        BandwidthUsage::factory()->create([
            'timestamp' => Carbon::now()->subDays(10),
        ]);
        BandwidthUsage::factory()->create([
            'timestamp' => Carbon::now()->subDays(5),
        ]);
        BandwidthUsage::factory()->create([
            'timestamp' => Carbon::now(),
        ]);

        $usages = BandwidthUsage::dateRange($startDate, $endDate)->get();

        $this->assertCount(2, $usages);
    }

    public function test_get_upload_human_formats_bytes(): void
    {
        $usage = BandwidthUsage::factory()->create([
            'upload_bytes' => 1048576, // 1 MB
        ]);

        $human = $usage->getUploadHuman();

        $this->assertStringContainsString('1.00', $human);
        $this->assertStringContainsString('MB', $human);
    }

    public function test_get_download_human_formats_bytes(): void
    {
        $usage = BandwidthUsage::factory()->create([
            'download_bytes' => 2097152, // 2 MB
        ]);

        $human = $usage->getDownloadHuman();

        $this->assertStringContainsString('2.00', $human);
        $this->assertStringContainsString('MB', $human);
    }

    public function test_get_total_human_formats_bytes(): void
    {
        $usage = BandwidthUsage::factory()->create([
            'total_bytes' => 1073741824, // 1 GB
        ]);

        $human = $usage->getTotalHuman();

        $this->assertStringContainsString('1.00', $human);
        $this->assertStringContainsString('GB', $human);
    }

    public function test_format_bytes_handles_large_values(): void
    {
        $usage = BandwidthUsage::factory()->create([
            'total_bytes' => 1099511627776, // 1 TB
        ]);

        $human = $usage->getTotalHuman();

        $this->assertStringContainsString('1.00', $human);
        $this->assertStringContainsString('TB', $human);
    }

    public function test_scope_raw_filters_raw_data(): void
    {
        BandwidthUsage::factory()->create(['period_type' => 'raw']);
        BandwidthUsage::factory()->create(['period_type' => 'hourly']);

        $raw = BandwidthUsage::raw()->get();

        $this->assertCount(1, $raw);
        $this->assertEquals('raw', $raw->first()->period_type);
    }

    public function test_scope_weekly_filters_weekly_data(): void
    {
        BandwidthUsage::factory()->create(['period_type' => 'weekly']);
        BandwidthUsage::factory()->create(['period_type' => 'daily']);

        $weekly = BandwidthUsage::weekly()->get();

        $this->assertCount(1, $weekly);
        $this->assertEquals('weekly', $weekly->first()->period_type);
    }

    public function test_scope_monthly_filters_monthly_data(): void
    {
        BandwidthUsage::factory()->create(['period_type' => 'monthly']);
        BandwidthUsage::factory()->create(['period_type' => 'weekly']);

        $monthly = BandwidthUsage::monthly()->get();

        $this->assertCount(1, $monthly);
        $this->assertEquals('monthly', $monthly->first()->period_type);
    }
}
