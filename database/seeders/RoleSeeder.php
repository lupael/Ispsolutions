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
                'level' => 100,
                'permissions' => ['*'], // Wildcard = all permissions
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'ISP Owner. Manages Operators, Sub-Operators, Staff, and Managers within their ISP. Full control over own ISP data including customers, packages, and network devices.',
                'level' => 90,
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
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'View/Edit access if explicitly permitted by Admin. Permission-based features with task-specific oversight. Cannot create or manage users.',
                'level' => 80,
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
                'description' => 'View/Edit access if explicitly permitted by Admin. Support staff with limited operational permissions. Cannot create or manage users.',
                'level' => 70,
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
                'description' => 'Resells ISP services with ability to manage sub-resellers and customers.',
                'level' => 60,
                'permissions' => [
                    'sub-resellers.create',
                    'sub-resellers.manage',
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'billing.view',
                    'billing.process',
                    'commission.view',
                    'packages.view',
                ],
            ],
            [
                'name' => 'Sub-Reseller',
                'slug' => 'sub-reseller',
                'description' => 'Sub-reseller under a Reseller. Manages their own customers only.',
                'level' => 50,
                'permissions' => [
                    'customers.view.own',
                    'customers.create.own',
                    'customers.update.own',
                    'billing.view.own',
                    'billing.process.own',
                    'commission.view',
                    'packages.view',
                ],
            ],
            [
                'name' => 'Card Distributor',
                'slug' => 'card-distributor',
                'description' => 'Distributes recharge cards to customers.',
                'level' => 40,
                'permissions' => [
                    'cards.view',
                    'cards.distribute',
                    'commission.view',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End user with self-service access',
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
