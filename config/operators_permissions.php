<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operator Permission Definitions
    |--------------------------------------------------------------------------
    |
    | This file defines all available operator permissions in the system.
    | Permissions are organized by module/feature area.
    |
    */

    'customers' => [
        'view_customers' => 'View customer list',
        'create_customers' => 'Create new customers',
        'edit_customers' => 'Edit customer details',
        'delete_customers' => 'Delete customers',
        'suspend_customers' => 'Suspend customer accounts',
        'activate_customers' => 'Activate customer accounts',
    ],

    'billing' => [
        'view_bills' => 'View bills',
        'create_bills' => 'Create bills',
        'edit_bills' => 'Edit bills',
        'delete_bills' => 'Delete bills',
        'process_payments' => 'Process customer payments',
        'refund_payments' => 'Refund payments',
        'view_invoices' => 'View invoices',
        'generate_invoices' => 'Generate invoices',
    ],

    'packages' => [
        'view_packages' => 'View service packages',
        'create_packages' => 'Create service packages',
        'edit_packages' => 'Edit service packages',
        'delete_packages' => 'Delete service packages',
        'assign_packages' => 'Assign packages to customers',
    ],

    'network' => [
        'view_routers' => 'View routers',
        'create_routers' => 'Create routers',
        'edit_routers' => 'Edit routers',
        'delete_routers' => 'Delete routers',
        'manage_ip_pools' => 'Manage IP pools',
        'view_sessions' => 'View active sessions',
        'disconnect_sessions' => 'Disconnect user sessions',
    ],

    'cards' => [
        'view_cards' => 'View recharge cards',
        'create_cards' => 'Create recharge cards',
        'distribute_cards' => 'Distribute cards to distributors',
        'view_card_sales' => 'View card sales',
    ],

    'operators' => [
        'view_operators' => 'View operators',
        'create_operators' => 'Create operators',
        'edit_operators' => 'Edit operators',
        'delete_operators' => 'Delete operators',
        'manage_permissions' => 'Manage operator permissions',
    ],

    'reports' => [
        'view_financial_reports' => 'View financial reports',
        'view_customer_reports' => 'View customer reports',
        'view_network_reports' => 'View network reports',
        'export_reports' => 'Export reports',
    ],

    'settings' => [
        'view_settings' => 'View system settings',
        'edit_settings' => 'Edit system settings',
        'manage_payment_gateways' => 'Manage payment gateways',
        'manage_sms_gateways' => 'Manage SMS gateways',
    ],

    /*
    |--------------------------------------------------------------------------
    | Operator Levels
    |--------------------------------------------------------------------------
    |
    | Define operator levels for hierarchical access control.
    | Lower numbers = higher privileges.
    |
    */

    'levels' => [
        'developer' => 0,        // Technical infrastructure and API
        'super_admin' => 10,     // System-wide administrator
        'group_admin' => 20,     // Tenant administrator (ISP Admin)
        'operator' => 30,        // Operational staff with configurable menus
        'sub_operator' => 40,    // Limited operator (subset of operator)
        'manager' => 50,         // Task-specific access
        'card_distributor' => 60, // Card operations only
        'reseller' => 65,        // Customer management and sales
        'accountant' => 70,      // Financial reporting (read-only)
        'sub_reseller' => 75,    // Subordinate to reseller
        'staff' => 80,           // Support staff
        'customer' => 100,       // End user (lowest privilege)
    ],

    /*
    |--------------------------------------------------------------------------
    | Controllable Menus for Operators
    |--------------------------------------------------------------------------
    |
    | These menus can be disabled per operator by Admin users.
    | The menu keys correspond to the 'key' field in config/sidebars.php
    |
    */

    'controllable_menus' => [
        'resellers_managers' => 'Resellers & Managers menu',
        'routers_packages' => 'Routers & Packages menu',
        'recharge_cards' => 'Recharge Card menu',
        'customers' => 'Customer menu',
        'bills_payments' => 'Bills & Payments menu',
        'incomes_expenses' => 'Incomes & Expenses menu',
        'affiliate_program' => 'Affiliate Program menu',
        'vat_management' => 'VAT menu',
    ],
];
