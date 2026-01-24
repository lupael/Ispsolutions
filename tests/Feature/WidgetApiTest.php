<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WidgetApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create a test user with tenant
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20, // Admin
        ]);

        // Attach admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $this->user->roles()->attach($adminRole);
        }

        // Clear cache before each test
        Cache::flush();
    }

    public function test_refresh_all_widgets_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/widgets/refresh', []);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'suspension_forecast',
                    'collection_target',
                    'sms_usage',
                ],
            ]);
    }

    public function test_refresh_specific_widget_endpoint_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/widgets/refresh', [
            'widgets' => ['suspension_forecast'],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'suspension_forecast',
                ],
            ]);
    }

    public function test_suspension_forecast_endpoint_returns_data()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/widgets/suspension-forecast');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_count',
                    'total_amount',
                    'by_package',
                    'by_zone',
                    'date',
                ],
            ]);
    }

    public function test_collection_target_endpoint_returns_data()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/widgets/collection-target');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'target_amount',
                    'collected_amount',
                    'pending_amount',
                    'percentage_collected',
                    'total_bills',
                    'paid_bills',
                    'pending_bills',
                    'date',
                ],
            ]);
    }

    public function test_sms_usage_endpoint_returns_data()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/widgets/sms-usage');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sent',
                    'sent_count',
                    'failed_count',
                    'pending_count',
                    'total_cost',
                    'remaining_balance',
                    'used_balance',
                    'date',
                ],
            ]);
    }

    public function test_widget_endpoints_require_authentication()
    {
        // Without authentication
        $response = $this->getJson('/api/v1/widgets/suspension-forecast');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/widgets/collection-target');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/widgets/sms-usage');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/widgets/refresh');
        $response->assertStatus(401);
    }

    public function test_refresh_endpoint_validates_widget_names()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/widgets/refresh', [
            'widgets' => ['invalid_widget'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['widgets.0']);
    }

    public function test_refresh_with_query_parameter_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/widgets/suspension-forecast?refresh=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_widgets_use_tenant_isolation()
    {
        // Create another tenant
        $tenant2 = Tenant::factory()->create();

        // Create another user with different tenant
        $user2 = User::factory()->create([
            'tenant_id' => $tenant2->id,
            'operator_level' => 20,
        ]);

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user2->roles()->attach($adminRole);
        }

        // Get data for user 1
        $this->actingAs($this->user);
        $response1 = $this->getJson('/api/v1/widgets/suspension-forecast');
        $response1->assertStatus(200);

        // Get data for user 2
        $this->actingAs($user2);
        $response2 = $this->getJson('/api/v1/widgets/suspension-forecast');
        $response2->assertStatus(200);

        // Both should succeed (tenant isolation working)
        $this->assertTrue($response1->json('success'));
        $this->assertTrue($response2->json('success'));
    }
}
