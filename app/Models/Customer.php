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
        static::addGlobalScope('customer', function ($query) {
            $query->where('is_subscriber', true);
        });
        
        // Set default is_subscriber when creating a new customer
        static::creating(function ($customer) {
            // Customers are subscribers, not operators
            if (!isset($customer->is_subscriber)) {
                $customer->is_subscriber = true;
            }
            
            // Customers should not have operator_level (or handle migration period)
            if ($customer->operator_level === null || $customer->operator_level === 100) {
                $customer->operator_level = null;
            }
            
            // Auto-generate customer_id if not set
            if (empty($customer->customer_id)) {
                $customer->customer_id = static::generateCustomerId();
            }
        });
    }

    /**
     * Generate a unique 5-6 digit customer ID.
     */
    private static function generateCustomerId(): string
    {
        do {
            // Generate a random 5-6 digit number
            $customerId = str_pad((string) random_int(10000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Check if it already exists (check in parent User table)
            $exists = \App\Models\User::withoutGlobalScope('customer')->where('customer_id', $customerId)->exists();
        } while ($exists);

        return $customerId;
    }

    /**
     * Check if the user is a customer/subscriber.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->is_subscriber === true;
    }
}
