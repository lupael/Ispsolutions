<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache TTL Configuration
    |--------------------------------------------------------------------------
    |
    | Define cache time-to-live (TTL) for various data types in seconds.
    | This allows centralized cache duration management.
    |
    */

    'ttl' => [
        // Dashboard statistics (5 minutes)
        'dashboard_stats' => 120,

        // Package listings (15 minutes)
        'packages' => 300,

        // Payment gateway configurations (30 minutes)
        'payment_gateways' => 300,

        // User role permissions (60 minutes)
        'role_permissions' => 300,

        // Network device status (2 minutes)
        'device_status' => 60,

        // Tenant data (10 minutes)
        'tenant_data' => 300,

        // Network statistics (5 minutes)
        'network_stats' => 120,

        // Billing summaries (10 minutes)
        'billing_summary' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes for cache keys to organize cached data and enable
    | pattern-based cache invalidation.
    |
    */

    'prefixes' => [
        'tenant' => 'tenant',
        'user' => 'user',
        'dashboard' => 'dashboard',
        'package' => 'package',
        'gateway' => 'gateway',
        'permission' => 'permission',
        'device' => 'device',
        'stats' => 'stats',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tagging
    |--------------------------------------------------------------------------
    |
    | Enable cache tagging for better cache organization.
    | Note: Tagging requires Redis or Memcached driver.
    |
    */

    'enable_tagging' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Define cache tags for grouping related cache entries.
    |
    */

    'tags' => [
        'tenants' => 'tenants',
        'users' => 'users',
        'packages' => 'packages',
        'gateways' => 'gateways',
        'permissions' => 'permissions',
        'devices' => 'devices',
        'invoices' => 'invoices',
        'payments' => 'payments',
    ],
];
