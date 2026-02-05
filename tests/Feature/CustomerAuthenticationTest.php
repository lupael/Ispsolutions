<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function a_customer_can_view_the_customer_dashboard()
    {
        $customer = User::factory()->create(['is_subscriber' => true]);

        $response = $this->actingAs($customer)->get('/panel/customer/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function an_operator_cannot_view_the_customer_dashboard()
    {
        $operator = User::factory()->create(['is_subscriber' => false]);
        $operator->assignRole('admin');

        $response = $this->actingAs($operator)->get('/panel/customer/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function a_guest_is_redirected_from_the_customer_dashboard()
    {
        $response = $this->get('/panel/customer/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function a_logged_in_customer_is_redirected_to_the_customer_dashboard()
    {
        $customer = User::factory()->create(['is_subscriber' => true]);

        $response = $this->actingAs($customer)->get('/');

        $response->assertRedirect('/panel/customer/dashboard');
    }
}
