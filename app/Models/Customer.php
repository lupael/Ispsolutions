<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Customer Model
 *
 * Type alias for User model when representing customers.
 * In this system, customers are Users with operator_level = 100.
 *
 * This class exists to improve code readability and make the intent
 * clearer when working with customer-specific operations.
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

        // Automatically scope to customer-level users
        static::addGlobalScope('customer', function ($query) {
            $query->where('operator_level', 100);
        });
        
        // Set default operator_level when creating a new customer
        static::creating(function ($customer) {
            if (!isset($customer->operator_level)) {
                $customer->operator_level = 100;
            }
        });
    }

    /**
     * Check if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->operator_level === 100;
    }
}
