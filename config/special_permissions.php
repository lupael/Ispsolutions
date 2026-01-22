<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Special Permissions
    |--------------------------------------------------------------------------
    |
    | Special permissions that can be granted to specific operators
    | These are typically assigned by Admin to customize operator access
    |
    */

    'access_all_customers' => [
        'label' => 'Access All Customers',
        'description' => 'Can view and manage customers across all zones/areas',
        'default' => false,
    ],

    'bypass_credit_limit' => [
        'label' => 'Bypass Credit Limit',
        'description' => 'Can process payments exceeding customer credit limits',
        'default' => false,
    ],

    'manual_discount' => [
        'label' => 'Apply Manual Discounts',
        'description' => 'Can apply manual discounts to bills and invoices',
        'default' => false,
    ],

    'delete_transactions' => [
        'label' => 'Delete Transactions',
        'description' => 'Can delete payment transactions (dangerous)',
        'default' => false,
    ],

    'modify_billing_cycle' => [
        'label' => 'Modify Billing Cycle',
        'description' => 'Can change customer billing cycles',
        'default' => false,
    ],

    'access_logs' => [
        'label' => 'Access System Logs',
        'description' => 'Can view system and audit logs',
        'default' => false,
    ],

    'bulk_operations' => [
        'label' => 'Bulk Operations',
        'description' => 'Can perform bulk operations (suspend, activate, bill generate)',
        'default' => false,
    ],

    'router_config_access' => [
        'label' => 'Router Configuration Access',
        'description' => 'Can access and modify router configurations',
        'default' => false,
    ],

    'override_package_pricing' => [
        'label' => 'Override Package Pricing',
        'description' => 'Can set custom pricing for individual customers',
        'default' => false,
    ],

    'view_sensitive_data' => [
        'label' => 'View Sensitive Data',
        'description' => 'Can view sensitive customer data (full credit card, passwords)',
        'default' => false,
    ],

    'export_all_data' => [
        'label' => 'Export All Data',
        'description' => 'Can export complete database dumps and customer data',
        'default' => false,
    ],

    'manage_operators' => [
        'label' => 'Manage Operators',
        'description' => 'Can create and manage operator accounts',
        'default' => false,
    ],

    // Backward compatibility alias
    'manage_resellers' => [
        'label' => 'Manage Operators',
        'description' => 'Can create and manage operator accounts (legacy alias)',
        'default' => false,
    ],
];
