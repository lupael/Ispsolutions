<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Customer Model
 *
 * Represents external subscribers (Internet/PPP/Hotspot/CableTV).
 * Customers are NOT part of the administrative hierarchy (Levels 0-80).
 * They are identified by the is_subscriber flag, not by operator_level.
 *
 * This model represents customers as assets/subscribers managed by
 * Operators (Level 30) and Sub-Operators (Level 40).
 */
class Customer extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        parent::booted();

        // Automatically scope to subscribers (customers)
        static::addGlobalScope('customer', function (\Illuminate\Database\Eloquent\Builder $query) {
            $query->where('is_subscriber', true);
        });

        // Set default is_subscriber when creating a new customer
        static::creating(function ($customer) {
            $customer->is_subscriber = true;

            // For backward compatibility with logic that might still check for level 100,
            // we set it on creation. The `updating` hook will enforce null later to
            // prevent accidental changes to other operator levels.
            $customer->operator_level = self::OPERATOR_LEVEL_CUSTOMER;

            // Auto-generate customer_id if not set
            if (empty($customer->customer_id)) {
                $customer->customer_id = static::generateCustomerId();
            }
        });

        // Also enforce on update to prevent accidental corruption
        static::updating(function ($customer) {
            // Always enforce null operator_level for customers
            // This prevents a customer from being accidentally converted to an operator.
            $customer->operator_level = null;
        });
    }

    /**
     * Generate a unique 5-6 digit customer ID.
     *
     * @return string
     */
    protected static function generateCustomerId(): string
    {
        do {
            // Generate a random 5-6 digit number
            $customerId = str_pad((string) random_int(10000, 999999), 6, '0', STR_PAD_LEFT);

            // Check if it already exists (check in parent User table)
            $exists = \App\Models\User::withoutGlobalScope('customer')->where('customer_id', $customerId)->exists();
        } while ($exists);

        return $customerId;
    }
}
