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
            'label' => 'Tenant Management',
            'icon' => 'bi-building',
            'children' => [
                ['label' => 'All Tenants', 'route' => 'panel.super-admin.isp.index'],
                ['label' => 'Add Tenant', 'route' => 'panel.super-admin.isp.create'],
            ],
        ],
        [
            'key' => 'admins',
            'label' => 'Admin Management',
            'icon' => 'bi-person-badge',
            'route' => 'panel.super-admin.users',
        ],
        [
            'key' => 'subscriptions',
            'label' => 'Subscription Management',
            'icon' => 'bi-wallet2',
            'children' => [
                ['label' => 'Fixed Billing', 'route' => 'panel.super-admin.billing.fixed'],
                ['label' => 'User-Based', 'route' => 'panel.super-admin.billing.user-base'],
                ['label' => 'Panel-Based', 'route' => 'panel.super-admin.billing.panel-base'],
            ],
        ],
        [
            'key' => 'global_config',
            'label' => 'Global Configuration',
            'icon' => 'bi-gear-wide-connected',
            'children' => [
                ['label' => 'System Settings', 'route' => 'panel.super-admin.settings'],
                ['label' => 'Payment Gateways', 'route' => 'panel.super-admin.payment-gateway.index'],
                ['label' => 'SMS Gateways', 'route' => 'panel.super-admin.sms-gateway.index'],
            ],
        ],
        [
            'key' => 'logs',
            'label' => 'System Logs',
            'icon' => 'bi-journal-text',
            'route' => 'panel.super-admin.logs',
        ],
        [
            'key' => 'monitoring',
            'label' => 'Monitoring',
            'icon' => 'bi-activity',
            'route' => 'panel.super-admin.monitoring',
        ],
    ],

    'admin' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.admin.dashboard',
        ],
        [
            'key' => 'operators_managers',
            'label' => 'Operators & Managers',
            'icon' => 'bi-people-fill',
            'children' => [
                ['label' => 'Operators', 'route' => 'panel.admin.operators.index'],
                ['label' => 'Sub-Operators', 'route' => 'panel.admin.operators.sub-operators'],
                ['label' => 'Managers', 'route' => 'panel.admin.operators.staff'],
            ],
        ],
        [
            'key' => 'routers_packages',
            'label' => 'Routers & Packages',
            'icon' => 'bi-hdd-network',
            'children' => [
                ['label' => 'Routers', 'route' => 'panel.admin.network.routers'],
                ['label' => 'Master Packages', 'route' => 'panel.admin.packages.index'],
                ['label' => 'PPPoE Profiles', 'route' => 'panel.admin.network.pppoe-profiles'],
            ],
        ],
        [
            'key' => 'olt_management',
            'label' => 'OLT Management',
            'icon' => 'bi-broadcast',
            'children' => [
                ['label' => 'OLT Dashboard', 'route' => 'panel.admin.olt.dashboard'],
                ['label' => 'ONU Devices', 'route' => 'panel.admin.network.onu.index'],
            ],
        ],
        [
            'key' => 'recharge_cards',
            'label' => 'Recharge Cards',
            'icon' => 'bi-credit-card-2-front',
            'children' => [
                ['label' => 'Card Generation', 'route' => 'panel.admin.cards.generate'],
                ['label' => 'Distributor Management', 'route' => 'panel.admin.cards.distributors'],
            ],
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'children' => [
                ['label' => 'All Customers', 'route' => 'panel.admin.customers.index'],
                ['label' => 'Online Customers', 'route' => 'panel.admin.customers-online'],
                ['label' => 'Offline Customers', 'route' => 'panel.admin.customers-offline'],
                ['label' => 'Import Customers', 'route' => 'panel.admin.customers.pppoe-import'],
                ['label' => 'Customer Zones', 'route' => 'panel.admin.customers.zones'],
            ],
        ],
        [
            'key' => 'cable_tv',
            'label' => 'Cable TV',
            'icon' => 'bi-tv',
            'children' => [
                ['label' => 'Subscriptions', 'route' => 'panel.admin.cable-tv.index'],
                ['label' => 'Add Subscription', 'route' => 'panel.admin.cable-tv.create'],
                ['label' => 'Packages', 'route' => 'panel.admin.cable-tv.packages.index'],
                ['label' => 'Channels', 'route' => 'panel.admin.cable-tv.channels.index'],
            ],
        ],
        [
            'key' => 'bills_payments',
            'label' => 'Bills & Payments',
            'icon' => 'bi-receipt',
            'children' => [
                ['label' => 'Billing', 'route' => 'panel.admin.bills.index'],
                ['label' => 'Payment Verification', 'route' => 'panel.admin.payments.index'],
                ['label' => 'Due Notifications', 'route' => 'panel.admin.sms.due-date-notification'],
            ],
        ],
        [
            'key' => 'incomes_expenses',
            'label' => 'Incomes & Expenses',
            'icon' => 'bi-cash-stack',
            'children' => [
                ['label' => 'Transactions', 'route' => 'panel.admin.accounting.transactions'],
                ['label' => 'Expenses', 'route' => 'panel.admin.accounting.expenses'],
                ['label' => 'Income/Expense Report', 'route' => 'panel.admin.accounting.income-expense-report'],
            ],
        ],
        [
            'key' => 'complaints_support',
            'label' => 'Complaints & Support',
            'icon' => 'bi-headset',
            'children' => [
                ['label' => 'Ticket Management', 'route' => 'panel.admin.tickets.index'],
                ['label' => 'Categories', 'route' => 'panel.admin.tickets.categories'],
            ],
        ],
        [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'bi-graph-up',
            'children' => [
                ['label' => 'BTRC Reports', 'route' => 'panel.admin.reports.btrc'],
                ['label' => 'Financial Reports', 'route' => 'panel.admin.reports.financial'],
                ['label' => 'Customer Reports', 'route' => 'panel.admin.reports.customer'],
            ],
        ],
        [
            'key' => 'analytics_dashboard',
            'label' => 'Analytics Dashboard',
            'icon' => 'bi-bar-chart-line',
            'route' => 'panel.admin.analytics.dashboard',
        ],
        [
            'key' => 'affiliate_program',
            'label' => 'Affiliate Program',
            'icon' => 'bi-share',
            'children' => [
                ['label' => 'Referral Tracking', 'route' => 'panel.admin.affiliate.referrals'],
                ['label' => 'Commission Tracking', 'route' => 'panel.admin.affiliate.commissions'],
            ],
        ],
        [
            'key' => 'vat_management',
            'label' => 'VAT Management',
            'icon' => 'bi-calculator',
            'children' => [
                ['label' => 'Tax Profiles', 'route' => 'panel.admin.vat.profiles'],
                ['label' => 'Collections', 'route' => 'panel.admin.accounting.vat-collections'],
            ],
        ],
        [
            'key' => 'sms_services',
            'label' => 'SMS Services',
            'icon' => 'bi-chat-dots',
            'children' => [
                ['label' => 'Gateway Configuration', 'route' => 'panel.admin.sms.gateway'],
                ['label' => 'Broadcasting', 'route' => 'panel.admin.sms.broadcast'],
                ['label' => 'SMS History', 'route' => 'panel.admin.sms.histories'],
            ],
        ],
        [
            'key' => 'configuration',
            'label' => 'Configuration',
            'icon' => 'bi-gear',
            'children' => [
                ['label' => 'Billing Profiles', 'route' => 'panel.admin.config.billing'],
                ['label' => 'Custom Fields', 'route' => 'panel.admin.config.custom-fields'],
                ['label' => 'All Devices', 'route' => 'panel.admin.network.devices'],
                // Temporarily hidden from device list as per issue requirements
                // Note: Cisco and MikroTik still accessible via direct routes if needed
            ],
        ],
        [
            'key' => 'activity_logs',
            'label' => 'Activity Logs',
            'icon' => 'bi-journal-text',
            'children' => [
                ['label' => 'Audit Trail', 'route' => 'panel.admin.logs.audit'],
                ['label' => 'Authentication Logs', 'route' => 'panel.admin.logs.auth'],
            ],
        ],
        [
            'key' => 'notifications',
            'label' => 'Notifications',
            'icon' => 'bi-bell',
            'children' => [
                ['label' => 'All Notifications', 'route' => 'notifications.index'],
                ['label' => 'Preferences', 'route' => 'notifications.preferences'],
            ],
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
            'key' => 'sub_operators',
            'label' => 'Sub-Operators',
            'icon' => 'bi-person-lines-fill',
            'route' => 'panel.operator.sub-operators.index',
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'route' => 'panel.operator.customers.index',
        ],
        [
            'key' => 'bills_payments',
            'label' => 'Bills & Payments',
            'icon' => 'bi-receipt',
            'children' => [
                ['label' => 'Bills', 'route' => 'panel.operator.bills.index'],
                ['label' => 'Process Payment', 'route' => 'panel.operator.payments.create'],
            ],
        ],
        [
            'key' => 'recharge_cards',
            'label' => 'Recharge Cards',
            'icon' => 'bi-credit-card-2-front',
            'route' => 'panel.operator.cards.index',
        ],
        [
            'key' => 'complaints',
            'label' => 'Complaints',
            'icon' => 'bi-headset',
            'route' => 'panel.operator.complaints.index',
        ],
        [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'bi-graph-up',
            'route' => 'panel.operator.reports.index',
        ],
        [
            'key' => 'sms',
            'label' => 'SMS',
            'icon' => 'bi-chat-dots',
            'route' => 'panel.operator.sms.index',
        ],
    ],

    'sub_operator' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.sub-operator.dashboard',
        ],
        [
            'key' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'route' => 'panel.sub-operator.customers.index',
        ],
        [
            'key' => 'bills_payments',
            'label' => 'Bills & Payments',
            'icon' => 'bi-receipt',
            'children' => [
                ['label' => 'Bills', 'route' => 'panel.sub-operator.bills.index'],
                ['label' => 'Process Payment', 'route' => 'panel.sub-operator.payments.create'],
            ],
        ],
        [
            'key' => 'reports',
            'label' => 'Basic Reports',
            'icon' => 'bi-graph-up',
            'route' => 'panel.sub-operator.reports.index',
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
            'label' => 'Customer Viewing',
            'icon' => 'bi-people',
            'route' => 'panel.manager.customers.index',
        ],
        [
            'key' => 'payments',
            'label' => 'Payment Processing',
            'icon' => 'bi-cash',
            'route' => 'panel.manager.payments.index',
        ],
        [
            'key' => 'complaints',
            'label' => 'Complaint Management',
            'icon' => 'bi-headset',
            'route' => 'panel.manager.complaints.index',
        ],
        [
            'key' => 'reports',
            'label' => 'Basic Reports',
            'icon' => 'bi-graph-up',
            'route' => 'panel.manager.reports.index',
        ],
    ],

    'developer' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.developer.dashboard',
        ],
        [
            'key' => 'tenant_management',
            'label' => 'Tenant Management',
            'icon' => 'bi-building',
            'children' => [
                ['label' => 'All Tenants', 'route' => 'panel.developer.tenancies.index'],
                ['label' => 'Add Tenant', 'route' => 'panel.developer.tenancies.create'],
            ],
        ],
        [
            'key' => 'subscriptions',
            'label' => 'Subscription Management',
            'icon' => 'bi-wallet2',
            'route' => 'panel.developer.subscriptions.index',
        ],
        [
            'key' => 'global_config',
            'label' => 'Global Configuration',
            'icon' => 'bi-gear-wide-connected',
            'route' => 'panel.developer.config.index',
        ],
        [
            'key' => 'sms_gateway',
            'label' => 'SMS Gateway Configuration',
            'icon' => 'bi-chat-dots',
            'route' => 'panel.developer.sms-gateway.index',
        ],
        [
            'key' => 'payment_gateway',
            'label' => 'Payment Gateway Configuration',
            'icon' => 'bi-credit-card',
            'route' => 'panel.developer.payment-gateway.index',
        ],
        [
            'key' => 'vpn_pools',
            'label' => 'VPN Pools',
            'icon' => 'bi-shield-lock',
            'route' => 'panel.developer.vpn-pools.index',
        ],
        [
            'key' => 'system_logs',
            'label' => 'System Logs',
            'icon' => 'bi-journal-text',
            'route' => 'panel.developer.logs',
        ],
        [
            'key' => 'api_management',
            'label' => 'API Management',
            'icon' => 'bi-code-square',
            'children' => [
                ['label' => 'API Documentation', 'route' => 'panel.developer.api-docs'],
                ['label' => 'API Keys', 'route' => 'panel.developer.api-keys'],
            ],
        ],
    ],

    'accountant' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.accountant.dashboard',
        ],
        [
            'key' => 'financial_reports',
            'label' => 'Financial Reports',
            'icon' => 'bi-graph-up',
            'children' => [
                ['label' => 'Income/Expense', 'route' => 'panel.accountant.reports.income-expense'],
                ['label' => 'Payment History', 'route' => 'panel.accountant.reports.payments'],
                ['label' => 'Customer Statements', 'route' => 'panel.accountant.reports.statements'],
            ],
        ],
        [
            'key' => 'income_expense',
            'label' => 'Income/Expense Tracking',
            'icon' => 'bi-cash-stack',
            'children' => [
                ['label' => 'Transactions', 'route' => 'panel.accountant.transactions.index'],
                ['label' => 'Expenses', 'route' => 'panel.accountant.expenses.index'],
            ],
        ],
        [
            'key' => 'vat_collections',
            'label' => 'VAT Collections',
            'icon' => 'bi-calculator',
            'route' => 'panel.accountant.vat.collections',
        ],
        [
            'key' => 'payment_history',
            'label' => 'Payment History',
            'icon' => 'bi-clock-history',
            'route' => 'panel.accountant.payments.history',
        ],
        [
            'key' => 'customer_statements',
            'label' => 'Customer Statements',
            'icon' => 'bi-receipt',
            'route' => 'panel.accountant.customers.statements',
        ],
    ],

    'customer' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.customer.dashboard',
        ],
        [
            'key' => 'profile',
            'label' => 'Profile',
            'icon' => 'bi-person',
            'route' => 'panel.customer.profile',
        ],
        [
            'key' => 'billing',
            'label' => 'Billing',
            'icon' => 'bi-receipt',
            'route' => 'panel.customer.billing',
        ],
        [
            'key' => 'usage',
            'label' => 'Usage Statistics',
            'icon' => 'bi-graph-up',
            'route' => 'panel.customer.usage',
        ],
        [
            'key' => 'tickets',
            'label' => 'Support Tickets',
            'icon' => 'bi-headset',
            'route' => 'panel.customer.tickets',
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
            'label' => 'Recharge Cards',
            'icon' => 'bi-credit-card-2-front',
            'route' => 'panel.card-distributor.cards',
        ],
        [
            'key' => 'sales',
            'label' => 'Sales History',
            'icon' => 'bi-cart-check',
            'route' => 'panel.card-distributor.sales',
        ],
        [
            'key' => 'commissions',
            'label' => 'My Commissions',
            'icon' => 'bi-currency-dollar',
            'route' => 'panel.card-distributor.commissions',
        ],
        [
            'key' => 'balance',
            'label' => 'Balance',
            'icon' => 'bi-wallet2',
            'route' => 'panel.card-distributor.balance',
        ],
    ],

    'staff' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'panel.staff.dashboard',
        ],
        [
            'key' => 'network_users',
            'label' => 'Network Users',
            'icon' => 'bi-people',
            'route' => 'panel.staff.network-users',
        ],
        [
            'key' => 'tickets',
            'label' => 'Support Tickets',
            'icon' => 'bi-headset',
            'route' => 'panel.staff.tickets',
        ],
        [
            'key' => 'devices',
            'label' => 'Network Devices',
            'icon' => 'bi-hdd-network',
            'children' => [
                // Temporarily hidden as per issue requirements
                // ['label' => 'MikroTik', 'route' => 'panel.staff.mikrotik'],
                // ['label' => 'Cisco Devices', 'route' => 'panel.staff.cisco'],
                ['label' => 'Router (RADIUS NAS)', 'route' => 'panel.staff.nas'],
                ['label' => 'OLT Devices', 'route' => 'panel.staff.olt'],
            ],
        ],
    ],

    // Backward compatibility: group_admin uses admin menu
    'group_admin' => null, // Will be resolved to 'admin' menu in menu helper
];
