<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive tenant isolation and role hierarchy test suite.
 * 
 * These tests validate that the security fixes prevent:
 * 1. Privilege escalation via unauthorized role creation
 * 2. Cross-tenant data access
 * 3. Unauthorized user management
 */
class TenantIsolationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_super_admin_cannot_create_operators(): void
    {
        $superAdmin = User::factory()->create(['operator_level' => 10]);
        
        // Super Admin should not be able to create an Operator (level 30)
        $this->assertFalse($superAdmin->canCreateUserWithLevel(30));
        $this->assertFalse($superAdmin->canCreateOperator());
    }

    public function test_super_admin_cannot_create_managers_staff_accountants(): void
    {
        $superAdmin = User::factory()->create(['operator_level' => 10]);
        
        // Super Admin should not be able to create these roles
        $this->assertFalse($superAdmin->canCreateUserWithLevel(50)); // Manager
        $this->assertFalse($superAdmin->canCreateUserWithLevel(70)); // Accountant
        $this->assertFalse($superAdmin->canCreateUserWithLevel(80)); // Staff
    }

    public function test_super_admin_can_only_see_users_in_own_tenants(): void
    {
        // Create two Super Admins with their own tenants
        $superAdmin1 = User::factory()->create(['operator_level' => 10]);
        $superAdmin2 = User::factory()->create(['operator_level' => 10]);

        $tenant1 = Tenant::factory()->create(['created_by' => $superAdmin1->id]);
        $tenant2 = Tenant::factory()->create(['created_by' => $superAdmin2->id]);

        // Create admins in each tenant
        $admin1 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant1->id,
        ]);
        $admin2 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant2->id,
        ]);

        // Super Admin 1 can manage admin1 but not admin2
        $this->assertTrue($superAdmin1->canManage($admin1));
        $this->assertFalse($superAdmin1->canManage($admin2));

        // Super Admin 2 can manage admin2 but not admin1
        $this->assertTrue($superAdmin2->canManage($admin2));
        $this->assertFalse($superAdmin2->canManage($admin1));
    }

    public function test_admin_cannot_see_users_from_other_tenants(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $admin1 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant1->id,
        ]);
        $admin2 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant2->id,
        ]);

        $operator1 = User::factory()->create([
            'operator_level' => 30,
            'tenant_id' => $tenant1->id,
        ]);
        $operator2 = User::factory()->create([
            'operator_level' => 30,
            'tenant_id' => $tenant2->id,
        ]);

        // Admin1 can manage operator in same tenant
        $this->assertTrue($admin1->canManage($operator1));
        
        // Admin1 cannot manage operator in different tenant
        $this->assertFalse($admin1->canManage($operator2));
    }

    public function test_admin_can_create_all_subordinate_roles_except_super_admin(): void
    {
        $admin = User::factory()->create(['operator_level' => 20]);

        // Admin cannot create Super Admin or other Admins
        $this->assertFalse($admin->canCreateUserWithLevel(10));
        $this->assertFalse($admin->canCreateUserWithLevel(20));
        $this->assertFalse($admin->canCreateSuperAdmin());
        $this->assertFalse($admin->canCreateAdmin());

        // Admin can create these roles
        $this->assertTrue($admin->canCreateUserWithLevel(30)); // Operator
        $this->assertTrue($admin->canCreateUserWithLevel(40)); // Sub-Operator
        $this->assertTrue($admin->canCreateUserWithLevel(50)); // Manager
        $this->assertTrue($admin->canCreateUserWithLevel(70)); // Accountant
        $this->assertTrue($admin->canCreateUserWithLevel(80)); // Staff
        $this->assertTrue($admin->canCreateUserWithLevel(100)); // Customer

        // Verify using convenience methods
        $this->assertTrue($admin->canCreateOperator());
        $this->assertTrue($admin->canCreateSubOperator());
        $this->assertTrue($admin->canCreateCustomer());
    }

    public function test_operator_cannot_create_managers_staff_accountants(): void
    {
        $operator = User::factory()->create(['operator_level' => 30]);

        // Operator cannot create these roles
        $this->assertFalse($operator->canCreateUserWithLevel(50)); // Manager
        $this->assertFalse($operator->canCreateUserWithLevel(70)); // Accountant
        $this->assertFalse($operator->canCreateUserWithLevel(80)); // Staff
        
        // Operator can only create Sub-Operators and Customers
        $this->assertTrue($operator->canCreateUserWithLevel(40)); // Sub-Operator
        $this->assertTrue($operator->canCreateUserWithLevel(100)); // Customer
    }

    public function test_sub_operator_can_only_create_customers(): void
    {
        $subOperator = User::factory()->create(['operator_level' => 40]);

        // Sub-Operator cannot create any role except Customer
        $this->assertFalse($subOperator->canCreateUserWithLevel(10)); // Super Admin
        $this->assertFalse($subOperator->canCreateUserWithLevel(20)); // Admin
        $this->assertFalse($subOperator->canCreateUserWithLevel(30)); // Operator
        $this->assertFalse($subOperator->canCreateUserWithLevel(40)); // Sub-Operator
        $this->assertFalse($subOperator->canCreateUserWithLevel(50)); // Manager
        $this->assertFalse($subOperator->canCreateUserWithLevel(70)); // Accountant
        $this->assertFalse($subOperator->canCreateUserWithLevel(80)); // Staff

        // Sub-Operator can only create Customers
        $this->assertTrue($subOperator->canCreateUserWithLevel(100)); // Customer
        $this->assertTrue($subOperator->canCreateCustomer());
    }

    public function test_view_only_roles_cannot_create_any_users(): void
    {
        $manager = User::factory()->create(['operator_level' => 50]);
        $accountant = User::factory()->create(['operator_level' => 70]);
        $staff = User::factory()->create(['operator_level' => 80]);

        // Verify they are view-only roles
        $this->assertTrue($manager->hasViewOnlyAccess());
        $this->assertTrue($accountant->hasViewOnlyAccess());
        $this->assertTrue($staff->hasViewOnlyAccess());

        // None of them can create any role
        foreach ([10, 20, 30, 40, 50, 70, 80, 100] as $level) {
            $this->assertFalse($manager->canCreateUserWithLevel($level));
            $this->assertFalse($accountant->canCreateUserWithLevel($level));
            $this->assertFalse($staff->canCreateUserWithLevel($level));
        }

        // Verify using convenience methods
        $this->assertFalse($manager->canCreateCustomer());
        $this->assertFalse($accountant->canCreateCustomer());
        $this->assertFalse($staff->canCreateCustomer());
    }

    public function test_developer_can_access_all_tenants(): void
    {
        $developer = User::factory()->create(['operator_level' => 0]);

        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $admin1 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant1->id,
        ]);
        $admin2 = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant2->id,
        ]);

        // Developer can manage users in any tenant
        $this->assertTrue($developer->canManage($admin1));
        $this->assertTrue($developer->canManage($admin2));
    }

    public function test_accessible_customers_respects_hierarchy(): void
    {
        $tenant = Tenant::factory()->create();

        $admin = User::factory()->create([
            'operator_level' => 20,
            'tenant_id' => $tenant->id,
        ]);

        $operator = User::factory()->create([
            'operator_level' => 30,
            'tenant_id' => $tenant->id,
            'created_by' => $admin->id,
        ]);

        $subOperator = User::factory()->create([
            'operator_level' => 40,
            'tenant_id' => $tenant->id,
            'created_by' => $operator->id,
        ]);

        // Create customers
        $operatorCustomer = User::factory()->create([
            'operator_level' => 100,
            'tenant_id' => $tenant->id,
            'created_by' => $operator->id,
        ]);

        $subOperatorCustomer = User::factory()->create([
            'operator_level' => 100,
            'tenant_id' => $tenant->id,
            'created_by' => $subOperator->id,
        ]);

        // Admin can see all customers in tenant
        $adminCustomers = $admin->accessibleCustomers()->pluck('id')->toArray();
        $this->assertContains($operatorCustomer->id, $adminCustomers);
        $this->assertContains($subOperatorCustomer->id, $adminCustomers);

        // Operator can see own customers and sub-operator customers
        $operatorCustomers = $operator->accessibleCustomers()->pluck('id')->toArray();
        $this->assertContains($operatorCustomer->id, $operatorCustomers);
        $this->assertContains($subOperatorCustomer->id, $operatorCustomers);

        // Sub-Operator can only see own customers
        $subOperatorCustomers = $subOperator->accessibleCustomers()->pluck('id')->toArray();
        $this->assertNotContains($operatorCustomer->id, $subOperatorCustomers);
        $this->assertContains($subOperatorCustomer->id, $subOperatorCustomers);
    }
}
