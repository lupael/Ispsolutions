<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerCreationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected ServicePackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create roles
        Role::factory()->create(['name' => 'admin', 'slug' => 'admin', 'level' => 20]);
        Role::factory()->create(['name' => 'customer', 'slug' => 'customer', 'level' => 100]);

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20,
        ]);
        $this->admin->assignRole('admin');

        // Create a test package
        $this->package = ServicePackage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Package',
            'price' => 100,
        ]);
    }

    public function test_admin_can_create_customer_and_it_appears_in_customers_list(): void
    {
        // Act as admin
        $this->actingAs($this->admin);

        // Create customer data matching the form
        $customerData = [
            'username' => 'testcustomer',
            'password' => 'password123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
            'customer_name' => 'Test Customer',
            'email' => 'test@customer.com',
            'phone' => '1234567890',
            'address' => '123 Test Street',
        ];

        // Make POST request to create customer
        $response = $this->post(route('panel.admin.customers.store'), $customerData);

        // Assert redirect to customers list
        $response->assertRedirect(route('panel.admin.customers.index'));
        $response->assertSessionHas('success', 'Customer created successfully.');

        // Assert customer was created in users table
        $this->assertDatabaseHas('users', [
            'username' => 'testcustomer',
            'name' => 'Test Customer',
            'email' => 'test@customer.com',
            'phone' => '1234567890',
            'operator_level' => 100, // Customer level
            'tenant_id' => $this->tenant->id,
            'service_type' => 'pppoe',
            'status' => 'active',
        ]);

        // Get the created customer
        $customer = User::where('username', 'testcustomer')->first();

        // Assert customer has the correct role
        $this->assertTrue($customer->hasRole('customer'));

        // Assert customer has correct attributes
        $this->assertEquals('testcustomer', $customer->username);
        $this->assertEquals('Test Customer', $customer->name);
        $this->assertEquals(100, $customer->operator_level);
        $this->assertEquals($this->package->id, $customer->service_package_id);
        $this->assertTrue($customer->is_active);
        $this->assertNotNull($customer->activated_at);
        $this->assertEquals($this->admin->id, $customer->created_by);
    }

    public function test_customer_created_by_admin_has_radius_password(): void
    {
        $this->actingAs($this->admin);

        $customerData = [
            'username' => 'radiususer',
            'password' => 'mypassword123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
        ];

        $this->post(route('panel.admin.customers.store'), $customerData);

        $customer = User::where('username', 'radiususer')->first();

        // Assert radius_password is stored as plain text for RADIUS
        $this->assertEquals('mypassword123', $customer->radius_password);

        // Assert password is hashed for app login
        $this->assertNotEquals('mypassword123', $customer->password);
        $this->assertTrue(password_verify('mypassword123', $customer->password));
    }

    public function test_customer_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        // Missing required fields
        $response = $this->post(route('panel.admin.customers.store'), []);

        $response->assertSessionHasErrors(['username', 'password', 'service_type', 'package_id', 'status']);
    }

    public function test_customer_creation_validates_unique_username(): void
    {
        $this->actingAs($this->admin);

        // Create first customer
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'username' => 'existinguser',
            'operator_level' => 100,
        ]);

        // Try to create customer with same username
        $customerData = [
            'username' => 'existinguser',
            'password' => 'password123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
        ];

        $response = $this->post(route('panel.admin.customers.store'), $customerData);

        $response->assertSessionHasErrors(['username']);
    }

    public function test_customer_creation_uses_username_as_fallback_for_name(): void
    {
        $this->actingAs($this->admin);

        $customerData = [
            'username' => 'fallbackuser',
            'password' => 'password123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
            // No customer_name provided
        ];

        $this->post(route('panel.admin.customers.store'), $customerData);

        $customer = User::where('username', 'fallbackuser')->first();

        // Assert username is used as name when customer_name is not provided
        $this->assertEquals('fallbackuser', $customer->name);
    }

    public function test_customer_creation_generates_email_if_not_provided(): void
    {
        $this->actingAs($this->admin);

        $customerData = [
            'username' => 'noemailuser',
            'password' => 'password123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
            // No email provided
        ];

        $this->post(route('panel.admin.customers.store'), $customerData);

        $customer = User::where('username', 'noemailuser')->first();

        // Assert auto-generated email
        $this->assertEquals('noemailuser@local.customer', $customer->email);
    }

    public function test_customer_creation_isolates_by_tenant(): void
    {
        $this->actingAs($this->admin);

        $customerData = [
            'username' => 'tenantcustomer',
            'password' => 'password123',
            'service_type' => 'pppoe',
            'package_id' => $this->package->id,
            'status' => 'active',
        ];

        $this->post(route('panel.admin.customers.store'), $customerData);

        $customer = User::where('username', 'tenantcustomer')->first();

        // Assert customer belongs to admin's tenant
        $this->assertEquals($this->tenant->id, $customer->tenant_id);
    }
}
