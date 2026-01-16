<?php

namespace Tests\Unit\Services;

use App\Models\Tenant;
use App\Services\TenancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyServiceTest extends TestCase
{
    use RefreshDatabase;

    private TenancyService $tenancyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenancyService = new TenancyService();
    }

    public function test_can_set_and_get_current_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant);
        $currentTenant = $this->tenancyService->getCurrentTenant();

        $this->assertNotNull($currentTenant);
        $this->assertEquals($tenant->id, $currentTenant->id);
    }

    public function test_can_get_current_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant);
        $tenantId = $this->tenancyService->getCurrentTenantId();

        $this->assertEquals($tenant->id, $tenantId);
    }

    public function test_has_tenant_returns_true_when_tenant_is_set(): void
    {
        $tenant = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant);

        $this->assertTrue($this->tenancyService->hasTenant());
    }

    public function test_has_tenant_returns_false_when_no_tenant(): void
    {
        $this->assertFalse($this->tenancyService->hasTenant());
    }

    public function test_can_resolve_tenant_by_domain(): void
    {
        $tenant = Tenant::factory()->create([
            'domain' => 'test-isp.com',
            'status' => 'active',
        ]);

        $resolved = $this->tenancyService->resolveTenantByDomain('test-isp.com');

        $this->assertNotNull($resolved);
        $this->assertEquals($tenant->id, $resolved->id);
    }

    public function test_can_resolve_tenant_by_subdomain(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'test',
            'status' => 'active',
        ]);

        $resolved = $this->tenancyService->resolveTenantByDomain('test.example.com');

        $this->assertNotNull($resolved);
        $this->assertEquals($tenant->id, $resolved->id);
    }

    public function test_does_not_resolve_inactive_tenant(): void
    {
        Tenant::factory()->create([
            'domain' => 'test-isp.com',
            'status' => 'inactive',
        ]);

        $resolved = $this->tenancyService->resolveTenantByDomain('test-isp.com');

        $this->assertNull($resolved);
    }

    public function test_can_run_callback_in_tenant_context(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant1);

        $result = $this->tenancyService->runForTenant($tenant2, function () {
            return $this->tenancyService->getCurrentTenantId();
        });

        $this->assertEquals($tenant2->id, $result);
        $this->assertEquals($tenant1->id, $this->tenancyService->getCurrentTenantId());
    }

    public function test_can_forget_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant);
        $this->assertTrue($this->tenancyService->hasTenant());

        $this->tenancyService->forgetTenant();
        $this->assertFalse($this->tenancyService->hasTenant());
    }
}
