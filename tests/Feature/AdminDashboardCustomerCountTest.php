<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardCustomerCountTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create roles
        Role::factory()->create(['name' => 'admin', 'slug' => 'admin', 'level' => 20]);
        Role::factory()->create(['name' => 'customer', 'slug' => 'customer', 'level' => 100]);

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_new_customers_today_counts_only_subscribers(): void
    {
        // Act as admin
        $this->actingAs($this->admin);

        // Create a user with customer role but is_subscriber = false (not a real subscriber)
        $nonSubscriberCustomer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_subscriber' => false,
            'created_at' => now(),
        ]);
        $nonSubscriberCustomer->assignRole('customer');

        // Create a user with customer role and is_subscriber = true (real subscriber)
        $subscriberCustomer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_subscriber' => true,
            'created_at' => now(),
        ]);
        $subscriberCustomer->assignRole('customer');

        // Visit admin dashboard
        $response = $this->get(route('panel.isp.dashboard'));

        // Assert response is OK
        $response->assertOk();

        // Check that new_customers_today only counts the subscriber (1, not 2)
        $response->assertViewHas('stats', function ($stats) {
            // Should be 1 (only the subscriber), not 2
            return $stats['new_customers_today'] === 1;
        });
    }

    public function test_dashboard_customer_stats_consistent_with_customer_list(): void
    {
        // Act as admin
        $this->actingAs($this->admin);

        // Create multiple customers with is_subscriber = true
        $subscriber1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_subscriber' => true,
            'status' => 'active',
            'created_at' => now(),
        ]);
        $subscriber1->assignRole('customer');

        $subscriber2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_subscriber' => true,
            'status' => 'active',
            'created_at' => now(),
        ]);
        $subscriber2->assignRole('customer');

        // Create a user with customer role but is_subscriber = false
        $nonSubscriber = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_subscriber' => false,
            'created_at' => now(),
        ]);
        $nonSubscriber->assignRole('customer');

        // Visit dashboard
        $dashboardResponse = $this->get(route('panel.isp.dashboard'));
        $dashboardResponse->assertOk();

        // Visit customer list
        $customersResponse = $this->get(route('panel.isp.customers.index'));
        $customersResponse->assertOk();

        // Extract stats from dashboard
        $dashboardStats = $dashboardResponse->viewData('stats');
        
        // Extract stats from customer list
        $customerStats = $customersResponse->viewData('stats');

        // new_customers_today on dashboard should count only subscribers (2)
        $this->assertEquals(2, $dashboardStats['new_customers_today']);
        
        // total count on customer list should also be 2 (only subscribers)
        $this->assertEquals(2, $customerStats['total']);
    }
}
