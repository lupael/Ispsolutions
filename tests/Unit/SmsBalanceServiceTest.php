<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\SmsBalanceHistory;
use App\Services\SmsBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SMS Balance Service Tests
 * 
 * Tests the SmsBalanceService for SMS credit management
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class SmsBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SmsBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SmsBalanceService();
    }

    /**
     * Test adding SMS credits to operator balance
     */
    public function test_add_credits(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 100,
        ]);

        $history = $this->service->addCredits($operator, 500, 'purchase');

        $this->assertInstanceOf(SmsBalanceHistory::class, $history);
        $this->assertEquals(100, $history->balance_before);
        $this->assertEquals(600, $history->balance_after);
        $this->assertEquals(500, $history->amount);
        $this->assertEquals('purchase', $history->transaction_type);
        
        $operator->refresh();
        $this->assertEquals(600, $operator->sms_balance);
    }

    /**
     * Test deducting SMS credits from operator balance
     */
    public function test_deduct_credits(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 500,
        ]);

        $history = $this->service->deductCredits($operator, 100);

        $this->assertInstanceOf(SmsBalanceHistory::class, $history);
        $this->assertEquals(500, $history->balance_before);
        $this->assertEquals(400, $history->balance_after);
        $this->assertEquals(-100, $history->amount);
        $this->assertEquals('usage', $history->transaction_type);
        
        $operator->refresh();
        $this->assertEquals(400, $operator->sms_balance);
    }

    /**
     * Test deducting credits throws exception when insufficient balance
     */
    public function test_deduct_credits_insufficient_balance(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 50,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient SMS balance');

        $this->service->deductCredits($operator, 100);
    }

    /**
     * Test adjusting operator SMS balance
     */
    public function test_adjust_balance_positive(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 100,
        ]);

        $history = $this->service->adjustBalance($operator, 50, 'Admin correction');

        $this->assertEquals(100, $history->balance_before);
        $this->assertEquals(150, $history->balance_after);
        $this->assertEquals(50, $history->amount);
        $this->assertEquals('adjustment', $history->transaction_type);
        $this->assertEquals('Admin correction', $history->notes);
        
        $operator->refresh();
        $this->assertEquals(150, $operator->sms_balance);
    }

    /**
     * Test adjusting operator SMS balance negatively
     */
    public function test_adjust_balance_negative(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 100,
        ]);

        $history = $this->service->adjustBalance($operator, -30, 'Admin deduction');

        $this->assertEquals(100, $history->balance_before);
        $this->assertEquals(70, $history->balance_after);
        $this->assertEquals(-30, $history->amount);
        
        $operator->refresh();
        $this->assertEquals(70, $operator->sms_balance);
    }

    /**
     * Test adjustment throws exception if it would result in negative balance
     */
    public function test_adjust_balance_negative_result_throws_exception(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 50,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Adjustment would result in negative balance');

        $this->service->adjustBalance($operator, -100);
    }

    /**
     * Test getting SMS balance history
     */
    public function test_get_history(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 100,
        ]);

        // Create some history
        $this->service->addCredits($operator, 100);
        $this->service->addCredits($operator, 200);
        $this->service->deductCredits($operator, 50);

        $history = $this->service->getHistory($operator, 10);

        $this->assertCount(3, $history);
        $this->assertEquals('usage', $history[0]->transaction_type);
        $this->assertEquals('purchase', $history[1]->transaction_type);
        $this->assertEquals('purchase', $history[2]->transaction_type);
    }

    /**
     * Test getting usage statistics
     */
    public function test_get_usage_stats(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 1000,
        ]);

        // Add and deduct credits
        $this->service->addCredits($operator, 1000);
        $this->service->deductCredits($operator, 100);
        $this->service->deductCredits($operator, 150);

        $stats = $this->service->getUsageStats($operator, 'month');

        $this->assertEquals('month', $stats['period']);
        $this->assertEquals(250, $stats['total_used']); // 100 + 150
        $this->assertEquals(2, $stats['transaction_count']);
        $this->assertEquals(1750, $stats['current_balance']); // 1000 + 1000 - 100 - 150
    }

    /**
     * Test low balance notification check
     */
    public function test_check_and_notify_low_balance(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 50,
            'sms_low_balance_threshold' => 100,
            'sms_low_balance_notified_at' => null,
        ]);

        $result = $this->service->checkAndNotifyLowBalance($operator);

        $this->assertTrue($result);
        $operator->refresh();
        $this->assertNotNull($operator->sms_low_balance_notified_at);
    }

    /**
     * Test no notification if balance is not low
     */
    public function test_no_notification_if_balance_not_low(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 500,
            'sms_low_balance_threshold' => 100,
        ]);

        $result = $this->service->checkAndNotifyLowBalance($operator);

        $this->assertFalse($result);
    }

    /**
     * Test no duplicate notification within 24 hours
     */
    public function test_no_duplicate_notification_within_24_hours(): void
    {
        $operator = User::factory()->create([
            'operator_level' => User::OPERATOR_LEVEL_OPERATOR,
            'sms_balance' => 50,
            'sms_low_balance_threshold' => 100,
            'sms_low_balance_notified_at' => now()->subHours(12),
        ]);

        $result = $this->service->checkAndNotifyLowBalance($operator);

        $this->assertFalse($result);
    }
}
