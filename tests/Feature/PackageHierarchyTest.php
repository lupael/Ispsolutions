<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Package hierarchy
 * Tests package parent/child relationships and inheritance
 */
class PackageHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_can_have_parent_package(): void
    {
        $parent = Package::factory()->create();
        $child = Package::factory()->create([
            'parent_package_id' => $parent->id,
        ]);

        $this->assertNotNull($child->parentPackage);
        $this->assertEquals($parent->id, $child->parent_package_id);
        $this->assertTrue($child->hasParent());
    }

    public function test_package_can_have_multiple_child_packages(): void
    {
        $parent = Package::factory()->create();
        
        $child1 = Package::factory()->create([
            'parent_package_id' => $parent->id,
        ]);
        
        $child2 = Package::factory()->create([
            'parent_package_id' => $parent->id,
        ]);

        $this->assertEquals(2, $parent->childPackages()->count());
        $this->assertTrue($parent->hasChildren());
    }

    public function test_child_package_inherits_parent_settings(): void
    {
        $parent = Package::factory()->create([
            'bandwidth_download' => 100,
            'bandwidth_upload' => 50,
            'validity_days' => 30,
        ]);

        $child = Package::factory()->create([
            'parent_package_id' => $parent->id,
            'bandwidth_download' => null,
            'bandwidth_upload' => null,
            'validity_days' => null,
        ]);

        // Child should inherit from parent when attributes are null
        $this->assertEquals(100, $child->getEffectiveBandwidthDownload());
        $this->assertEquals(50, $child->getEffectiveBandwidthUpload());
        $this->assertEquals(30, $child->getEffectiveValidity());
    }

    public function test_child_package_can_override_parent_settings(): void
    {
        $parent = Package::factory()->create([
            'bandwidth_download' => 100,
            'price' => 50,
        ]);

        $child = Package::factory()->create([
            'parent_package_id' => $parent->id,
            'bandwidth_download' => 200, // Override
            'price' => 75, // Override
        ]);

        // Child's explicit values should take precedence
        $this->assertEquals(200, $child->getEffectiveBandwidthDownload());
        $this->assertEquals(75, $child->getEffectivePrice());
    }

    public function test_package_upgrade_path_returns_higher_tier_packages(): void
    {
        $basic = Package::factory()->create([
            'price' => 25,
            'bandwidth_download' => 50,
            'status' => 'active',
        ]);

        $standard = Package::factory()->create([
            'price' => 50,
            'bandwidth_download' => 100,
            'status' => 'active',
        ]);

        $premium = Package::factory()->create([
            'price' => 100,
            'bandwidth_download' => 200,
            'status' => 'active',
        ]);

        $upgrades = $basic->getAvailableUpgrades();

        $this->assertTrue($upgrades->contains($standard));
        $this->assertTrue($upgrades->contains($premium));
        $this->assertFalse($upgrades->contains($basic));
    }

    public function test_package_can_upgrade_to_higher_tier(): void
    {
        $basic = Package::factory()->create([
            'price' => 25,
            'bandwidth_download' => 50,
            'status' => 'active',
        ]);

        $premium = Package::factory()->create([
            'price' => 100,
            'bandwidth_download' => 200,
            'status' => 'active',
        ]);

        $this->assertTrue($basic->canUpgradeTo($premium));
    }

    public function test_package_cannot_upgrade_to_lower_tier(): void
    {
        $premium = Package::factory()->create([
            'price' => 100,
            'bandwidth_download' => 200,
            'status' => 'active',
        ]);

        $basic = Package::factory()->create([
            'price' => 25,
            'bandwidth_download' => 50,
            'status' => 'active',
        ]);

        $this->assertFalse($premium->canUpgradeTo($basic));
    }

    public function test_package_cannot_upgrade_to_inactive_package(): void
    {
        $basic = Package::factory()->create([
            'price' => 25,
            'status' => 'active',
        ]);

        $inactive = Package::factory()->create([
            'price' => 100,
            'status' => 'inactive',
        ]);

        $this->assertFalse($basic->canUpgradeTo($inactive));
    }

    public function test_nested_package_hierarchy(): void
    {
        $grandparent = Package::factory()->create();
        
        $parent = Package::factory()->create([
            'parent_package_id' => $grandparent->id,
        ]);
        
        $child = Package::factory()->create([
            'parent_package_id' => $parent->id,
        ]);

        $this->assertEquals($grandparent->id, $parent->parent_package_id);
        $this->assertEquals($parent->id, $child->parent_package_id);
        $this->assertEquals(1, $grandparent->childPackages()->count());
        $this->assertEquals(1, $parent->childPackages()->count());
    }
}
