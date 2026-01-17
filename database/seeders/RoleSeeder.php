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
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access with all privileges across all tenants',
                'level' => 100,
                'permissions' => ['*'],
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
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Developer with API and system access',
                'level' => 95,
                'permissions' => [
                    'api.access',
                    'system.debug',
                    'logs.view',
                    'settings.manage',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('9 roles seeded successfully!');
    }
}
