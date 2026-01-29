<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Test Date Helper Functions
 * 
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #3 (Date Formatting Enhancement)
 */
class DateHelperTest extends TestCase
{
    /**
     * Test ordinal suffix generation
     */
    public function test_get_ordinal_suffix(): void
    {
        $this->assertEquals('st', DateHelper::getOrdinalSuffix(1));
        $this->assertEquals('nd', DateHelper::getOrdinalSuffix(2));
        $this->assertEquals('rd', DateHelper::getOrdinalSuffix(3));
        $this->assertEquals('th', DateHelper::getOrdinalSuffix(4));
        $this->assertEquals('th', DateHelper::getOrdinalSuffix(11));
        $this->assertEquals('th', DateHelper::getOrdinalSuffix(12));
        $this->assertEquals('th', DateHelper::getOrdinalSuffix(13));
        $this->assertEquals('st', DateHelper::getOrdinalSuffix(21));
        $this->assertEquals('nd', DateHelper::getOrdinalSuffix(22));
        $this->assertEquals('rd', DateHelper::getOrdinalSuffix(23));
        $this->assertEquals('th', DateHelper::getOrdinalSuffix(24));
        $this->assertEquals('st', DateHelper::getOrdinalSuffix(31));
    }

    /**
     * Test ordinal number formatting
     */
    public function test_ordinal(): void
    {
        $this->assertEquals('1st', DateHelper::ordinal(1));
        $this->assertEquals('2nd', DateHelper::ordinal(2));
        $this->assertEquals('3rd', DateHelper::ordinal(3));
        $this->assertEquals('4th', DateHelper::ordinal(4));
        $this->assertEquals('11th', DateHelper::ordinal(11));
        $this->assertEquals('12th', DateHelper::ordinal(12));
        $this->assertEquals('13th', DateHelper::ordinal(13));
        $this->assertEquals('21st', DateHelper::ordinal(21));
        $this->assertEquals('22nd', DateHelper::ordinal(22));
        $this->assertEquals('23rd', DateHelper::ordinal(23));
        $this->assertEquals('31st', DateHelper::ordinal(31));
    }

    /**
     * Test day with ordinal formatting
     */
    public function test_day_with_ordinal(): void
    {
        $this->assertEquals('1st day', DateHelper::dayWithOrdinal(1));
        $this->assertEquals('21st day', DateHelper::dayWithOrdinal(21));
        $this->assertEquals('11th day', DateHelper::dayWithOrdinal(11));
    }

    /**
     * Test billing day text formatting
     */
    public function test_billing_day_text(): void
    {
        $this->assertEquals('1st day of each month', DateHelper::billingDayText(1));
        $this->assertEquals('21st day of each month', DateHelper::billingDayText(21));
        $this->assertEquals('15th day of each month', DateHelper::billingDayText(15));
    }

    /**
     * Test relative time for future dates
     */
    public function test_relative_time_future(): void
    {
        $today = Carbon::now();
        $tomorrow = $today->copy()->addDay();
        $in5Days = $today->copy()->addDays(5);

        $this->assertEquals('Today', DateHelper::relativeTime($today));
        $this->assertEquals('Tomorrow', DateHelper::relativeTime($tomorrow));
        $this->assertEquals('In 5 days', DateHelper::relativeTime($in5Days));
    }

    /**
     * Test relative time for past dates
     */
    public function test_relative_time_past(): void
    {
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();
        $threeDaysAgo = $today->copy()->subDays(3);

        $this->assertEquals('Yesterday', DateHelper::relativeTime($yesterday));
        $this->assertEquals('Expired 3 days ago', DateHelper::relativeTime($threeDaysAgo));
    }

    /**
     * Test relative time with short format
     */
    public function test_relative_time_short_format(): void
    {
        $today = Carbon::now();
        $in5Days = $today->copy()->addDays(5);
        $threeDaysAgo = $today->copy()->subDays(3);

        $this->assertEquals('in 5d', DateHelper::relativeTime($in5Days, true));
        $this->assertEquals('3d ago', DateHelper::relativeTime($threeDaysAgo, true));
    }

