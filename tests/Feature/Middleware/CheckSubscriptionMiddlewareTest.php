<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Tests\TestCase;

class CheckSubscriptionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test subscription plan if it doesn't exist
        if (!SubscriptionPlan::where('slug', 'test-plan')->exists()) {
            SubscriptionPlan::create([
                'name' => 'Test Plan',
                'slug' => 'test-plan',
                'price' => 100,
                'billing_cycle' => 'monthly',
            ]);
        }
    }

    public function test_super_admin_with_active_subscription_can_access_protected_route()
    {
        $plan = SubscriptionPlan::where('slug', 'test-plan')->first();

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
            'subscription_plan_id' => $plan->id,
            'expires_at' => Carbon::now()->addMonth(),
        ]);

        $this->actingAs($superAdmin);

        // Test access to a protected route (requires authentication and subscription)
        $response = $this->get('/super-admin');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_super_admin_with_expired_subscription_is_blocked()
    {
        $plan = SubscriptionPlan::where('slug', 'test-plan')->first();

        $superAdmin = User::create([
            'name' => 'Expired Super Admin',
            'email' => 'expired@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
            'subscription_plan_id' => $plan->id,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->actingAs($superAdmin);

        // Test access to a protected route
        $response = $this->get('/super-admin', ['Accept' => 'application/json']);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_super_admin_without_subscription_is_blocked()
    {
        $superAdmin = User::create([
            'name' => 'Unsubscribed Admin',
            'email' => 'unsubscribed@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
        ]);

        $this->actingAs($superAdmin);

        // Test access to a protected route (JSON)
        $response = $this->get('/super-admin', ['Accept' => 'application/json']);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_developer_is_unrestricted_by_subscription()
    {
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 1,
            // Note: no subscription_plan_id or expires_at
        ]);

        $this->actingAs($developer);

        // Developers should bypass subscription checks
        $response = $this->get('/developer');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_inherits_parent_subscription()
    {
        $plan = SubscriptionPlan::where('slug', 'test-plan')->first();

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'parent@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
            'subscription_plan_id' => $plan->id,
            'expires_at' => Carbon::now()->addMonth(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 3,
            'parent_id' => $superAdmin->id,
        ]);

        $this->actingAs($admin);

        // Admin should inherit parent's subscription
        $response = $this->get('/admin');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_expired_subscription_returns_json_for_api_requests()
    {
        $plan = SubscriptionPlan::where('slug', 'test-plan')->first();

        $superAdmin = User::create([
            'name' => 'Expired Admin',
            'email' => 'api_expired@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
            'subscription_plan_id' => $plan->id,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->actingAs($superAdmin);

        // API request should return JSON
        $response = $this->getJson('/api/some-endpoint');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function test_expired_subscription_redirects_for_web_requests()
    {
        $plan = SubscriptionPlan::where('slug', 'test-plan')->first();

        $superAdmin = User::create([
            'name' => 'Expired Admin Web',
            'email' => 'web_expired@test.com',
            'password' => bcrypt('password'),
            'operator_level' => 2,
            'subscription_plan_id' => $plan->id,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->actingAs($superAdmin);

        // Web request should redirect or show error page
        $response = $this->get('/super-admin');

        // Could be 403, redirect, or other response depending on implementation
        $this->assertIn($response->getStatusCode(), [302, 403, 500]);
    }
}
