<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Reseller hierarchy
 * Tests parent/child account relationships
 */
class ResellerHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_customer_can_have_child_accounts(): void
    {
        $parent = User::factory()->create([
            'is_reseller' => true,
        ]);

        $child1 = User::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $child2 = User::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals(2, $parent->childAccounts()->count());
        $this->assertTrue($parent->childAccounts->contains($child1));
        $this->assertTrue($parent->childAccounts->contains($child2));
    }

    public function test_child_customer_can_access_parent_account(): void
    {
        $parent = User::factory()->create([
            'is_reseller' => true,
        ]);

        $child = User::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertNotNull($child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_reseller_can_view_child_accounts(): void
    {
        $reseller = User::factory()->create([
            'is_reseller' => true,
            'reseller_status' => 'approved',
            'operator_level' => 50,
        ]);

        $child = User::factory()->create([
            'parent_id' => $reseller->id,
        ]);

        $this->actingAs($reseller);

        // Test policy: reseller can manage child account
        $this->assertTrue($reseller->can('manageChildAccount', $child));
    }

    public function test_child_customer_cannot_view_parent_account_details(): void
    {
        $parent = User::factory()->create([
            'is_reseller' => true,
            'operator_level' => 50,
        ]);

        $child = User::factory()->create([
            'parent_id' => $parent->id,
            'operator_level' => 90, // Regular customer
        ]);

        $this->actingAs($child);

        // Child cannot manage parent
        $this->assertFalse($child->can('manageChildAccount', $parent));
    }

    public function test_reseller_can_have_nested_hierarchy(): void
    {
        $grandparent = User::factory()->create([
            'is_reseller' => true,
        ]);

        $parent = User::factory()->create([
            'parent_id' => $grandparent->id,
            'is_reseller' => true,
        ]);

        $child = User::factory()->create([
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals(1, $grandparent->childAccounts()->count());
        $this->assertEquals(1, $parent->childAccounts()->count());
        $this->assertEquals(0, $child->childAccounts()->count());
    }

    public function test_reseller_commission_rate_can_be_set(): void
    {
        $reseller = User::factory()->create([
            'is_reseller' => true,
            'commission_rate' => 0.15, // 15%
        ]);

        $this->assertEquals(0.15, $reseller->commission_rate);
    }

    public function test_only_approved_resellers_can_access_reseller_dashboard(): void
    {
        $pendingReseller = User::factory()->create([
            'is_reseller' => true,
            'reseller_status' => 'pending',
        ]);

        $approvedReseller = User::factory()->create([
            'is_reseller' => true,
            'reseller_status' => 'approved',
        ]);

        // Create a child account for approved reseller
        $child = User::factory()->create([
            'parent_id' => $approvedReseller->id,
        ]);

        $this->actingAs($pendingReseller);
        $this->assertFalse($pendingReseller->can('viewResellerDashboard', User::class));

        $this->actingAs($approvedReseller);
        $this->assertTrue($approvedReseller->can('viewResellerDashboard', User::class));
    }
}
