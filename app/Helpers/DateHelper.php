<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Date Formatting Helper
 * 
 * Provides utility functions for consistent date formatting across the application
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Quick Win #3 (Date Formatting Enhancement)
 */
class DateHelper
{
    /**
     * Get ordinal suffix for a number (1st, 2nd, 3rd, 21st, etc.)
     * 
     * @param int $number The number to get the suffix for
     * @return string The ordinal suffix (st, nd, rd, or th)
     */
    public static function getOrdinalSuffix(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        // Handle special cases (11th, 12th, 13th)
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return 'th';
        }
        
        return match ($lastDigit) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    /**
     * Format a number with ordinal suffix (1 -> "1st", 21 -> "21st")
     * 
     * @param int $number The number to format
     * @return string The number with ordinal suffix
     */
    public static function ordinal(int $number): string
    {
        return $number . self::getOrdinalSuffix($number);
    }

    /**
     * Format a day of the month with ordinal suffix (21 -> "21st day")
     * 
     * @param int $day The day of the month (1-31)
     * @return string The formatted day with ordinal suffix
     */
    public static function dayWithOrdinal(int $day): string
    {
        return self::ordinal($day) . ' day';
    }

    /**
     * Format a billing day with full text (21 -> "21st day of each month")
     * 
     * @param int $day The day of the month (1-31)
     * @return string The formatted billing day text
     */
    public static function billingDayText(int $day): string
    {
        return self::dayWithOrdinal($day) . ' of each month';
    }

    /**
     * Get relative time until a date ("Expires in 5 days", "Expired 3 days ago")
     * 
     * @param Carbon|string $date The date to calculate from
     * @param bool $short Whether to use short format (5d vs 5 days)
     * @return string The relative time text
     */
    public static function relativeTime(Carbon|string $date, bool $short = false): string
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $now = Carbon::now();
        $diffInDays = (int) round($now->diffInDays($date, false));

        // Handle dates in the past
        if ($diffInDays < 0) {
            $absDays = abs($diffInDays);
            if ($absDays === 0) {
                return 'Today';
            } elseif ($absDays === 1) {
                return 'Yesterday';
            } else {
                return $short 
                    ? "{$absDays}d ago" 
                    : "Expired {$absDays} " . ($absDays === 1 ? 'day' : 'days') . " ago";
            }
        }

        // Handle dates in the future
        if ($diffInDays === 0) {
            return 'Today';
        } elseif ($diffInDays === 1) {
            return 'Tomorrow';
        } else {
            return $short 
                ? "in {$diffInDays}d" 
                : "In {$diffInDays} " . ($diffInDays === 1 ? 'day' : 'days');
        }
    }

    /**
     * Get expiry status text with relative time ("Expires in 5 days")
     * 
     * @param Carbon|string|null $expiryDate The expiry date
     * @param bool $short Whether to use short format
     * @return string The expiry status text
     */
    public static function expiryText(Carbon|string|null $expiryDate, bool $short = false): string
    {
        if (!$expiryDate) {
            return 'No expiry';
        }

        if (!$expiryDate instanceof Carbon) {
            $expiryDate = Carbon::parse($expiryDate);
        }

        $now = Carbon::now();
        $diffInDays = (int) round($now->diffInDays($expiryDate, false));

        if ($diffInDays < 0) {
            $absDays = abs($diffInDays);
            return $short 
                ? "Expired {$absDays}d ago" 
                : "Expired {$absDays} " . ($absDays === 1 ? 'day' : 'days') . " ago";
        } elseif ($diffInDays === 0) {
            return 'Expires today';
        } elseif ($diffInDays === 1) {
            return 'Expires tomorrow';
        } else {
            return $short 
                ? "Exp. in {$diffInDays}d" 
                : "Expires in {$diffInDays} " . ($diffInDays === 1 ? 'day' : 'days');
        }
    }

    /**
     * Format a date in a human-friendly format
     * 
     * @param Carbon|string|null $date The date to format
     * @param string $format The format to use (default: 'M d, Y')
     * @param string|null $timezone The timezone to use (default: null)
     * @return string The formatted date
     */
    public static function format(Carbon|string|null $date, string $format = 'M d, Y', ?string $timezone = null): string
    {
        if (!$date) {
            return 'N/A';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        if ($timezone) {
            $date = $date->timezone($timezone);
        }

        return $date->format($format);
    }

    /**
     * Get grace period display text ("5 days grace period")
     * 
     * @param int $days The number of grace period days
     * @return string The grace period text
     */
    public static function gracePeriodText(int $days): string
    {
        if ($days === 0) {
            return 'No grace period';
        } elseif ($days === 1) {
            return '1 day grace period';
        } else {
            return "{$days} days grace period";
        }
    }

    /**
     * Get urgency color based on days remaining
     * 
     * @param int $daysRemaining The number of days remaining
     * @return string The color class (red, orange, yellow, green)
     */
    public static function urgencyColor(int $daysRemaining): string
    {
        if ($daysRemaining < 0) {
            return 'red';
        } elseif ($daysRemaining === 0) {
            return 'red';
        } elseif ($daysRemaining <= 3) {
            return 'orange';
        } elseif ($daysRemaining <= 7) {
            return 'yellow';
        } else {
            return 'green';
        }
    }

    /**
     * Format duration in seconds to human-readable format (3h 25m 10s)
     * 
     * @param int $seconds The duration in seconds
     * @param bool $short Whether to use short format (3h vs 3 hours)
     * @return string The formatted duration
     */
    public static function duration(int $seconds, bool $short = false): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = (int) ($seconds % 60);

        $parts = [];
        
        if ($hours > 0) {
            $parts[] = $short ? "{$hours}h" : "{$hours} " . ($hours === 1 ? 'hour' : 'hours');
        }
        
        if ($minutes > 0) {
            $parts[] = $short ? "{$minutes}m" : "{$minutes} " . ($minutes === 1 ? 'minute' : 'minutes');
        }
        
        if ($secs > 0 || empty($parts)) {
            $parts[] = $short ? "{$secs}s" : "{$secs} " . ($secs === 1 ? 'second' : 'seconds');
        }

        return implode(' ', $parts);
    }
}
