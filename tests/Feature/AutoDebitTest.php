<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessAutoDebitJob;
use App\Models\AutoDebitHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Auto-Debit Feature Tests
 *
 * Tests the auto-debit system functionality
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class AutoDebitTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test customer with auto-debit enabled
        $this->customer = User::factory()->create([
            'auto_debit_enabled' => true,
            'auto_debit_payment_method' => 'bkash',
            'auto_debit_max_retries' => 3,
            'auto_debit_retry_count' => 0,
        ]);
    }

    public function test_customer_can_view_auto_debit_settings(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/api/auto-debit/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_debit_enabled' => true,
                    'auto_debit_payment_method' => 'bkash',
                    'auto_debit_max_retries' => 3,
                ],
            ]);
    }

    public function test_customer_can_enable_auto_debit(): void
    {
        $customer = User::factory()->create([
            'auto_debit_enabled' => false,
        ]);

        $response = $this->actingAs($customer)
            ->putJson('/api/auto-debit/settings', [
                'auto_debit_enabled' => true,
                'auto_debit_payment_method' => 'bkash',
                'auto_debit_max_retries' => 3,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Auto-debit settings updated successfully',
            ]);

        $this->assertTrue($customer->fresh()->auto_debit_enabled);
        $this->assertEquals('bkash', $customer->fresh()->auto_debit_payment_method);
    }

    public function test_customer_can_disable_auto_debit(): void
    {
        $response = $this->actingAs($this->customer)
            ->putJson('/api/auto-debit/settings', [
                'auto_debit_enabled' => false,
                'auto_debit_payment_method' => null,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($this->customer->fresh()->auto_debit_enabled);
    }

    public function test_payment_method_required_when_enabling_auto_debit(): void
    {
        $customer = User::factory()->create([
            'auto_debit_enabled' => false,
        ]);

        $response = $this->actingAs($customer)
            ->putJson('/api/auto-debit/settings', [
                'auto_debit_enabled' => true,
                'auto_debit_payment_method' => null,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['auto_debit_payment_method']);
    }

    public function test_invalid_payment_method_rejected(): void
    {
        $response = $this->actingAs($this->customer)
            ->putJson('/api/auto-debit/settings', [
                'auto_debit_enabled' => true,
                'auto_debit_payment_method' => 'invalid_gateway',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['auto_debit_payment_method']);
    }

    public function test_customer_can_view_auto_debit_history(): void
    {
        // Create some history records
        AutoDebitHistory::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->customer)
            ->get('/api/auto-debit/history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'customer_id',
                            'amount',
                            'status',
                            'attempted_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_auto_debit_job_dispatched_for_eligible_customers(): void
    {
        Queue::fake();

        // Dispatch job
        ProcessAutoDebitJob::dispatch($this->customer);

        // Assert job was pushed
        Queue::assertPushed(ProcessAutoDebitJob::class, function ($job) {
            return $job->customer->id === $this->customer->id;
        });
    }

    public function test_admin_can_view_failed_auto_debit_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create failed attempts
        AutoDebitHistory::factory()->count(2)->create([
            'customer_id' => $this->customer->id,
            'status' => 'failed',
        ]);

        $response = $this->actingAs($admin)
            ->get('/api/auto-debit/failed-report');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_non_admin_cannot_view_failed_report(): void
    {
        $response = $this->actingAs($this->customer)
            ->get('/api/auto-debit/failed-report');

        $response->assertStatus(403);
    }

    public function test_admin_can_trigger_auto_debit_manually(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)
            ->postJson("/api/auto-debit/trigger/{$this->customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Auto-debit process triggered successfully',
            ]);

        Queue::assertPushed(ProcessAutoDebitJob::class);
    }

    public function test_admin_can_reset_retry_count(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->customer->update(['auto_debit_retry_count' => 3]);

        $response = $this->actingAs($admin)
            ->postJson("/api/auto-debit/reset-retry/{$this->customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_debit_retry_count' => 0,
                ],
            ]);

        $this->assertEquals(0, $this->customer->fresh()->auto_debit_retry_count);
    }

    public function test_non_admin_cannot_trigger_auto_debit(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson("/api/auto-debit/trigger/{$this->customer->id}");

        $response->assertStatus(403);
    }

    public function test_retry_count_resets_when_re_enabling_auto_debit(): void
    {
        $this->customer->update(['auto_debit_retry_count' => 2]);

        $response = $this->actingAs($this->customer)
            ->putJson('/api/auto-debit/settings', [
                'auto_debit_enabled' => true,
                'auto_debit_payment_method' => 'bkash',
                'auto_debit_max_retries' => 3,
            ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $this->customer->fresh()->auto_debit_retry_count);
    }
}
