<?php

namespace Database\Seeders;

use App\Models\IpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Models\Olt;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates demo accounts for all role levels:
     * - Developer (level 0)
     * - Super Admin (level 10)
     * - Admin/ISP Owner (level 20)
     * - Operator (level 30)
     * - Sub-Operator (level 40)
     * - Customer (level 100)
     *
     * It also seeds demo packages, PPP profiles, NAS, MikroTik routers, OLTs, and IP pools.
     */
    public function run(): void
    {
        $this->command->info('Starting DemoSeeder...');

        // Ensure roles are seeded first
        $this->call(RoleSeeder::class);

        // Create demo tenant (Super Admin's tenant)
        $tenant = $this->createDemoTenant();

        // Create demo Developer account (system-level, no tenant)
        $developer = $this->createDeveloper();

        // Create demo Super Admin account (tenancy anchor)
        $superAdmin = $this->createSuperAdmin($developer);

        // Create demo Admin account (ISP owner)
        $admin = $this->createAdmin($tenant, $superAdmin);

        // Create demo Operator account
        $operator = $this->createOperator($tenant, $admin);

        // Create demo Sub-Operator account
        $subOperator = $this->createSubOperator($tenant, $operator);

        // Create demo packages
        $packages = $this->createDemoPackages($tenant);

        // Create demo Customer account (PPP user)
        $customer = $this->createCustomer($tenant, $subOperator, $packages[0]);

        // Seed demo network resources (Admin-only resources)
        $this->seedDemoNetworkResources($tenant);

        $this->command->info('');
        $this->command->info('✅ DemoSeeder completed successfully!');
        $this->command->info('');
        $this->command->info('📋 Demo Account Credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Developer:    developer@ispbills.com / password');
        $this->command->info('Super Admin:  superadmin@ispbills.com / password');
        $this->command->info('Admin:        admin@ispbills.com / password');
        $this->command->info('Operator:     operator@ispbills.com / password');
        $this->command->info('Sub-Operator: suboperator@ispbills.com / password');
        $this->command->info('Customer:     customer@ispbills.com / password');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Create demo tenant for the ISP
     */
    private function createDemoTenant(): Tenant
    {
        $tenant = Tenant::firstOrCreate(
            ['domain' => 'demo-isp.local'],
            [
                'name' => 'Demo ISP',
                'subdomain' => 'demo',
                'database' => null,
                'settings' => [
                    'currency' => 'BDT',
                    'timezone' => 'Asia/Dhaka',
                    'billing_day' => 1,
                ],
                'status' => 'active',
            ]
        );

        $this->command->info("✓ Demo tenant created: {$tenant->name}");

        return $tenant;
    }

    /**
     * Create demo Developer account (system-level authority)
     */
    private function createDeveloper(): User
    {
        $developerRole = Role::where('slug', 'developer')->first();

        $developer = User::firstOrCreate(
            ['email' => 'developer@ispbills.com'],
            [
                'name' => 'Demo Developer',
                'password' => Hash::make('password'),
                'tenant_id' => null, // Developers are not bound to any tenant
                'operator_level' => 0,
                'operator_type' => 'developer',
                'is_active' => true,
                'activated_at' => now(),
            ]
        );

        if ($developerRole) {
            $developer->roles()->syncWithoutDetaching([
                $developerRole->id => ['tenant_id' => null],
            ]);
        }

        $this->command->info("✓ Demo Developer created: {$developer->email}");

        return $developer;
    }

    /**
     * Create demo Super Admin account (tenancy anchor)
     */
    private function createSuperAdmin(User $createdBy): User
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@ispbills.com'],
            [
                'name' => 'Demo Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null, // Super Admin manages tenants
                'operator_level' => 10,
                'operator_type' => 'super_admin',
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $createdBy->id,
            ]
        );

        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([
                $superAdminRole->id => ['tenant_id' => null],
            ]);
        }

        $this->command->info("✓ Demo Super Admin created: {$superAdmin->email}");

        return $superAdmin;
    }

    /**
     * Create demo Admin account (ISP owner)
     */
    private function createAdmin(Tenant $tenant, User $createdBy): User
    {
        $adminRole = Role::where('slug', 'admin')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@ispbills.com'],
            [
                'name' => 'Demo Admin (ISP Owner)',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 20,
                'operator_type' => 'admin',
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $createdBy->id,
            ]
        );

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([
                $adminRole->id => ['tenant_id' => $tenant->id],
            ]);
        }

        // Update tenant's created_by to link it to Super Admin
        $tenant->update(['created_by' => $createdBy->id]);

        $this->command->info("✓ Demo Admin created: {$admin->email}");

        return $admin;
    }

    /**
     * Create demo Operator account
     */
    private function createOperator(Tenant $tenant, User $createdBy): User
    {
        $operatorRole = Role::where('slug', 'operator')->first();

        $operator = User::firstOrCreate(
            ['email' => 'operator@ispbills.com'],
            [
                'name' => 'Demo Operator',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 30,
                'operator_type' => 'operator',
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $createdBy->id,
            ]
        );

        if ($operatorRole) {
            $operator->roles()->syncWithoutDetaching([
                $operatorRole->id => ['tenant_id' => $tenant->id],
            ]);
        }

        $this->command->info("✓ Demo Operator created: {$operator->email}");

        return $operator;
    }

    /**
     * Create demo Sub-Operator account
     */
    private function createSubOperator(Tenant $tenant, User $createdBy): User
    {
        $subOperatorRole = Role::where('slug', 'sub-operator')->first();

        $subOperator = User::firstOrCreate(
            ['email' => 'suboperator@ispbills.com'],
            [
                'name' => 'Demo Sub-Operator',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 40,
                'operator_type' => 'sub_operator',
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $createdBy->id,
            ]
        );

        if ($subOperatorRole) {
            $subOperator->roles()->syncWithoutDetaching([
                $subOperatorRole->id => ['tenant_id' => $tenant->id],
            ]);
        }

        $this->command->info("✓ Demo Sub-Operator created: {$subOperator->email}");

        return $subOperator;
    }

    /**
     * Create demo packages
     */
    private function createDemoPackages(Tenant $tenant): array
    {
        $packages = [
            [
                'name' => 'Demo Basic 5Mbps',
                'description' => 'Demo package for basic browsing and email',
                'bandwidth_up' => 1024,
                'bandwidth_down' => 5120,
                'price' => 500.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Demo Standard 10Mbps',
                'description' => 'Demo package for streaming and remote work',
                'bandwidth_up' => 2048,
                'bandwidth_down' => 10240,
                'price' => 800.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Demo Premium 20Mbps',
                'description' => 'Demo package for power users',
                'bandwidth_up' => 5120,
                'bandwidth_down' => 20480,
                'price' => 1200.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
        ];

        $createdPackages = [];
        foreach ($packages as $packageData) {
            $package = ServicePackage::firstOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );
            $createdPackages[] = $package;
        }

        $this->command->info('✓ Demo packages created: ' . count($createdPackages));

        return $createdPackages;
    }

    /**
     * Create demo Customer account (PPP user)
     */
    private function createCustomer(Tenant $tenant, User $createdBy, ServicePackage $package): User
    {
        $customerRole = Role::where('slug', 'customer')->first();

        $customer = User::firstOrCreate(
            ['email' => 'customer@ispbills.com'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'operator_level' => 100,
                'operator_type' => null,
                'service_package_id' => $package->id,
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => $createdBy->id,
            ]
        );

        if ($customerRole) {
            $customer->roles()->syncWithoutDetaching([
                $customerRole->id => ['tenant_id' => $tenant->id],
            ]);
        }

        $this->command->info("✓ Demo Customer created: {$customer->email}");

        return $customer;
    }

    /**
     * Seed demo network resources (Admin-only resource management)
     */
    private function seedDemoNetworkResources(Tenant $tenant): void
    {
        // Create demo MikroTik router
        $mikrotik = MikrotikRouter::firstOrCreate(
            ['ip_address' => '192.168.1.1', 'tenant_id' => $tenant->id],
            [
                'name' => 'Demo MikroTik Router',
                'api_port' => 8728,
                'username' => 'admin',
                'password' => 'demo-password',
                'status' => 'active',
            ]
        );
        $this->command->info("✓ Demo MikroTik router created: {$mikrotik->name}");

        // Create demo PPP profile
        MikrotikProfile::firstOrCreate(
            ['name' => 'demo-5mbps', 'router_id' => $mikrotik->id],
            [
                'router_id' => $mikrotik->id,
                'local_address' => '10.0.0.1',
                'remote_address' => '10.0.0.0/24',
                'rate_limit' => '5M/5M',
                'session_timeout' => 0,
                'idle_timeout' => 0,
            ]
        );
        $this->command->info('✓ Demo PPP profile created');

        // Create demo NAS
        $nas = Nas::firstOrCreate(
            ['nas_name' => 'demo-nas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Demo NAS Server',
                'short_name' => 'demo-nas',
                'type' => 'other',
                'ports' => 1812,
                'secret' => 'demo-secret',
                'server' => '192.168.1.10',
                'community' => 'public',
                'description' => 'Demo NAS for testing',
                'status' => 'active',
                'tenant_id' => $tenant->id,
            ]
        );
        $this->command->info("✓ Demo NAS created: {$nas->name}");

        // Create demo OLT
        // WARNING: Using demo credentials for testing purposes only.
        // DO NOT use these credentials in production environments.
        $olt = Olt::firstOrCreate(
            ['ip_address' => '192.168.1.20'],
            [
                'name' => 'Demo OLT Device',
                'ip_address' => '192.168.1.20',
                'port' => 23,
                'management_protocol' => 'telnet',
                'username' => 'admin',
                'password' => 'demo-password',
                'snmp_community' => 'public',
                'snmp_version' => 'v2c',
                'model' => 'Generic OLT',
                'location' => 'Demo Data Center',
                'status' => 'active',
            ]
        );

        // Set tenant_id separately as it's not in the fillable array
        if ($olt->tenant_id !== $tenant->id) {
            $olt->tenant_id = $tenant->id;
            $olt->save();
        }

        $this->command->info("✓ Demo OLT created: {$olt->name}");

        // Create demo IP pool
        $ipPool = IpPool::firstOrCreate(
            ['name' => 'demo-pool'],
            [
                'description' => 'Demo IP pool for testing',
                'start_ip' => '10.10.0.1',
                'end_ip' => '10.10.0.254',
                'gateway' => '10.10.0.254',
                'dns_servers' => '8.8.8.8,8.8.4.4',
                'vlan_id' => 100,
                'status' => 'active',
            ]
        );

        // Set tenant_id separately as it's not in the fillable array
        if ($ipPool->tenant_id !== $tenant->id) {
            $ipPool->tenant_id = $tenant->id;
            $ipPool->save();
        }

        $this->command->info("✓ Demo IP pool created: {$ipPool->name}");
    }
}