    /**
     * Test expiry text for future dates
     */
    public function test_expiry_text_future(): void
    {
        $today = Carbon::now();
        $tomorrow = $today->copy()->addDay();
        $in5Days = $today->copy()->addDays(5);

        $this->assertEquals('Expires today', DateHelper::expiryText($today));
        $this->assertEquals('Expires tomorrow', DateHelper::expiryText($tomorrow));
        $this->assertEquals('Expires in 5 days', DateHelper::expiryText($in5Days));
    }

    /**
     * Test expiry text for past dates
     */
    public function test_expiry_text_past(): void
    {
        $today = Carbon::now();
        $threeDaysAgo = $today->copy()->subDays(3);

        $this->assertEquals('Expired 3 days ago', DateHelper::expiryText($threeDaysAgo));
    }

    /**
     * Test expiry text with null date
     */
    public function test_expiry_text_null(): void
    {
        $this->assertEquals('No expiry', DateHelper::expiryText(null));
    }

    /**
     * Test expiry text with short format
     */
    public function test_expiry_text_short_format(): void
    {
        $today = Carbon::now();
        $in5Days = $today->copy()->addDays(5);
        $threeDaysAgo = $today->copy()->subDays(3);

        $this->assertEquals('Exp. in 5d', DateHelper::expiryText($in5Days, true));
        $this->assertEquals('Expired 3d ago', DateHelper::expiryText($threeDaysAgo, true));
    }

    /**
     * Test date formatting
     */
    public function test_format(): void
    {
        $date = Carbon::parse('2024-01-21 14:30:00');

        $this->assertEquals('Jan 21, 2024', DateHelper::format($date));
        $this->assertEquals('2024-01-21', DateHelper::format($date, 'Y-m-d'));
        $this->assertEquals('N/A', DateHelper::format(null));
    }

    /**
     * Test grace period text
     */
    public function test_grace_period_text(): void
    {
        $this->assertEquals('No grace period', DateHelper::gracePeriodText(0));
        $this->assertEquals('1 day grace period', DateHelper::gracePeriodText(1));
        $this->assertEquals('5 days grace period', DateHelper::gracePeriodText(5));
    }

    /**
     * Test urgency color
     */
    public function test_urgency_color(): void
    {
        $this->assertEquals('red', DateHelper::urgencyColor(-1));
        $this->assertEquals('red', DateHelper::urgencyColor(0));
        $this->assertEquals('orange', DateHelper::urgencyColor(3));
        $this->assertEquals('yellow', DateHelper::urgencyColor(7));
        $this->assertEquals('green', DateHelper::urgencyColor(30));
    }

    /**
     * Test duration formatting
     */
    public function test_duration(): void
    {
        $this->assertEquals('1 hour 5 minutes 30 seconds', DateHelper::duration(3930));
        $this->assertEquals('2 hours', DateHelper::duration(7200));
        $this->assertEquals('30 seconds', DateHelper::duration(30));
    }

    /**
     * Test duration formatting with short format
     */
    public function test_duration_short_format(): void
    {
        $this->assertEquals('1h 5m 30s', DateHelper::duration(3930, true));
        $this->assertEquals('2h', DateHelper::duration(7200, true));
        $this->assertEquals('30s', DateHelper::duration(30, true));
    }

    /**
     * Test global helper functions
     */
    public function test_global_helper_functions(): void
    {
        $this->assertEquals('1st', ordinal(1));
        $this->assertEquals('21st day', dayWithOrdinal(21));
        $this->assertEquals('15th day of each month', billingDayText(15));
        $this->assertEquals('5 days grace period', gracePeriodText(5));
        $this->assertEquals('1h 30m', durationText(5400, true));
        
        $today = Carbon::now();
        $in5Days = $today->copy()->addDays(5);
        
        $this->assertEquals('In 5 days', relativeTime($in5Days));
        $this->assertEquals('Expires in 5 days', expiryText($in5Days));
    }
}
