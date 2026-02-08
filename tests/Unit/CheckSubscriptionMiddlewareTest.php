<?php

namespace Tests\Unit;

use App\Models\User;
use App\Http\Middleware\CheckSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckSubscriptionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_developer_role_bypasses_subscription_check()
    {
        $developer = User::factory()->state([
            'operator_level' => 0,
            'subscription_plan_id' => null,
            'expires_at' => null,
        ])->create();

        $request = $this->actingAs($developer)
            ->get('/developer/dashboard');

        $request->assertStatus(200);
    }

    public function test_super_admin_with_valid_subscription_allowed()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => 1,
            'expires_at' => Carbon::now()->addMonths(3),
        ])->create();

        $this->actingAs($superAdmin)
            ->get('/super-admin/dashboard')
            ->assertStatus(200);
    }

    public function test_super_admin_without_subscription_blocked()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => null,
            'expires_at' => null,
        ])->create();

        $response = $this->actingAs($superAdmin)
            ->get('/super-admin/dashboard');

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'No active subscription.');
    }

    public function test_super_admin_with_expired_subscription_blocked()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => 1,
            'expires_at' => Carbon::now()->subDays(5),
        ])->create();

        $response = $this->actingAs($superAdmin)
            ->get('/super-admin/dashboard');

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Subscription expired.');
    }

    public function test_admin_inherits_parent_subscription()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => 1,
            'expires_at' => Carbon::now()->addMonths(3),
        ])->create();

        $admin = User::factory()->state([
            'operator_level' => 2,
            'parent_id' => $superAdmin->id,
            'subscription_plan_id' => null,
            'expires_at' => null,
        ])->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_operator_inherits_parent_subscription()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => 1,
            'expires_at' => Carbon::now()->addMonths(3),
        ])->create();

        $operator = User::factory()->state([
            'operator_level' => 3,
            'parent_id' => $superAdmin->id,
            'subscription_plan_id' => null,
            'expires_at' => null,
        ])->create();

        $this->actingAs($operator)
            ->get('/operator/dashboard')
            ->assertStatus(200);
    }

    public function test_admin_without_valid_parent_subscription_blocked()
    {
        $expiredSuperAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => 1,
            'expires_at' => Carbon::now()->subDays(1),
        ])->create();

        $admin = User::factory()->state([
            'operator_level' => 2,
            'parent_id' => $expiredSuperAdmin->id,
            'subscription_plan_id' => null,
            'expires_at' => null,
        ])->create();

        $response = $this->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_middleware_redirects_web_requests_on_auth_failure()
    {
        $superAdmin = User::factory()->state([
            'operator_level' => 1,
            'subscription_plan_id' => null,
        ])->create();

        $response = $this->actingAs($superAdmin)
            ->post('/super-admin/dashboard');

        $response->assertStatus(403);
    }
}
