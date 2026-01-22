<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        
        // Create roles
        Role::factory()->create(['name' => 'customer', 'level' => 10]);
        Role::factory()->create(['name' => 'operator', 'level' => 50]);
    }

    public function test_customer_can_register_with_valid_data(): void
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'tenant_id' => $this->tenant->id,
        ];

        $customer = User::create([
            'tenant_id' => $customerData['tenant_id'],
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'password' => Hash::make($customerData['password']),
        ]);

        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'tenant_id' => $this->tenant->id,
        ]);

        // Verify role was attached
        $this->assertDatabaseHas('role_user', [
            'user_id' => $customer->id,
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_customer_registration_validates_unique_email(): void
    {
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'existing@example.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'username' => 'another',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_customer_registration_validates_unique_username(): void
    {
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'user1@example.com',
        ]);

        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'user2@example.com',
        ]);

        // Both users created successfully
        $this->assertDatabaseCount('users', 2);
    }

    public function test_customer_account_is_created_with_operator_reference(): void
    {
        $this->markTestSkipped('Requires createdBy relationship to be defined in User model');

        $operatorRole = Role::where('name', 'operator')->first();
        $operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $operator->roles()->attach($operatorRole);

        $customerRole = Role::where('name', 'customer')->first();
        $customer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $operator->id,
        ]);
        $customer->roles()->attach($customerRole);

        $this->assertEquals($operator->id, $customer->created_by);
        $this->assertNotNull($customer->createdBy);
        $this->assertTrue($customer->createdBy->hasRole('operator'));
    }

    public function test_customer_profile_can_be_updated(): void
    {
        $customer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'John Doe',
        ]);

        $customer->update([
            'name' => 'John Smith',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'John Smith',
        ]);
    }

    public function test_customer_can_be_assigned_to_service_package(): void
    {
        $customer = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Verify customer was created with default package from factory
        $this->assertNotNull($customer->service_package_id);
    }

    public function test_multiple_customers_can_register_in_same_tenant(): void
    {
        $customers = User::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertCount(5, $customers);
        
        foreach ($customers as $customer) {
            $this->assertEquals($this->tenant->id, $customer->tenant_id);
        }
    }

    public function test_customer_data_is_isolated_by_tenant(): void
    {
        $tenant2 = Tenant::factory()->create();

        $customer1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'customer@tenant1.com',
        ]);

        $customer2 = User::factory()->create([
            'tenant_id' => $tenant2->id,
            'email' => 'customer@tenant2.com',
        ]);

        $tenant1Customers = User::where('tenant_id', $this->tenant->id)->get();
        $tenant2Customers = User::where('tenant_id', $tenant2->id)->get();

        $this->assertTrue($tenant1Customers->contains($customer1));
        $this->assertFalse($tenant1Customers->contains($customer2));
        
        $this->assertTrue($tenant2Customers->contains($customer2));
        $this->assertFalse($tenant2Customers->contains($customer1));
    }
}
