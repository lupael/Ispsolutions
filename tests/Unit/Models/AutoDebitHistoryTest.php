<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AutoDebitHistory;
use App\Models\User;
use App\Models\SubscriptionBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auto-Debit History Model Tests
 * 
 * Tests the AutoDebitHistory model methods and relationships
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class AutoDebitHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test auto-debit history can be created
     */
    public function test_auto_debit_history_can_be_created(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'retry_count' => 0,
            'payment_method' => 'bkash',
            'attempted_at' => now(),
        ]);

        $this->assertInstanceOf(AutoDebitHistory::class, $history);
        $this->assertEquals($customer->id, $history->customer_id);
        $this->assertEquals(100.00, $history->amount);
        $this->assertEquals('pending', $history->status);
    }

    /**
     * Test auto-debit history belongs to customer
     */
    public function test_auto_debit_history_belongs_to_customer(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $history->customer);
        $this->assertEquals($customer->id, $history->customer->id);
    }

    /**
     * Test isSuccessful method returns true for success status
     */
    public function test_is_successful_returns_true_for_success_status(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        $this->assertTrue($history->isSuccessful());
        $this->assertFalse($history->isFailed());
        $this->assertFalse($history->isPending());
    }

    /**
     * Test isFailed method returns true for failed status
     */
    public function test_is_failed_returns_true_for_failed_status(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'failure_reason' => 'Insufficient funds',
            'attempted_at' => now(),
        ]);

        $this->assertTrue($history->isFailed());
        $this->assertFalse($history->isSuccessful());
        $this->assertFalse($history->isPending());
    }

    /**
     * Test isPending method returns true for pending status
     */
    public function test_is_pending_returns_true_for_pending_status(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        $this->assertTrue($history->isPending());
        $this->assertFalse($history->isSuccessful());
        $this->assertFalse($history->isFailed());
    }

    /**
     * Test markSuccessful updates status
     */
    public function test_mark_successful_updates_status(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        $history->markSuccessful('TXN12345');

        $this->assertEquals('success', $history->fresh()->status);
        $this->assertEquals('TXN12345', $history->fresh()->transaction_id);
    }

    /**
     * Test markFailed updates status and reason
     */
    public function test_mark_failed_updates_status_and_reason(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        $history->markFailed('Payment gateway timeout');

        $this->assertEquals('failed', $history->fresh()->status);
        $this->assertEquals('Payment gateway timeout', $history->fresh()->failure_reason);
    }

    /**
     * Test incrementRetryCount increments counter
     */
    public function test_increment_retry_count_increments_counter(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        $history = AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'retry_count' => 0,
            'attempted_at' => now(),
        ]);

        $history->incrementRetryCount();

        $this->assertEquals(1, $history->fresh()->retry_count);

        $history->incrementRetryCount();

        $this->assertEquals(2, $history->fresh()->retry_count);
    }

    /**
     * Test scopeSuccessful filters successful records
     */
    public function test_scope_successful_filters_successful_records(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        $successful = AutoDebitHistory::successful()->get();

        $this->assertCount(1, $successful);
        $this->assertEquals('success', $successful->first()->status);
    }

    /**
     * Test scopeFailed filters failed records
     */
    public function test_scope_failed_filters_failed_records(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        $failed = AutoDebitHistory::failed()->get();

        $this->assertCount(2, $failed);
        $this->assertTrue($failed->every(fn($h) => $h->status === 'failed'));
    }

    /**
     * Test scopePending filters pending records
     */
    public function test_scope_pending_filters_pending_records(): void
    {
        $customer = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        $pending = AutoDebitHistory::pending()->get();

        $this->assertCount(1, $pending);
        $this->assertEquals('pending', $pending->first()->status);
    }

    /**
     * Test scopeForCustomer filters by customer
     */
    public function test_scope_for_customer_filters_by_customer(): void
    {
        $customer1 = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);
        $customer2 = User::factory()->create(['operator_level' => User::OPERATOR_LEVEL_CUSTOMER]);

        AutoDebitHistory::create([
            'customer_id' => $customer1->id,
            'amount' => 100.00,
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer1->id,
            'amount' => 100.00,
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        AutoDebitHistory::create([
            'customer_id' => $customer2->id,
            'amount' => 100.00,
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        $customer1History = AutoDebitHistory::forCustomer($customer1->id)->get();
        $customer2History = AutoDebitHistory::forCustomer($customer2->id)->get();

        $this->assertCount(2, $customer1History);
        $this->assertCount(1, $customer2History);
        $this->assertTrue($customer1History->every(fn($h) => $h->customer_id === $customer1->id));
        $this->assertTrue($customer2History->every(fn($h) => $h->customer_id === $customer2->id));
    }

    /**
     * Test table name is correctly set
     */
    public function test_table_name_is_correctly_set(): void
    {
        $model = new AutoDebitHistory();
        $this->assertEquals('auto_debit_history', $model->getTable());
    }
}
