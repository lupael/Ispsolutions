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
                'description' => 'Supreme authority across all tenants. Can create and manage Super Admins. Source code owner with unrestricted permissions.',
                'level' => 0,
                'permissions' => ['*'], // Wildcard = all permissions
            ],
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Tenancy owner. Manages Admins within their own tenancy only. Cannot access other tenancies.',
                'level' => 10,
                'permissions' => ['*'], // Wildcard = all permissions
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'ISP Owner. Manages Operators, Sub-Operators, Staff, and Managers within their ISP. Full control over own ISP data including customers, packages, and network devices.',
                'level' => 20,
                'permissions' => [
                    'operators.create',
                    'operators.manage',
                    'sub-operators.create',
                    'sub-operators.manage',
                    'managers.create',
                    'managers.manage',
                    'accountants.create',
                    'accountants.manage',
                    'staff.create',
                    'staff.manage',
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
                    'users.manage',
                ],
            ],
            [
                'name' => 'Operator',
                'slug' => 'operator',
                'description' => 'Manages Sub-Operators and customer accounts within their segment. Can view own customers and sub-operator customers. Restricted panel based on menu configuration.',
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
                    'commission.view',
                    'packages.view',
                ],
            ],
            [
                'name' => 'Sub-Operator',
                'slug' => 'sub-operator',
                'description' => 'Manages only their own customers. Further restricted access under an Operator. Cannot create other sub-operators.',
                'level' => 40,
                'permissions' => [
                    'customers.view.own',
                    'customers.create.own',
                    'customers.update.own',
                    'billing.view.own',
                    'billing.process.own',
                    'payments.receive.own',
                    'reports.view.own',
                    'commission.view',
                    'packages.view',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'View/Edit access if explicitly permitted by Admin. Permission-based features with task-specific oversight. Cannot create or manage users.',
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
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'View-only financial access. Can view billing, payments, and financial reports. Cannot create or manage users.',
                'level' => 70,
                'permissions' => [
                    'billing.view',
                    'payments.view',
                    'reports.financial.view',
                    'invoices.view',
                ],
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'View/Edit access if explicitly permitted by Admin. Support staff with limited operational permissions. Cannot create or manage users.',
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
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End user with self-service access',
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

        $this->command->info(count($roles) . ' roles seeded successfully with updated hierarchy!');
    }
}
