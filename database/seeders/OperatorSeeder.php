<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (! $tenant) {
            $this->command->warn('No tenant found. Run TenantSeeder first.');

            return;
        }

        // Get or create roles
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full system access',
                'permissions' => ['*'],
                'level' => 10,
            ]
        );

        $groupAdminRole = Role::firstOrCreate(
            ['slug' => 'group-admin'],
            [
                'name' => 'Group Admin (ISP)',
                'description' => 'ISP administrator',
                'permissions' => config('operators_permissions'),
                'level' => 20,
            ]
        );

        $operatorRole = Role::firstOrCreate(
            ['slug' => 'operator'],
            [
                'name' => 'Operator',
                'description' => 'Regular operator',
                'permissions' => [
                    'view_customers',
                    'edit_customers',
                    'view_bills',
                    'process_payments',
                ],
                'level' => 30,
            ]
        );

        // Create operators
        $operators = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@ispsolution.local',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'operator_level' => 10,
                'operator_type' => 'super_admin',
                'is_active' => true,
                'role' => $superAdminRole,
            ],
            [
                'name' => 'ISP Admin',
                'email' => 'admin@demo-isp.local',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 20,
                'operator_type' => 'group_admin',
                'is_active' => true,
                'role' => $groupAdminRole,
            ],
            [
                'name' => 'John Operator',
                'email' => 'operator@demo-isp.local',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 30,
                'operator_type' => 'operator',
                'is_active' => true,
                'role' => $operatorRole,
            ],
        ];

        foreach ($operators as $operatorData) {
            $role = $operatorData['role'];
            unset($operatorData['role']);

            $operator = User::firstOrCreate(
                ['email' => $operatorData['email']],
                $operatorData
            );

            // Attach role without creating duplicates
            $operator->roles()->syncWithoutDetaching([
                $role->id => ['tenant_id' => $operator->tenant_id],
            ]);
        }

        $this->command->info('Operators seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('  Super Admin: superadmin@ispsolution.local / password');
        $this->command->info('  ISP Admin: admin@demo-isp.local / password');
        $this->command->info('  Operator: operator@demo-isp.local / password');
    }
}
