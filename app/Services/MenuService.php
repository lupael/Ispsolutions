<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class MenuService
{
    /**
     * Generate menu items based on user roles and permissions.
     *
     * Role handling:
     * - A user may have multiple roles assigned.
     * - Menus are generated based on a fixed role priority/hierarchy.
     * - The menu for the highest-priority role that the user has will be returned.
     * - Role priority order (highest to lowest):
     *   1. Developer (supreme authority)
     *   2. Super Admin
     *   3. Admin
     *   4. Manager
     *   5. Operator
     *   6. Sub-Operator
     *   7. Staff
     *   8. Card Distributor
     *   9. Customer
     */
    public function generateMenu(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $menu = [];

        // Developer menu (supreme authority)
        if ($user->hasRole('developer')) {
            $menu = $this->getDeveloperMenu();
        }
        // Super Admin menu
        elseif ($user->hasRole('super-admin')) {
            $menu = $this->getSuperAdminMenu();
        }
        // Admin menu
        elseif ($user->hasRole('admin')) {
            $menu = $this->getAdminMenu();
        }
        // Manager menu
        elseif ($user->hasRole('manager')) {
            $menu = $this->getManagerMenu();
        }
        // Operator menu
        elseif ($user->hasRole('operator')) {
            $menu = $this->getOperatorMenu();
        }
        // Sub-Operator menu
        elseif ($user->hasRole('sub-operator')) {
            $menu = $this->getSubOperatorMenu();
        }
        // Staff menu
        elseif ($user->hasRole('staff')) {
            $menu = $this->getStaffMenu();
        }
        // Card Distributor menu
        elseif ($user->hasRole('card-distributor')) {
            $menu = $this->getCardDistributorMenu();
        }
        // Customer menu
        elseif ($user->hasRole('customer')) {
            $menu = $this->getCustomerMenu();
        }

        return $menu;
    }

    /**
     * Get Developer menu items (supreme authority).
     */
    protected function getDeveloperMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.developer.dashboard',
            ],
            [
                'title' => 'Tenancy Management',
                'icon' => 'ki-abstract-26',
                'children' => [
                    ['title' => 'All Tenancies', 'route' => 'panel.developer.tenancies.index'],
                    ['title' => 'Create Tenancy', 'route' => 'panel.developer.tenancies.create'],
                    ['title' => 'Subscription Plans', 'route' => 'panel.developer.subscriptions.index'],
                ],
            ],
            [
                'title' => 'System Access',
                'icon' => 'ki-security-user',
                'children' => [
                    ['title' => 'Access Any Panel', 'route' => 'panel.developer.access-panel'],
                    ['title' => 'Search Customers', 'route' => 'panel.developer.customers.search'],
                    ['title' => 'View All Customers', 'route' => 'panel.developer.customers.index'],
                ],
            ],
            [
                'title' => 'Audit & Logs',
                'icon' => 'ki-document',
                'children' => [
                    ['title' => 'Audit Logs', 'route' => 'panel.developer.audit-logs'],
                    ['title' => 'System Logs', 'route' => 'panel.developer.logs'],
                    ['title' => 'Error Logs', 'route' => 'panel.developer.error-logs'],
                ],
            ],
            [
                'title' => 'API Management',
                'icon' => 'ki-code',
                'children' => [
                    ['title' => 'API Documentation', 'route' => 'panel.developer.api-docs'],
                    ['title' => 'API Keys', 'route' => 'panel.developer.api-keys'],
                ],
            ],
            [
                'title' => 'System Tools',
                'icon' => 'ki-setting-2',
                'children' => [
                    ['title' => 'Debug Tools', 'route' => 'panel.developer.debug'],
                    ['title' => 'System Settings', 'route' => 'panel.developer.settings'],
                ],
            ],
        ];
    }

    /**
     * Get Super Admin menu items.
     */
    protected function getSuperAdminMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.super-admin.dashboard',
            ],
            [
                'title' => 'ISP Management',
                'icon' => 'ki-abstract-26',
                'children' => [
                    ['title' => 'Add New ISP/Admin', 'route' => 'panel.super-admin.isp.create'],
                    ['title' => 'Manage ISPs', 'route' => 'panel.super-admin.isp.index'],
                ],
            ],
            [
                'title' => 'Billing Configuration',
                'icon' => 'ki-bill',
                'children' => [
                    ['title' => 'Fixed Bill', 'route' => 'panel.super-admin.billing.fixed'],
                    ['title' => 'User Base Bill', 'route' => 'panel.super-admin.billing.user-base'],
                    ['title' => 'Panel Base Bill', 'route' => 'panel.super-admin.billing.panel-base'],
                ],
            ],
            [
                'title' => 'Payment Gateway',
                'icon' => 'ki-wallet',
                'children' => [
                    ['title' => 'Add Gateway', 'route' => 'panel.super-admin.payment-gateway.create'],
                    ['title' => 'Manage Gateways', 'route' => 'panel.super-admin.payment-gateway.index'],
                ],
            ],
            [
                'title' => 'SMS Gateway',
                'icon' => 'ki-message-text-2',
                'children' => [
                    ['title' => 'Add SMS Gateway', 'route' => 'panel.super-admin.sms-gateway.create'],
                    ['title' => 'Manage SMS Gateways', 'route' => 'panel.super-admin.sms-gateway.index'],
                ],
            ],
            [
                'title' => 'Users & Roles',
                'icon' => 'ki-profile-user',
                'children' => [
                    ['title' => 'All Users', 'route' => 'panel.super-admin.users'],
                    ['title' => 'Manage Roles', 'route' => 'panel.super-admin.roles'],
                ],
            ],
            [
                'title' => 'Logs',
                'icon' => 'ki-document',
                'route' => 'panel.super-admin.logs',
            ],
            [
                'title' => 'Settings',
                'icon' => 'ki-setting-2',
                'route' => 'panel.super-admin.settings',
            ],
        ];
    }

    /**
     * Get Admin menu items.
     */
    protected function getAdminMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.admin.dashboard',
            ],
            [
                'title' => 'Customers',
                'icon' => 'ki-profile-user',
                'children' => [
                    ['title' => 'All Customers', 'route' => 'panel.admin.customers'],
                    ['title' => 'Add Customer', 'route' => 'panel.admin.customers.create'],
                    ['title' => 'Online Customers', 'route' => 'panel.admin.customers.online'],
                    ['title' => 'Offline Customers', 'route' => 'panel.admin.customers.offline'],
                ],
            ],
            [
                'title' => 'Network Management',
                'icon' => 'ki-abstract-26',
                'children' => [
                    ['title' => 'Customers', 'route' => 'panel.admin.customers'],
                    ['title' => 'MikroTik', 'route' => 'panel.admin.mikrotik'],
                    ['title' => 'NAS Devices', 'route' => 'panel.admin.nas'],
                    ['title' => 'OLT Devices', 'route' => 'panel.admin.olt'],
                    ['title' => 'IP Pools', 'route' => 'panel.admin.network.ipv4-pools'],
                ],
            ],
            [
                'title' => 'Packages',
                'icon' => 'ki-package',
                'route' => 'panel.admin.packages',
            ],
            [
                'title' => 'Billing',
                'icon' => 'ki-bill',
                'children' => [
                    ['title' => 'Transactions', 'route' => 'panel.admin.accounting.transactions'],
                    ['title' => 'Customer Payments', 'route' => 'panel.admin.accounting.customer-payments'],
                    ['title' => 'Income/Expense', 'route' => 'panel.admin.accounting.income-expense-report'],
                ],
            ],
            [
                'title' => 'SMS',
                'icon' => 'ki-message-text-2',
                'children' => [
                    ['title' => 'Send SMS', 'route' => 'panel.admin.sms.send'],
                    ['title' => 'SMS History', 'route' => 'panel.admin.sms.histories'],
                ],
            ],
            [
                'title' => 'Settings',
                'icon' => 'ki-setting-2',
                'route' => 'panel.admin.settings',
            ],
        ];
    }

    /**
     * Get Manager menu items.
     */
    protected function getManagerMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.manager.dashboard',
            ],
            [
                'title' => 'Customers',
                'icon' => 'ki-profile-user',
                'route' => 'panel.manager.customers.index',
            ],
            [
                'title' => 'Sessions',
                'icon' => 'ki-abstract-26',
                'route' => 'panel.manager.sessions',
            ],
            [
                'title' => 'Reports',
                'icon' => 'ki-document',
                'route' => 'panel.manager.reports',
            ],
        ];
    }

    /**
     * Get Operator menu items.
     * Note: Operator replaces the deprecated "Reseller" role.
     */
    protected function getOperatorMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.operator.dashboard',
            ],
            [
                'title' => 'Customers',
                'icon' => 'ki-profile-user',
                'route' => 'panel.operator.customers',
            ],
            [
                'title' => 'Packages',
                'icon' => 'ki-package',
                'route' => 'panel.operator.packages',
            ],
            [
                'title' => 'Commission',
                'icon' => 'ki-bill',
                'route' => 'panel.operator.commission',
            ],
        ];
    }

    /**
     * Get Sub-Operator menu items.
     * Note: Sub-Operator replaces the deprecated "Sub-Reseller" role.
     */
    protected function getSubOperatorMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.sub-operator.dashboard',
            ],
            [
                'title' => 'Customers',
                'icon' => 'ki-profile-user',
                'route' => 'panel.sub-operator.customers',
            ],
            [
                'title' => 'Packages',
                'icon' => 'ki-package',
                'route' => 'panel.sub-operator.packages',
            ],
            [
                'title' => 'Commission',
                'icon' => 'ki-bill',
                'route' => 'panel.sub-operator.commission',
            ],
        ];
    }

    /**
     * Get Staff menu items.
     */
    protected function getStaffMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.staff.dashboard',
            ],
            // Note: Staff role doesn't have direct customer management access
            // Customers are managed through admin/manager panels
            // [
            //     'title' => 'Customers',
            //     'icon' => 'ki-profile-user',
            //     'route' => 'panel.staff.customers',
            // ],
            [
                'title' => 'Tickets',
                'icon' => 'ki-message-text-2',
                'route' => 'panel.staff.tickets',
            ],
        ];
    }

    /**
     * Get Card Distributor menu items.
     */
    protected function getCardDistributorMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.card-distributor.dashboard',
            ],
            [
                'title' => 'Cards',
                'icon' => 'ki-badge',
                'route' => 'panel.card-distributor.cards',
            ],
            [
                'title' => 'Sales',
                'icon' => 'ki-bill',
                'route' => 'panel.card-distributor.sales',
            ],
            [
                'title' => 'Balance',
                'icon' => 'ki-wallet',
                'route' => 'panel.card-distributor.balance',
            ],
        ];
    }

    /**
     * Get Customer menu items.
     */
    protected function getCustomerMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'icon' => 'ki-home-3',
                'route' => 'panel.customer.dashboard',
            ],
            [
                'title' => 'Profile',
                'icon' => 'ki-profile-user',
                'route' => 'panel.customer.profile',
            ],
            [
                'title' => 'Billing',
                'icon' => 'ki-bill',
                'route' => 'panel.customer.billing',
            ],
            [
                'title' => 'Usage',
                'icon' => 'ki-chart-simple',
                'route' => 'panel.customer.usage',
            ],
            [
                'title' => 'Tickets',
                'icon' => 'ki-message-text-2',
                'route' => 'panel.customer.tickets',
            ],
        ];
    }
}
