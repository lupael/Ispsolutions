<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pagination Fix Verification Tests
 *
 * These tests verify that controller methods return proper paginator objects
 * to prevent "Call to a member function hasPages() on array" errors.
 */
class PaginationFixesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that AdminController packages route returns paginator.
     */
    public function test_admin_packages_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('panel.admin.packages'));

        $response->assertStatus(200);
        $response->assertViewHas('packages', function ($packages) {
            return $packages instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that AdminController deleted customers route returns paginator.
     */
    public function test_admin_deleted_customers_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('panel.admin.customers.deleted'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that AdminController import requests route returns paginator.
     */
    public function test_admin_import_requests_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('panel.admin.customers.import-requests'));

        $response->assertStatus(200);
        $response->assertViewHas('importRequests', function ($importRequests) {
            return $importRequests instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that DeveloperController logs route returns paginator.
     */
    public function test_developer_logs_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('developer');

        $response = $this->actingAs($user)->get(route('panel.developer.logs'));

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that DeveloperController customers index returns paginator and stats.
     */
    public function test_developer_customers_index_returns_paginator_and_stats(): void
    {
        $user = User::factory()->create();
        $user->assignRole('developer');

        $response = $this->actingAs($user)->get(route('panel.developer.customers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers instanceof LengthAwarePaginator;
        });
        $response->assertViewHas('stats');
    }

    /**
     * Test that DeveloperController search returns paginator for empty query.
     */
    public function test_developer_search_without_query_returns_all_customers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('developer');

        $response = $this->actingAs($user)->get(route('panel.developer.customers.search'));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers instanceof LengthAwarePaginator;
        });
        $response->assertViewHas('stats');
    }

    /**
     * Test that DeveloperController search with query returns paginator.
     */
    public function test_developer_search_with_query_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('developer');

        $response = $this->actingAs($user)->get(route('panel.developer.customers.search', ['query' => 'test']));

        $response->assertStatus(200);
        $response->assertViewHas('customers', function ($customers) {
            return $customers instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that SubResellerController commission returns paginator and summary.
     */
    public function test_sub_reseller_commission_returns_paginator_and_summary(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sub-reseller');

        $response = $this->actingAs($user)->get(route('panel.sub-reseller.commission'));

        $response->assertStatus(200);
        $response->assertViewHas('transactions', function ($transactions) {
            return $transactions instanceof LengthAwarePaginator;
        });
        $response->assertViewHas('summary');
    }

    /**
     * Test that SubResellerController packages returns paginator.
     */
    public function test_sub_reseller_packages_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sub-reseller');

        $response = $this->actingAs($user)->get(route('panel.sub-reseller.packages'));

        $response->assertStatus(200);
        $response->assertViewHas('packages', function ($packages) {
            return $packages instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that ManagerController complaints returns paginator.
     */
    public function test_manager_complaints_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get(route('panel.manager.complaints.index'));

        $response->assertStatus(200);
        $response->assertViewHas('complaints', function ($complaints) {
            return $complaints instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that OperatorController complaints returns paginator.
     */
    public function test_operator_complaints_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');

        $response = $this->actingAs($user)->get(route('panel.operator.complaints.index'));

        $response->assertStatus(200);
        $response->assertViewHas('complaints', function ($complaints) {
            return $complaints instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that CardDistributorController commissions returns paginator.
     */
    public function test_card_distributor_commissions_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('card-distributor');

        $response = $this->actingAs($user)->get(route('panel.card-distributor.commissions'));

        $response->assertStatus(200);
        $response->assertViewHas('commissions', function ($commissions) {
            return $commissions instanceof LengthAwarePaginator;
        });
    }

    /**
     * Test that paginator has required methods.
     */
    public function test_paginator_has_required_methods(): void
    {
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1);

        $this->assertTrue(method_exists($paginator, 'hasPages'));
        $this->assertTrue(method_exists($paginator, 'links'));
        $this->assertFalse($paginator->hasPages());
    }
}
