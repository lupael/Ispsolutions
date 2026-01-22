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
                'description' => 'Manages Admins within their own tenants only. Represents the overarching tenant context. Cannot access other tenants.',
                'level' => 100,
                'permissions' => ['*'], // Wildcard = all permissions
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'ISP Owner. Manages Resellers within their ISP tenant segment. Full control over own ISP data including customers, packages, and network devices.',
                'level' => 90,
                'permissions' => [
                    'resellers.create',
                    'resellers.manage',
                    'sub-resellers.manage',
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
                    'users.manage',
                ],
            ],
            [
                'name' => 'Reseller',
                'slug' => 'reseller',
                'description' => 'Manages Sub-Resellers and customer accounts within their segment. Can view own customers and sub-reseller customers. Restricted panel based on menu configuration.',
                'level' => 60,
                'permissions' => [
                    'sub-resellers.create',
                    'sub-resellers.manage',
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
                'name' => 'Sub-Reseller',
                'slug' => 'sub-reseller',
                'description' => 'Manages only their own customers. Further restricted access under a Reseller. Cannot create other sub-resellers.',
                'level' => 50,
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
                'description' => 'View-only scoped access. Permission-based features with task-specific oversight. Cannot create or manage users.',
                'level' => 40,
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
                'description' => 'View-only scoped access. Support staff with limited operational permissions. Cannot create or manage users.',
                'level' => 30,
                'permissions' => [
                    'customers.view',
                    'network.view',
                    'billing.view',
                    'tickets.manage',
                    'complaints.respond',
                ],
            ],
            [
                'name' => 'Card Distributor',
                'slug' => 'card-distributor',
                'description' => 'Recharge card distributor with card operations only',
                'level' => 20,
                'permissions' => [
                    'cards.view',
                    'cards.sales.view',
                    'cards.commission.view',
                    'balance.view',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End customer with self-service access',
                'level' => 10,
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
