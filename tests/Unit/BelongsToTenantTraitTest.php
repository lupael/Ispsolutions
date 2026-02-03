<?php

namespace Tests\Unit;

use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BelongsToTenantTraitTest extends TestCase
{
    use RefreshDatabase;

    private TenancyService $tenancyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenancyService = app(TenancyService::class);
    }

    public function test_auto_assigns_tenant_id_on_create(): void
    {
        $tenant = Tenant::factory()->create();
        $this->tenancyService->setCurrentTenant($tenant);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals($tenant->id, $user->tenant_id);
    }

    public function test_global_scope_filters_by_current_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant1);
        $user1 = User::factory()->create(['email' => 'user1@example.com']);

        $this->tenancyService->setCurrentTenant($tenant2);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Query should only return users for current tenant
        $this->tenancyService->setCurrentTenant($tenant1);
        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }

    public function test_all_tenants_scope_bypasses_global_scope(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant1);
        User::factory()->create(['email' => 'user1@example.com']);

        $this->tenancyService->setCurrentTenant($tenant2);
        User::factory()->create(['email' => 'user2@example.com']);

        // Query with allTenants should return all users
        $this->tenancyService->setCurrentTenant($tenant1);
        $users = User::allTenants()->get();

        $this->assertGreaterThanOrEqual(2, $users->count());
    }

    public function test_for_tenant_scope_filters_by_specific_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant1);
        $user1 = User::factory()->create(['email' => 'user1@example.com']);

        $this->tenancyService->setCurrentTenant($tenant2);
        User::factory()->create(['email' => 'user2@example.com']);

        // Query for specific tenant
        $users = User::allTenants()->forTenant($tenant1->id)->get();

        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }

    public function test_does_not_override_explicitly_set_tenant_id(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->tenancyService->setCurrentTenant($tenant1);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'tenant_id' => $tenant2->id,
        ]);

        $this->assertEquals($tenant2->id, $user->tenant_id);
    }

    /**
     * Test that queries with joins don't produce ambiguous column errors.
     *
     * This test replicates the scenario from the bug report where a User
     * query with a Package join fails due to ambiguous tenant_id column.
     *
     * Related issue: SQLSTATE[23000]: Integrity constraint violation: 1052
     * Column 'tenant_id' in where clause is ambiguous
     */
    public function test_user_package_join_does_not_produce_ambiguous_column_error(): void
    {
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Create a tenant
        $tenant = Tenant::factory()->create();
        $this->tenancyService->setCurrentTenant($tenant);

        // Create a package
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Package',
            'price' => 100.00,
        ]);

        // Create a customer with the package
        $customer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'operator_level' => User::OPERATOR_LEVEL_CUSTOMER,
            'service_package_id' => $package->id,
            'status' => 'active',
        ]);

        // This query should NOT throw an ambiguous column error
        // It mimics the query from ISPController that was failing
        $result = User::where('operator_level', User::OPERATOR_LEVEL_CUSTOMER)
            ->whereNotNull('service_package_id')
            ->join('packages', 'users.service_package_id', '=', 'packages.id')
            ->where('users.status', 'active')
            ->sum('packages.price');

        // Assert that the query executed successfully and returned expected value
        $this->assertEquals(100.00, $result);
    }
}
