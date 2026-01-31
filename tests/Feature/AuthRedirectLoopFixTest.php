<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthRedirectLoopFixTest extends TestCase
{
    /**
     * Test that the root route redirects guests to login.
     */
    public function test_root_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that the login page is accessible to guests.
     */
    public function test_login_page_accessible_to_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test that authenticated user with valid role redirects properly from root.
     * This test ensures no redirect loop occurs.
     */
    public function test_authenticated_user_with_role_redirects_to_dashboard(): void
    {
        // Create a mock user with the customer role
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['hasRole'])
            ->getMock();

        $user->method('hasRole')
            ->willReturnCallback(function ($role) {
                return $role === 'customer';
            });

        $user->id = 1;

        // Act as this user
        $this->actingAs($user);

        // Access root route - should redirect to customer dashboard
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('panel.customer.dashboard'));
    }

    /**
     * Test that authenticated user without role gets logged out with error.
     * This test ensures the redirect loop is fixed.
     */
    public function test_authenticated_user_without_role_gets_logged_out(): void
    {
        // Create a mock user without any valid role
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['hasRole'])
            ->getMock();

        $user->method('hasRole')
            ->willReturn(false);

        $user->id = 2;

        // Act as this user
        $this->actingAs($user);

        // Access root route - should logout and redirect to login with error
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test that login redirects authenticated users away from login page.
     */
    public function test_login_page_redirects_authenticated_users(): void
    {
        // Create a mock user with customer role
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['hasRole'])
            ->getMock();

        $user->method('hasRole')
            ->willReturnCallback(function ($role) {
                return $role === 'customer';
            });

        $user->id = 3;

        // Act as this user
        $this->actingAs($user);

        // Try to access login page
        $response = $this->get('/login');

        // Guest middleware should redirect authenticated users
        $response->assertStatus(302);
    }
}
