<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
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

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
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
                'email' => 'superadmin@ispbills.com',
                'password' => Hash::make('password'),
                'tenant_id' => null,
                'operator_level' => 10,
                'operator_type' => 'super_admin',
                'is_active' => true,
                'role' => $superAdminRole,
            ],
            [
                'name' => 'ISP Admin',
                'email' => 'admin@ispbills.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 20,
                'operator_type' => 'admin',
                'is_active' => true,
                'role' => $adminRole,
            ],
            [
                'name' => 'John Operator',
                'email' => 'operator@ispbills.com',
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
        $this->command->info('');
        $this->command->info('📋 Demo Accounts');
        $this->command->info('All demo accounts use password: password');
        $this->command->info('');
        $this->command->info('Email                        Role            Level');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('developer@ispbills.com       Developer       0');
        $this->command->info('superadmin@ispbills.com      Super Admin     10');
        $this->command->info('admin@ispbills.com           Admin           20');
        $this->command->info('operator@ispbills.com        Operator        30');
        $this->command->info('suboperator@ispbills.com     Sub-Operator    40');
        $this->command->info('customer@ispbills.com        Customer        100');
        $this->command->info('');
        $this->command->info('Seed demo data with:');
        $this->command->info('  php artisan db:seed --class=DemoSeeder');
    }
}
