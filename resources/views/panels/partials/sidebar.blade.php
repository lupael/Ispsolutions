@php
    $userRole = auth()->user()->roles->first()?->slug ?? '';
    $currentRoute = request()->route()->getName();
    
    $menus = [];
    
    // Define menu structure for each role
    if ($userRole === 'super-admin') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.super-admin.dashboard', 'icon' => 'home'],
            ['label' => 'Users', 'route' => 'panel.super-admin.users', 'icon' => 'users'],
            ['label' => 'Roles', 'route' => 'panel.super-admin.roles', 'icon' => 'shield'],
            [
                'label' => 'ISP Management',
                'icon' => 'building',
                'children' => [
                    ['label' => 'ISPs List', 'route' => 'panel.super-admin.isp.index'],
                    ['label' => 'Add New ISP', 'route' => 'panel.super-admin.isp.create'],
                ]
            ],
            [
                'label' => 'Billing Config',
                'icon' => 'currency',
                'children' => [
                    ['label' => 'Fixed Billing', 'route' => 'panel.super-admin.billing.fixed'],
                    ['label' => 'User-Base Billing', 'route' => 'panel.super-admin.billing.user-base'],
                    ['label' => 'Panel-Base Billing', 'route' => 'panel.super-admin.billing.panel-base'],
                ]
            ],
            [
                'label' => 'Gateways',
                'icon' => 'credit-card',
                'children' => [
                    ['label' => 'Payment Gateways', 'route' => 'panel.super-admin.payment-gateway.index'],
                    ['label' => 'SMS Gateways', 'route' => 'panel.super-admin.sms-gateway.index'],
                ]
            ],
            ['label' => 'Logs', 'route' => 'panel.super-admin.logs', 'icon' => 'clipboard'],
            ['label' => 'Settings', 'route' => 'panel.super-admin.settings', 'icon' => 'cog'],
        ];
    } elseif ($userRole === 'admin') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.admin.dashboard', 'icon' => 'home'],
            ['label' => 'Users', 'route' => 'panel.admin.users', 'icon' => 'users'],
            // Removed 'Network Users' - customers now managed through customer menu
            ['label' => 'Packages', 'route' => 'panel.admin.packages', 'icon' => 'box'],
            [
                'label' => 'Customers',
                'icon' => 'user-group',
                'children' => [
                    ['label' => 'All Customers', 'route' => 'panel.admin.customers'],
                    ['label' => 'Add Customer', 'route' => 'panel.admin.customers.create'],
                    ['label' => 'Online Customers', 'route' => 'panel.admin.customers.online'],
                    ['label' => 'Offline Customers', 'route' => 'panel.admin.customers.offline'],
                    ['label' => 'Deleted Customers', 'route' => 'panel.admin.customers.deleted'],
                    ['label' => 'Import Requests', 'route' => 'panel.admin.customers.import-requests'],
                    ['label' => 'PPPoE Import', 'route' => 'panel.admin.customers.pppoe-import'],
                    ['label' => 'Bulk Update', 'route' => 'panel.admin.customers.bulk-update'],
                ]
            ],
            [
                'label' => 'Network Devices',
                'icon' => 'server',
                'children' => [
                    ['label' => 'MikroTik Routers', 'route' => 'panel.admin.mikrotik'],
                    ['label' => 'NAS Devices', 'route' => 'panel.admin.nas'],
                    ['label' => 'Cisco Devices', 'route' => 'panel.admin.cisco'],
                    ['label' => 'All Devices', 'route' => 'panel.admin.network.devices'],
                    ['label' => 'Device Monitors', 'route' => 'panel.admin.network.device-monitors'],
                    ['label' => 'Devices Map', 'route' => 'panel.admin.network.devices.map'],
                ]
            ],
            [
                'label' => 'Network',
                'icon' => 'network',
                'children' => [
                    ['label' => 'IPv4 Pools', 'route' => 'panel.admin.network.ipv4-pools'],
                    ['label' => 'IPv6 Pools', 'route' => 'panel.admin.network.ipv6-pools'],
                    ['label' => 'PPPoE Profiles', 'route' => 'panel.admin.network.pppoe-profiles'],
                    ['label' => 'Ping Test', 'route' => 'panel.admin.network.ping-test'],
                ]
            ],
            [
                'label' => 'OLT Management',
                'icon' => 'lightning',
                'children' => [
                    ['label' => 'OLT Devices', 'route' => 'panel.admin.olt'],
                    ['label' => 'OLT Dashboard', 'route' => 'panel.admin.olt.dashboard'],
                    ['label' => 'Templates', 'route' => 'panel.admin.olt.templates'],
                    ['label' => 'SNMP Traps', 'route' => 'panel.admin.olt.snmp-traps'],
                    ['label' => 'Firmware', 'route' => 'panel.admin.olt.firmware'],
                    ['label' => 'Backups', 'route' => 'panel.admin.olt.backups'],
                ]
            ],
            [
                'label' => 'Accounting',
                'icon' => 'currency',
                'children' => [
                    ['label' => 'Transactions', 'route' => 'panel.admin.accounting.transactions'],
                    ['label' => 'Gateway Transactions', 'route' => 'panel.admin.accounting.payment-gateway-transactions'],
                    ['label' => 'Account Statement', 'route' => 'panel.admin.accounting.statement'],
                    ['label' => 'Accounts Payable', 'route' => 'panel.admin.accounting.payable'],
                    ['label' => 'Accounts Receivable', 'route' => 'panel.admin.accounting.receivable'],
                    ['label' => 'Income/Expense Report', 'route' => 'panel.admin.accounting.income-expense-report'],
                    ['label' => 'Expense Report', 'route' => 'panel.admin.accounting.expense-report'],
                    ['label' => 'Expenses', 'route' => 'panel.admin.accounting.expenses'],
                    ['label' => 'VAT Collections', 'route' => 'panel.admin.accounting.vat-collections'],
                    ['label' => 'Customer Payments', 'route' => 'panel.admin.accounting.customer-payments'],
                    ['label' => 'Gateway Payments', 'route' => 'panel.admin.accounting.gateway-customer-payments'],
                ]
            ],
            [
                'label' => 'Operators',
                'icon' => 'user-circle',
                'children' => [
                    ['label' => 'All Operators', 'route' => 'panel.admin.operators'],
                    ['label' => 'Add Operator', 'route' => 'panel.admin.operators.create'],
                    ['label' => 'Sub Operators', 'route' => 'panel.admin.operators.sub-operators'],
                    ['label' => 'Staff', 'route' => 'panel.admin.operators.staff'],
                ]
            ],
            [
                'label' => 'SMS Management',
                'icon' => 'chat',
                'children' => [
                    ['label' => 'Send SMS', 'route' => 'panel.admin.sms.send'],
                    ['label' => 'Broadcast SMS', 'route' => 'panel.admin.sms.broadcast'],
                    ['label' => 'SMS History', 'route' => 'panel.admin.sms.histories'],
                    ['label' => 'SMS Events', 'route' => 'panel.admin.sms.events'],
                    ['label' => 'Due Date Notification', 'route' => 'panel.admin.sms.due-date-notification'],
                    ['label' => 'Payment Link Broadcast', 'route' => 'panel.admin.sms.payment-link-broadcast'],
                ]
            ],
            [
                'label' => 'Logs',
                'icon' => 'clipboard',
                'children' => [
                    ['label' => 'System Log', 'route' => 'panel.admin.logs.system'],
                    ['label' => 'Router Log', 'route' => 'panel.admin.logs.router'],
                    ['label' => 'PPP Log', 'route' => 'panel.admin.logs.ppp'],
                    ['label' => 'Hotspot Log', 'route' => 'panel.admin.logs.hotspot'],
                    ['label' => 'Activity Log', 'route' => 'panel.admin.logs.activity'],
                ]
            ],
            [
                'label' => 'Analytics',
                'icon' => 'chart',
                'children' => [
                    ['label' => 'Dashboard', 'route' => 'panel.admin.analytics.dashboard'],
                    ['label' => 'Revenue Report', 'route' => 'panel.admin.analytics.revenue-report'],
                    ['label' => 'Customer Report', 'route' => 'panel.admin.analytics.customer-report'],
                    ['label' => 'Service Report', 'route' => 'panel.admin.analytics.service-report'],
                ]
            ],
            ['label' => 'Payment Gateways', 'route' => 'panel.admin.payment-gateways', 'icon' => 'credit-card'],
            ['label' => 'Settings', 'route' => 'panel.admin.settings', 'icon' => 'cog'],
        ];
    } elseif ($userRole === 'manager') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.manager.dashboard', 'icon' => 'home'],
            // Removed 'Network Users' - customers managed through customers menu
            ['label' => 'Customers', 'route' => 'panel.manager.customers.index', 'icon' => 'users'],
            ['label' => 'Payments', 'route' => 'panel.manager.payments.index', 'icon' => 'currency'],
            ['label' => 'Complaints', 'route' => 'panel.manager.complaints.index', 'icon' => 'ticket'],
            ['label' => 'Active Sessions', 'route' => 'panel.manager.sessions', 'icon' => 'activity'],
            ['label' => 'Reports', 'route' => 'panel.manager.reports', 'icon' => 'chart'],
        ];
    } elseif ($userRole === 'staff') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.staff.dashboard', 'icon' => 'home'],
            // Removed 'Network Users' - managed through device-specific interfaces
            ['label' => 'Support Tickets', 'route' => 'panel.staff.tickets', 'icon' => 'ticket'],
            [
                'label' => 'Network Devices',
                'icon' => 'server',
                'children' => [
                    ['label' => 'MikroTik Routers', 'route' => 'panel.staff.mikrotik'],
                    ['label' => 'NAS Devices', 'route' => 'panel.staff.nas'],
                    ['label' => 'Cisco Devices', 'route' => 'panel.staff.cisco'],
                    ['label' => 'OLT Devices', 'route' => 'panel.staff.olt'],
                ]
            ],
        ];
    } elseif ($userRole === 'operator') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.operator.dashboard', 'icon' => 'home'],
            ['label' => 'Sub Operators', 'route' => 'panel.operator.sub-operators.index', 'icon' => 'user-group'],
            ['label' => 'Customers', 'route' => 'panel.operator.customers.index', 'icon' => 'users'],
            ['label' => 'Bills', 'route' => 'panel.operator.bills.index', 'icon' => 'receipt'],
            ['label' => 'Collect Payment', 'route' => 'panel.operator.payments.create', 'icon' => 'currency'],
            ['label' => 'Recharge Cards', 'route' => 'panel.operator.cards.index', 'icon' => 'card'],
            ['label' => 'Complaints', 'route' => 'panel.operator.complaints.index', 'icon' => 'ticket'],
            ['label' => 'Reports', 'route' => 'panel.operator.reports.index', 'icon' => 'chart'],
            ['label' => 'Send SMS', 'route' => 'panel.operator.sms.index', 'icon' => 'chat'],
        ];
    } elseif ($userRole === 'sub-operator') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.sub-operator.dashboard', 'icon' => 'home'],
            ['label' => 'My Customers', 'route' => 'panel.sub-operator.customers.index', 'icon' => 'users'],
            ['label' => 'Bills', 'route' => 'panel.sub-operator.bills.index', 'icon' => 'receipt'],
            ['label' => 'Collect Payment', 'route' => 'panel.sub-operator.payments.create', 'icon' => 'currency'],
            ['label' => 'Reports', 'route' => 'panel.sub-operator.reports.index', 'icon' => 'chart'],
        ];
    } elseif ($userRole === 'accountant') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.accountant.dashboard', 'icon' => 'home'],
            [
                'label' => 'Reports',
                'icon' => 'chart',
                'children' => [
                    ['label' => 'Income & Expense', 'route' => 'panel.accountant.reports.income-expense'],
                    ['label' => 'Payment History', 'route' => 'panel.accountant.reports.payments'],
                    ['label' => 'Customer Statements', 'route' => 'panel.accountant.reports.statements'],
                ]
            ],
            ['label' => 'Transactions', 'route' => 'panel.accountant.transactions.index', 'icon' => 'currency'],
            ['label' => 'Expenses', 'route' => 'panel.accountant.expenses.index', 'icon' => 'receipt'],
            ['label' => 'VAT Collections', 'route' => 'panel.accountant.vat.collections', 'icon' => 'clipboard'],
            ['label' => 'Payment History', 'route' => 'panel.accountant.payments.history', 'icon' => 'wallet'],
        ];
    } elseif ($userRole === 'customer') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.customer.dashboard', 'icon' => 'home'],
            ['label' => 'My Profile', 'route' => 'panel.customer.profile', 'icon' => 'user'],
            ['label' => 'Billing History', 'route' => 'panel.customer.billing', 'icon' => 'receipt'],
            ['label' => 'Usage Statistics', 'route' => 'panel.customer.usage', 'icon' => 'chart'],
            ['label' => 'Support Tickets', 'route' => 'panel.customer.tickets', 'icon' => 'ticket'],
        ];
    } elseif ($userRole === 'developer') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.developer.dashboard', 'icon' => 'home'],
            [
                'label' => 'Tenancy Management',
                'icon' => 'database',
                'children' => [
                    ['label' => 'All Tenancies', 'route' => 'panel.developer.tenancies.index'],
                    ['label' => 'Create Tenancy', 'route' => 'panel.developer.tenancies.create'],
                ]
            ],
            [
                'label' => 'User Management',
                'icon' => 'users',
                'children' => [
                    ['label' => 'Super Admins', 'route' => 'panel.developer.super-admins.index'],
                    ['label' => 'Create Super Admin', 'route' => 'panel.developer.super-admins.create'],
                    ['label' => 'All Admins', 'route' => 'panel.developer.admins.index'],
                    ['label' => 'All Customers', 'route' => 'panel.developer.customers.index'],
                ]
            ],
            [
                'label' => 'Subscription Plans',
                'icon' => 'currency',
                'children' => [
                    ['label' => 'Manage Plans', 'route' => 'panel.developer.subscriptions.index'],
                ]
            ],
            [
                'label' => 'Gateway Config',
                'icon' => 'credit-card',
                'children' => [
                    ['label' => 'Payment Gateways', 'route' => 'panel.developer.gateways.payment'],
                    ['label' => 'SMS Gateways', 'route' => 'panel.developer.gateways.sms'],
                ]
            ],
            ['label' => 'VPN Pools', 'route' => 'panel.developer.vpn-pools', 'icon' => 'network'],
            [
                'label' => 'System Logs',
                'icon' => 'clipboard',
                'children' => [
                    ['label' => 'Application Logs', 'route' => 'panel.developer.logs'],
                    ['label' => 'Audit Logs', 'route' => 'panel.developer.audit-logs'],
                    ['label' => 'Error Logs', 'route' => 'panel.developer.error-logs'],
                    ['label' => 'RADIUS Logs', 'route' => 'panel.admin.logs.radius'],
                    ['label' => 'Laravel Logs', 'route' => 'panel.admin.logs.laravel'],
                ]
            ],
            [
                'label' => 'API Management',
                'icon' => 'code',
                'children' => [
                    ['label' => 'API Documentation', 'route' => 'panel.developer.api-docs'],
                    ['label' => 'API Keys', 'route' => 'panel.developer.api-keys'],
                ]
            ],
            ['label' => 'Debug Tools', 'route' => 'panel.developer.debug', 'icon' => 'bug'],
            ['label' => 'Settings', 'route' => 'panel.developer.settings', 'icon' => 'cog'],
        ];
    } elseif ($userRole === 'sales-manager') {
        $menus = [
            ['label' => 'Dashboard', 'route' => 'panel.sales-manager.dashboard', 'icon' => 'home'],
            [
                'label' => 'ISP Clients',
                'icon' => 'building',
                'children' => [
                    ['label' => 'All Clients', 'route' => 'panel.sales-manager.admins.index'],
                ]
            ],
            [
                'label' => 'Lead Management',
                'icon' => 'users',
                'children' => [
                    ['label' => 'Affiliate Leads', 'route' => 'panel.sales-manager.leads.affiliate'],
                    ['label' => 'Create Lead', 'route' => 'panel.sales-manager.leads.create'],
                ]
            ],
            ['label' => 'Sales Comments', 'route' => 'panel.sales-manager.sales-comments', 'icon' => 'chat'],
            [
                'label' => 'Subscriptions',
                'icon' => 'currency',
                'children' => [
                    ['label' => 'Subscription Bills', 'route' => 'panel.sales-manager.subscriptions.bills'],
                    ['label' => 'Record Payment', 'route' => 'panel.sales-manager.subscriptions.payment.create'],
                    ['label' => 'Pending Payments', 'route' => 'panel.sales-manager.subscriptions.pending-payments'],
                ]
            ],
            ['label' => 'Notice Broadcast', 'route' => 'panel.sales-manager.notice-broadcast', 'icon' => 'clipboard'],
            [
                'label' => 'Security',
                'icon' => 'shield',
                'children' => [
                    ['label' => 'Change Password', 'route' => 'panel.sales-manager.change-password'],
                    ['label' => 'Secure Login', 'route' => 'panel.sales-manager.secure-login'],
                ]
            ],
        ];
    }
    
    function getIcon($iconName) {
        $icons = [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
            'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
            'currency' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />',
            'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />',
            'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
            'globe' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />',
            'box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
            'user-group' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
            'server' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />',
            'network' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            'lightning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
            'user-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />',
            'activity' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
            'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />',
            'card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />',
            'shopping' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />',
            'wallet' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
            'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />',
            'database' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />',
            'code' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
            'bug' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ];
        return $icons[$iconName] ?? $icons['home'];
    }
