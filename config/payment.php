<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | when initiating payments.
    |
    */
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'bkash'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for various payment gateways supported by the application.
    | Actual credentials should be stored in the database per tenant.
    |
    */
    'gateways' => [
        'bkash' => [
            'name' => 'bKash',
            'enabled' => env('BKASH_ENABLED', false),
            'test_mode' => env('BKASH_TEST_MODE', true),
        ],

        'nagad' => [
            'name' => 'Nagad',
            'enabled' => env('NAGAD_ENABLED', false),
            'test_mode' => env('NAGAD_TEST_MODE', true),
        ],

        'sslcommerz' => [
            'name' => 'SSLCommerz',
            'enabled' => env('SSLCOMMERZ_ENABLED', false),
            'test_mode' => env('SSLCOMMERZ_TEST_MODE', true),
        ],

        'stripe' => [
            'name' => 'Stripe',
            'enabled' => env('STRIPE_ENABLED', false),
            'test_mode' => env('STRIPE_TEST_MODE', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Manual Payment Methods
    |--------------------------------------------------------------------------
    |
    | Manual payment methods that can be used for offline payments.
    |
    */
    'manual_methods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
    ],
];
