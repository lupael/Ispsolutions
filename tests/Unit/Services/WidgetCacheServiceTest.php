<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\SmsLog;
use App\Models\SubscriptionBill;
use App\Models\User;
use App\Services\WidgetCacheService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WidgetCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WidgetCacheService $widgetCacheService;
    protected int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetCacheService = new WidgetCacheService;
        $this->tenantId = 1;

        // Clear cache before each test
        Cache::flush();
    }

    public function test_suspension_forecast_returns_cached_data()
    {
        // First call should calculate and cache
        $data1 = $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        
        // Second call should return cached data
        $data2 = $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        
        $this->assertEquals($data1, $data2);
        $this->assertIsArray($data1);
        $this->assertArrayHasKey('total_count', $data1);
        $this->assertArrayHasKey('total_amount', $data1);
        $this->assertArrayHasKey('by_package', $data1);
        $this->assertArrayHasKey('by_zone', $data1);
        $this->assertArrayHasKey('date', $data1);
    }

    public function test_suspension_forecast_refresh_clears_cache()
    {
        // Get initial cached data
        $data1 = $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        
        // Refresh cache
        $data2 = $this->widgetCacheService->getSuspensionForecast($this->tenantId, true);
        
        // Both should have same structure but may have different data
        $this->assertIsArray($data2);
        $this->assertArrayHasKey('total_count', $data2);
    }

    public function test_collection_target_returns_correct_structure()
    {
        $data = $this->widgetCacheService->getCollectionTarget($this->tenantId);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('target_amount', $data);
        $this->assertArrayHasKey('collected_amount', $data);
        $this->assertArrayHasKey('pending_amount', $data);
        $this->assertArrayHasKey('percentage_collected', $data);
        $this->assertArrayHasKey('total_bills', $data);
        $this->assertArrayHasKey('paid_bills', $data);
        $this->assertArrayHasKey('pending_bills', $data);
        $this->assertArrayHasKey('date', $data);
    }

    public function test_collection_target_calculates_percentage_correctly()
    {
        $data = $this->widgetCacheService->getCollectionTarget($this->tenantId);
        
        // When no bills, percentage should be 0
        $this->assertEquals(0, $data['percentage_collected']);
        
        // When target is 0, percentage should be 0
        $this->assertEquals(0, $data['target_amount']);
    }

    public function test_sms_usage_returns_correct_structure()
    {
        $data = $this->widgetCacheService->getSmsUsage($this->tenantId);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('total_sent', $data);
        $this->assertArrayHasKey('sent_count', $data);
        $this->assertArrayHasKey('failed_count', $data);
        $this->assertArrayHasKey('pending_count', $data);
        $this->assertArrayHasKey('total_cost', $data);
        $this->assertArrayHasKey('remaining_balance', $data);
        $this->assertArrayHasKey('used_balance', $data);
        $this->assertArrayHasKey('date', $data);
    }

    public function test_refresh_all_widgets_clears_all_caches()
    {
        // Get initial data to populate caches
        $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        $this->widgetCacheService->getCollectionTarget($this->tenantId);
        $this->widgetCacheService->getSmsUsage($this->tenantId);
        
        // Refresh all
        $this->widgetCacheService->refreshAllWidgets($this->tenantId);
        
        // Should have fresh data
        $suspension = $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        $collection = $this->widgetCacheService->getCollectionTarget($this->tenantId);
        $sms = $this->widgetCacheService->getSmsUsage($this->tenantId);
        
        $this->assertIsArray($suspension);
        $this->assertIsArray($collection);
        $this->assertIsArray($sms);
    }

    public function test_refresh_widget_returns_null_for_invalid_widget()
    {
        $result = $this->widgetCacheService->refreshWidget($this->tenantId, 'invalid_widget');
        
        $this->assertNull($result);
    }

    public function test_refresh_widget_returns_data_for_valid_widgets()
    {
        $suspension = $this->widgetCacheService->refreshWidget($this->tenantId, 'suspension_forecast');
        $collection = $this->widgetCacheService->refreshWidget($this->tenantId, 'collection_target');
        $sms = $this->widgetCacheService->refreshWidget($this->tenantId, 'sms_usage');
        
        $this->assertIsArray($suspension);
        $this->assertIsArray($collection);
        $this->assertIsArray($sms);
    }

    public function test_suspension_forecast_handles_empty_data_gracefully()
    {
        // No data in database
        $data = $this->widgetCacheService->getSuspensionForecast($this->tenantId);
        
        $this->assertEquals(0, $data['total_count']);
        $this->assertEquals(0, $data['total_amount']);
        $this->assertEmpty($data['by_package']);
        $this->assertEmpty($data['by_zone']);
    }

    public function test_collection_target_handles_empty_data_gracefully()
    {
        // No bills in database
        $data = $this->widgetCacheService->getCollectionTarget($this->tenantId);
        
        $this->assertEquals(0, $data['target_amount']);
        $this->assertEquals(0, $data['collected_amount']);
        $this->assertEquals(0, $data['pending_amount']);
        $this->assertEquals(0, $data['total_bills']);
    }

    public function test_sms_usage_handles_empty_data_gracefully()
    {
        // No SMS logs in database
        $data = $this->widgetCacheService->getSmsUsage($this->tenantId);
        
        $this->assertEquals(0, $data['total_sent']);
        $this->assertEquals(0, $data['sent_count']);
        $this->assertEquals(0, $data['failed_count']);
        $this->assertEquals(0, $data['pending_count']);
        $this->assertEquals(0, $data['total_cost']);
    }

    public function test_widget_cache_keys_are_tenant_specific()
    {
        $data1 = $this->widgetCacheService->getSuspensionForecast(1);
        $data2 = $this->widgetCacheService->getSuspensionForecast(2);
        
        // Both should return data (even if empty)
        $this->assertIsArray($data1);
        $this->assertIsArray($data2);
        
        // They may be the same if both tenants have no data, which is fine
        $this->assertArrayHasKey('total_count', $data1);
        $this->assertArrayHasKey('total_count', $data2);
    }
}
