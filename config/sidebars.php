<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines sidebar menus for different operator types.
    | Each menu item can have permissions, disabled checks, and submenus.
    |
    */

    'super_admin' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.super-admin.dashboard',
        ],
        [
            'key' => 'tenants',
            'label' => 'ISP/Tenants',
            'icon' => 'bi-building',
            'route' => 'panel.super-admin.isp.index',
        ],
        [
            'key' => 'users',
            'label' => 'Users',
            'icon' => 'bi-people',
            'route' => 'panel.super-admin.users',
        ],
        [
            'key' => 'roles',
            'label' => 'Roles & Permissions',
            'icon' => 'bi-shield-lock',
            'route' => 'panel.super-admin.roles',
        ],
        [
            'key' => 'billing',
            'label' => 'Billing Config',
            'icon' => 'bi-cash-coin',
            'children' => [
                ['label' => 'Fixed Billing', 'route' => 'panel.super-admin.billing.fixed'],
                ['label' => 'User-Based', 'route' => 'panel.super-admin.billing.user-base'],
                ['label' => 'Panel-Based', 'route' => 'panel.super-admin.billing.panel-base'],
            ],
        ],
        [
            'key' => 'payment_gateways',
            'label' => 'Payment Gateways',
            'icon' => 'bi-credit-card',
            'route' => 'panel.super-admin.payment-gateway.index',
        ],
        [
            'key' => 'sms_gateways',
            'label' => 'SMS Gateways',
            'icon' => 'bi-chat-dots',
            'route' => 'panel.super-admin.sms-gateway.index',
        ],
        [
            'key' => 'settings',
            'label' => 'Settings',
            'icon' => 'bi-gear',
            'route' => 'panel.super-admin.settings',
        ],
    ],

    'group_admin' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.admin.dashboard',
        ],
        [
            'key' => 'operators',
            'label' => 'Operators',
            'icon' => 'bi-person-badge',
            'permission' => 'view_operators',
            'children' => [
                ['label' => 'All Operators', 'route' => 'panel.admin.operators.index'],
                ['label' => 'Create Operator', 'route' => 'panel.admin.operators.create'],
                ['label' => 'Staff', 'route' => 'panel.admin.operators.staff'],
            ],
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'permission' => 'view_customers',
            'route' => 'panel.admin.customers.index',
        ],
        [
            'key' => 'packages',
            'label' => 'Packages',
            'icon' => 'bi-box',
            'permission' => 'view_packages',
            'route' => 'panel.admin.packages.index',
        ],
        [
            'key' => 'billing',
            'label' => 'Billing',
            'icon' => 'bi-receipt',
            'permission' => 'view_bills',
            'children' => [
                ['label' => 'Bills', 'route' => 'panel.admin.bills.index'],
                ['label' => 'Invoices', 'route' => 'panel.admin.invoices.index'],
                ['label' => 'Payments', 'route' => 'panel.admin.payments.index'],
            ],
        ],
        [
            'key' => 'network',
            'label' => 'Network',
            'icon' => 'bi-hdd-network',
            'permission' => 'view_routers',
            'children' => [
                ['label' => 'Routers', 'route' => 'panel.admin.routers.index'],
                ['label' => 'IP Pools', 'route' => 'panel.admin.ip-pools.index'],
                ['label' => 'Sessions', 'route' => 'panel.admin.sessions.index'],
            ],
        ],
        [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'bi-graph-up',
            'permission' => 'view_financial_reports',
            'route' => 'panel.admin.reports.index',
        ],
    ],

    'operator' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.operator.dashboard',
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'permission' => 'view_customers',
            'route' => 'panel.operator.customers.index',
        ],
        [
            'key' => 'billing',
            'label' => 'Billing',
            'icon' => 'bi-receipt',
            'permission' => 'view_bills',
            'children' => [
                ['label' => 'Bills', 'route' => 'panel.operator.bills.index'],
                ['label' => 'Process Payment', 'route' => 'panel.operator.payments.create'],
            ],
        ],
        [
            'key' => 'cards',
            'label' => 'Recharge Cards',
            'icon' => 'bi-credit-card-2-front',
            'permission' => 'view_cards',
            'route' => 'panel.operator.cards.index',
        ],
    ],

    'card_distributor' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.card-distributor.dashboard',
        ],
        [
            'key' => 'cards',
            'label' => 'My Cards',
            'icon' => 'bi-credit-card-2-front',
            'route' => 'panel.card-distributor.cards.index',
        ],
        [
            'key' => 'sales',
            'label' => 'Sales',
            'icon' => 'bi-cart',
            'route' => 'panel.card-distributor.sales.index',
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventory',
            'icon' => 'bi-box-seam',
            'route' => 'panel.card-distributor.inventory.index',
        ],
    ],

    'manager' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.manager.dashboard',
        ],
        [
            'key' => 'customers',
            'label' => 'My Customers',
            'icon' => 'bi-people',
            'route' => 'panel.manager.customers.index',
        ],
        [
            'key' => 'sessions',
            'label' => 'Active Sessions',
            'icon' => 'bi-activity',
            'route' => 'panel.manager.sessions.index',
        ],
        [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'bi-graph-up',
            'route' => 'panel.manager.reports.index',
        ],
    ],
];
