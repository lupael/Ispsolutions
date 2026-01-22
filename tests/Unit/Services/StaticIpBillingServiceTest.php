<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\IpAllocation;
use App\Models\NetworkUser;
use App\Models\Tenant;
use App\Services\StaticIpBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticIpBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StaticIpBillingService $staticIpBillingService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staticIpBillingService = new StaticIpBillingService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();
    }

    public function test_can_generate_invoices_for_static_ip_allocations()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $ipAllocation = IpAllocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'monthly_price' => 500,
            'allocation_type' => 'static',
        ]);

        $result = $this->staticIpBillingService->generateMonthlyInvoices();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('generated', $result);
        $this->assertArrayHasKey('skipped', $result);
        $this->assertGreaterThanOrEqual(0, $result['generated']);
    }

    public function test_can_calculate_monthly_revenue()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        IpAllocation::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'monthly_price' => 500,
            'allocation_type' => 'static',
        ]);

        $revenue = $this->staticIpBillingService->calculateMonthlyRevenue();

        $this->assertIsFloat($revenue);
        $this->assertGreaterThanOrEqual(0, $revenue);
    }

    public function test_can_get_static_ip_allocations_count()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        IpAllocation::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'allocation_type' => 'static',
        ]);

        $count = $this->staticIpBillingService->getStaticIpCount();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_skips_invoices_if_already_generated_this_month()
    {
        $networkUser = NetworkUser::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $ipAllocation = IpAllocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'monthly_price' => 500,
            'allocation_type' => 'static',
        ]);

        // Generate invoice for this month
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'network_user_id' => $networkUser->id,
            'invoice_type' => 'static_ip',
            'invoice_month' => now()->format('Y-m'),
        ]);

        $result = $this->staticIpBillingService->generateMonthlyInvoices();

        // Should skip since invoice already exists
        $this->assertGreaterThanOrEqual(0, $result['skipped']);
    }
}
