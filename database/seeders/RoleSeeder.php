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
                'description' => 'Source code owner and supreme authority with all permissions. Can create tenancies, define subscription prices, access any panel, view all customer details, audit logs, and suspend/activate tenancies.',
                'level' => 1000,
                'permissions' => ['*'],
            ],
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Tenancy administrator with full privileges. Can add ISPs/admins, configure billing, payment gateways, SMS gateways, and view logs.',
                'level' => 100,
                'permissions' => [
                    'tenants.manage',
                    'isp.create',
                    'billing.configure',
                    'payment-gateway.manage',
                    'sms-gateway.manage',
                    'logs.view',
                    'users.manage',
                    'roles.manage',
                    'network.manage',
                    'billing.manage',
                    'reports.view',
                    'settings.manage',
                ],
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Tenant administrator with full access within their tenant',
                'level' => 90,
                'permissions' => [
                    'users.manage',
                    'roles.manage',
                    'network.manage',
                    'billing.manage',
                    'reports.view',
                    'settings.manage',
                    'devices.mikrotik.manage',
                    'devices.nas.manage',
                    'devices.cisco.manage',
                    'devices.olt.manage',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manager with operational permissions',
                'level' => 70,
                'permissions' => [
                    'users.view',
                    'users.create',
                    'users.update',
                    'network.view',
                    'network.manage',
                    'billing.view',
                    'reports.view',
                ],
            ],
            [
                'name' => 'Operator',
                'slug' => 'operator',
                'description' => 'Operator with restricted panel based on menu configuration',
                'level' => 30,
                'permissions' => [
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'billing.view',
                    'billing.process',
                    'cards.view',
                    'reports.view',
                ],
            ],
            [
                'name' => 'Sub-Operator',
                'slug' => 'sub-operator',
                'description' => 'Sub-operator with further restricted access',
                'level' => 40,
                'permissions' => [
                    'customers.view',
                    'billing.view',
                    'reports.view',
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
                'level' => 55,
                'permissions' => [
                    'customers.manage',
                    'packages.view',
                    'billing.view',
                    'commission.view',
                ],
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Staff member with limited operational access',
                'level' => 50,
                'permissions' => [
                    'users.view',
                    'network.view',
                    'billing.view',
                    'tickets.manage',
                ],
            ],
            [
                'name' => 'Card Distributor',
                'slug' => 'card-distributor',
                'description' => 'Recharge card distributor',
                'level' => 40,
                'permissions' => [
                    'cards.manage',
                    'cards.sell',
                    'balance.view',
                ],
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Accountant with read-only financial reporting access',
                'level' => 70,
                'permissions' => [
                    'reports.financial.view',
                    'reports.vat.view',
                    'reports.payments.view',
                    'transactions.view',
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
                    'billing.view',
                    'tickets.create',
                    'tickets.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('12 roles seeded successfully!');
    }
}
