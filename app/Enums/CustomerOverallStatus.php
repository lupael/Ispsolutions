<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Customer Overall Status Enum
 * 
 * Task 3.1: Create CustomerOverallStatus enum
 * Combines payment type and service status for easier filtering
 */
enum CustomerOverallStatus: string
{
    case PREPAID_ACTIVE = 'prepaid_active';
    case PREPAID_SUSPENDED = 'prepaid_suspended';
    case PREPAID_EXPIRED = 'prepaid_expired';
    case PREPAID_INACTIVE = 'prepaid_inactive';
    case POSTPAID_ACTIVE = 'postpaid_active';
    case POSTPAID_SUSPENDED = 'postpaid_suspended';
    case POSTPAID_EXPIRED = 'postpaid_expired';
    case POSTPAID_INACTIVE = 'postpaid_inactive';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::PREPAID_ACTIVE => 'Prepaid & Active',
            self::PREPAID_SUSPENDED => 'Prepaid & Suspended',
            self::PREPAID_EXPIRED => 'Prepaid & Expired',
            self::PREPAID_INACTIVE => 'Prepaid & Inactive',
            self::POSTPAID_ACTIVE => 'Postpaid & Active',
            self::POSTPAID_SUSPENDED => 'Postpaid & Suspended',
            self::POSTPAID_EXPIRED => 'Postpaid & Expired',
            self::POSTPAID_INACTIVE => 'Postpaid & Inactive',
        };
    }

    /**
     * Get badge color for UI
     * Task 3.5: Add color coding for overall status in UI
     */
    public function color(): string
    {
        return match ($this) {
            self::PREPAID_ACTIVE => 'green',
            self::POSTPAID_ACTIVE => 'blue',
            self::PREPAID_SUSPENDED, self::POSTPAID_SUSPENDED => 'orange',
            self::PREPAID_EXPIRED, self::POSTPAID_EXPIRED => 'red',
            self::PREPAID_INACTIVE, self::POSTPAID_INACTIVE => 'gray',
        };
    }

    /**
     * Get icon for UI
     */
    public function icon(): string
    {
        return match ($this) {
            self::PREPAID_ACTIVE, self::POSTPAID_ACTIVE => 'check-circle',
            self::PREPAID_SUSPENDED, self::POSTPAID_SUSPENDED => 'pause-circle',
            self::PREPAID_EXPIRED, self::POSTPAID_EXPIRED => 'x-circle',
            self::PREPAID_INACTIVE, self::POSTPAID_INACTIVE => 'minus-circle',
        };
    }
}