@endphp

<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 overflow-y-auto">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
            <a href="/" class="text-xl font-bold text-gray-800 dark:text-white">
                ISP Solution
            </a>
            <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1">
            @foreach($menus as $menu)
                @if(isset($menu['children']))
                    <!-- Menu with submenu -->
                    @php
                        $isOpen = false;
                        foreach ($menu['children'] as $submenu) {
                            $childRoute = $submenu['route'] ?? '';
                            if ($childRoute === '') {
                                continue;
                            }
                            // Remove a trailing ".index" only, to get the base route name
                            $baseRoute = preg_replace('/\.index$/', '', $childRoute);
                            if ($currentRoute === $childRoute || str_starts_with($currentRoute, $baseRoute . '.')) {
                                $isOpen = true;
                                break;
                            }
                        }
                    @endphp
                    <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }" class="mb-1">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! getIcon($menu['icon']) !!}
                                </svg>
                                <span>{{ $menu['label'] }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="mt-1 ml-4 space-y-1">
                            @foreach($menu['children'] as $submenu)
                                @if(Route::has($submenu['route']))
                                    <a href="{{ route($submenu['route']) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ $currentRoute === $submenu['route'] ? 'bg-indigo-50 dark:bg-gray-700 text-indigo-600 dark:text-indigo-400' : '' }}">
                                        <span class="w-2 h-2 mr-3 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                                        {{ $submenu['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Single menu item -->
                    @if(Route::has($menu['route']))
                        <a href="{{ route($menu['route']) }}" 
                           class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $currentRoute === $menu['route'] ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! getIcon($menu['icon']) !!}
                            </svg>
                            <span>{{ $menu['label'] }}</span>
                        </a>
                    @endif
                @endif
            @endforeach
        </nav>

        <!-- User Info -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ ucwords(str_replace('-', ' ', $userRole)) }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<script nonce="{{ csp_nonce() }}">
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        function closeSidebar() {
            if (sidebar) sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }

        if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    });
</script>
