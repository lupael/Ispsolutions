<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_developer_can_create_super_admin(): void
    {
        $developer = User::factory()->create(['operator_level' => 0]);

        $this->assertTrue($developer->isDeveloper());
        $this->assertTrue($developer->canCreateSuperAdmin());
        $this->assertTrue($developer->canCreateUserWithLevel(10));
    }

    public function test_super_admin_can_create_admin(): void
    {
        $superAdmin = User::factory()->create(['operator_level' => 10]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->canCreateAdmin());
        $this->assertTrue($superAdmin->canCreateUserWithLevel(20));
        $this->assertFalse($superAdmin->canCreateSuperAdmin());
    }

    public function test_admin_can_create_operator(): void
    {
        $admin = User::factory()->create(['operator_level' => 20]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canCreateOperator());
        $this->assertTrue($admin->canCreateUserWithLevel(30));
        $this->assertFalse($admin->canCreateAdmin());
        $this->assertFalse($admin->canCreateSuperAdmin());
    }

    public function test_operator_can_create_sub_operator_and_customer(): void
    {
        $operator = User::factory()->create(['operator_level' => 30]);

        $this->assertTrue($operator->isOperatorRole());
        $this->assertTrue($operator->canCreateSubOperator());
        $this->assertTrue($operator->canCreateCustomer());
        $this->assertTrue($operator->canCreateUserWithLevel(40));
        $this->assertTrue($operator->canCreateUserWithLevel(100));
        $this->assertFalse($operator->canCreateOperator());
    }

    public function test_sub_operator_can_only_create_customer(): void
    {
        $subOperator = User::factory()->create(['operator_level' => 40]);

        $this->assertTrue($subOperator->isSubOperator());
        $this->assertTrue($subOperator->canCreateCustomer());
        $this->assertTrue($subOperator->canCreateUserWithLevel(100));
        $this->assertFalse($subOperator->canCreateSubOperator());
        $this->assertFalse($subOperator->canCreateOperator());
    }

    public function test_manager_has_view_only_access(): void
    {
        $manager = User::factory()->create(['operator_level' => 50]);

        $this->assertTrue($manager->isManager());
        $this->assertTrue($manager->hasViewOnlyAccess());
        $this->assertFalse($manager->canCreateCustomer());
        $this->assertFalse($manager->canCreateSubOperator());
        $this->assertFalse($manager->canCreateOperator());
    }

    public function test_staff_has_view_only_access(): void
    {
        $staff = User::factory()->create(['operator_level' => 80]);

        $this->assertTrue($staff->isStaff());
        $this->assertTrue($staff->hasViewOnlyAccess());
        $this->assertFalse($staff->canCreateCustomer());
    }

    public function test_accountant_has_view_only_access(): void
    {
        $accountant = User::factory()->create(['operator_level' => 70]);

        $this->assertTrue($accountant->isAccountant());
        $this->assertTrue($accountant->hasViewOnlyAccess());
        $this->assertFalse($accountant->canCreateCustomer());
    }

    public function test_super_admin_can_only_manage_users_in_own_tenants(): void
    {
        // Create two Super Admins
        $superAdmin1 = User::factory()->create(['operator_level' => 10]);
        $superAdmin2 = User::factory()->create(['operator_level' => 10]);

        // Create tenants for each
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

        // Super Admin 1 can manage admin in their tenant
        $this->assertTrue($superAdmin1->canManage($admin1));

        // Super Admin 1 cannot manage admin in another Super Admin's tenant
        $this->assertFalse($superAdmin1->canManage($admin2));
    }

    public function test_developer_can_manage_users_across_all_tenants(): void
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

    public function test_operator_can_manage_users_in_same_tenant_only(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $operator1 = User::factory()->create([
            'operator_level' => 30,
            'tenant_id' => $tenant1->id,
        ]);
        $operator2 = User::factory()->create([
            'operator_level' => 30,
            'tenant_id' => $tenant2->id,
        ]);

        $subOperator1 = User::factory()->create([
            'operator_level' => 40,
            'tenant_id' => $tenant1->id,
            'created_by' => $operator1->id,
        ]);
        $subOperator2 = User::factory()->create([
            'operator_level' => 40,
            'tenant_id' => $tenant2->id,
            'created_by' => $operator2->id,
        ]);

        // Operator can manage sub-operator in same tenant
        $this->assertTrue($operator1->canManage($subOperator1));

        // Operator cannot manage sub-operator in different tenant
        $this->assertFalse($operator1->canManage($subOperator2));
    }
}
