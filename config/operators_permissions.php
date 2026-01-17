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
        'developer' => 0,
        'super_admin' => 10,
        'group_admin' => 20,
        'operator' => 30,
        'sub_operator' => 40,
        'manager' => 50,
        'card_distributor' => 60,
        'accountant' => 70,
        'staff' => 80,
        'customer' => 100,
    ],
];
