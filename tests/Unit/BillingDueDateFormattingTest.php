<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\BillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Billing due date formatting
 * Tests that billing due date displays with ordinal suffix
 */
class BillingDueDateFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_due_date_has_ordinal_suffix_for_first(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 1,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('1st', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_second(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 2,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('2nd', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_third(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 3,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('3rd', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_fourth(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 4,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('4th', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_twentyfirst(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 21,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('21st', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_twentysecond(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 22,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('22nd', $profile->getDueDateWithOrdinal());
    }

    public function test_billing_due_date_has_ordinal_suffix_for_twentythird(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 23,
            'type' => 'monthly',
        ]);

        $this->assertStringContainsString('23rd', $profile->getDueDateWithOrdinal());
    }

    public function test_due_date_figure_includes_ordinal_for_monthly(): void
    {
        $profile = BillingProfile::factory()->create([
            'billing_day' => 15,
            'type' => 'monthly',
        ]);

        $figure = $profile->due_date_figure;
        
        $this->assertStringContainsString('15th', $figure);
        $this->assertStringContainsString('of each month', $figure);
    }

    public function test_due_date_figure_for_daily_billing(): void
    {
        $profile = BillingProfile::factory()->create([
            'type' => 'daily',
            'billing_time' => '09:00',
        ]);

        $figure = $profile->due_date_figure;
        
        $this->assertStringContainsString('Daily', $figure);
        $this->assertStringContainsString('09:00', $figure);
    }

    public function test_due_date_figure_for_free_billing(): void
    {
        $profile = BillingProfile::factory()->create([
            'type' => 'free',
        ]);

        $figure = $profile->due_date_figure;
        
        $this->assertEquals('No billing', $figure);
    }
}
