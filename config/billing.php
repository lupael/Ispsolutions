<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | The default tax rate (VAT/GST) to apply to invoices.
    | This can be overridden per tenant.
    |
    */
    'tax_rate' => env('BILLING_TAX_RATE', 0), // Percentage (e.g., 15 for 15%)

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after due date before account is locked.
    |
    */
    'grace_period_days' => env('BILLING_GRACE_PERIOD', 7),

    /*
    |--------------------------------------------------------------------------
    | Invoice Number Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix to use for invoice numbers.
    |
    */
    'invoice_prefix' => env('BILLING_INVOICE_PREFIX', 'INV'),

    /*
    |--------------------------------------------------------------------------
    | Payment Number Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix to use for payment numbers.
    |
    */
    'payment_prefix' => env('BILLING_PAYMENT_PREFIX', 'PAY'),

    /*
    |--------------------------------------------------------------------------
    | Billing Types
    |--------------------------------------------------------------------------
    |
    | Supported billing types in the system.
    |
    */
    'billing_types' => [
        'daily' => 'Daily',
        'monthly' => 'Monthly',
        'onetime' => 'One Time',
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Billing Base Days
    |--------------------------------------------------------------------------
    |
    | Number of days to use as base when calculating daily rate from monthly price.
    |
    */
    'daily_billing_base_days' => env('BILLING_DAILY_BASE_DAYS', 30),
];
