<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Supreme authority. All tenants (can create/manage tenants). Source code owner with unrestricted permissions.',
                'level' => 0,
                'permissions' => ['*'], // Wildcard = all permissions
            ],
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Only OWN tenants. Represents the overarching tenant context (can create/manage admins).',
                'level' => 10,
                'permissions' => [
                    'tenants.manage.own',
                    'admins.create',
                    'admins.manage',
                    'billing.configure',
                    'payment-gateway.manage',
                    'sms-gateway.manage',
                    'subscriptions.manage',
                    'logs.view',
                    'settings.manage',
                ],
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Admin (Formerly Group Admin) ISP Owner, Own ISP data within a tenancy (can create/manage operators).',
                'level' => 20,
                'permissions' => [
                    'operators.create',
                    'operators.manage',
                    'sub-operators.manage',
                    'managers.create',
                    'managers.manage',
                    'customers.manage',
                    'packages.manage',
                    'network.manage',
                    'billing.manage',
                    'reports.view',
                    'settings.manage',
                    'devices.mikrotik.manage',
                    'devices.nas.manage',
                    'devices.cisco.manage',
                    'devices.olt.manage',
                    'recharge-cards.manage',
                    'vat.manage',
                    'affiliate-program.manage',
                ],
            ],
            [
                'name' => 'Operator',
                'slug' => 'operator',
                'description' => 'Own + sub-operator customers (can create/manage sub-operators). Restricted panel based on menu configuration.',
                'level' => 30,
                'permissions' => [
                    'sub-operators.create',
                    'sub-operators.manage',
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'customers.suspend',
                    'customers.activate',
                    'billing.view',
                    'billing.process',
                    'invoices.generate',
                    'payments.receive',
                    'cards.view',
                    'cards.use',
                    'complaints.manage',
                    'reports.view.own',
                    'sms.send.own',
                ],
            ],
            [
                'name' => 'Sub-Operator',
                'slug' => 'sub-operator',
                'description' => 'Only own customers. Further restricted access under an Operator.',
                'level' => 40,
                'permissions' => [
                    'customers.view.own',
                    'customers.update.own',
                    'billing.view.own',
                    'billing.process.own',
                    'payments.receive.own',
                    'reports.view.own',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'View based on permissions. Task-specific access with permission-based feature access.',
                'level' => 50,
                'permissions' => [
                    'customers.view',
                    'network.view',
                    'network.monitor',
                    'sessions.view',
                    'billing.view',
                    'payments.process',
                    'complaints.manage',
                    'reports.view',
                ],
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'View based on permissions. Support staff with limited operational access.',
                'level' => 80,
                'permissions' => [
                    'customers.view',
                    'network.view',
                    'billing.view',
                    'tickets.manage',
                    'complaints.respond',
                ],
            ],
            [
                'name' => 'Reseller',
                'slug' => 'reseller',
                'description' => 'Reseller with customer management and commission access',
                'level' => 60,
                'permissions' => [
                    'customers.manage',
                    'packages.view',
                    'billing.view',
                    'reports.view',
                    'commission.view',
                ],
            ],
            [
                'name' => 'Sub-Reseller',
                'slug' => 'sub-reseller',
                'description' => 'Sub-reseller under a main reseller',
                'level' => 65,
                'permissions' => [
                    'customers.manage',
                    'packages.view',
                    'billing.view',
                    'commission.view',
                ],
            ],
            [
                'name' => 'Card Distributor',
                'slug' => 'card-distributor',
                'description' => 'Recharge card distributor with card operations only',
                'level' => 60,
                'permissions' => [
                    'cards.view',
                    'cards.sales.view',
                    'cards.commission.view',
                    'balance.view',
                ],
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'View based on permissions. Accountant with read-only financial reporting access.',
                'level' => 70,
                'permissions' => [
                    'reports.financial.view',
                    'reports.vat.view',
                    'reports.payments.view',
                    'reports.income-expense.view',
                    'transactions.view',
                    'expenses.view',
                    'vat.view',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End customer with self-service access',
                'level' => 100,
                'permissions' => [
                    'profile.view',
                    'profile.update',
                    'billing.view.own',
                    'usage.view.own',
                    'tickets.create',
                    'tickets.view.own',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info(count($roles).' roles seeded successfully with updated hierarchy!');
    }
}
