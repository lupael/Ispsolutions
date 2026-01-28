<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Collection;

/**
 * Package Hierarchy Service
 * 
 * Manages package hierarchy operations including:
 * - Building package tree structures
 * - Finding upgrade paths
 * - Managing parent/child relationships
 */
class PackageHierarchyService
{
    /**
     * Build a hierarchical tree of packages
     * 
     * @param Collection|null $packages Optional collection of packages, will fetch all if not provided
     * @return Collection Tree structure with nested children
     */
    public function buildTree(?Collection $packages = null): Collection
    {
        if ($packages === null) {
            $packages = Package::with(['childPackages', 'users'])
                ->orderBy('price', 'asc')
                ->get();
        }

        // Build tree structure
        $tree = collect();
        $indexed = $packages->keyBy('id');

        foreach ($packages as $package) {
            if ($package->parent_package_id === null) {
                // Root package
                $packageData = $this->buildPackageNode($package, $indexed);
                $tree->push($packageData);
            }
        }

        return $tree;
    }

    /**
     * Build a package node with its children recursively
     */
    protected function buildPackageNode(Package $package, Collection $indexed, int $level = 0): array
    {
        $node = [
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'customer_count' => $package->customer_count, // Use attribute accessor, not method
            'status' => $package->status,
            'description' => $package->description,
            'bandwidth_upload' => $package->bandwidth_upload,
            'bandwidth_download' => $package->bandwidth_download,
            'validity_days' => $package->validity_days,
            'parent_id' => $package->parent_package_id,
            'children' => [],
            'level' => $level,
        ];

        // Add children recursively
        $children = $indexed->filter(fn($p) => $p->parent_package_id === $package->id);
        foreach ($children as $child) {
            $childNode = $this->buildPackageNode($child, $indexed, $level + 1);
            $node['children'][] = $childNode;
        }

        return $node;
    }

    /**
     * Get all possible upgrade paths from a given package
     * 
     * @param Package $currentPackage
     * @return Collection Collection of packages that are valid upgrades
     */
    public function getUpgradePaths(Package $currentPackage): Collection
    {
        // Upgrade paths include:
        // 1. Sibling packages (same parent) with higher price
        // 2. Child packages
        // 3. Parent's sibling packages with higher price
        
        $upgrades = collect();

        // Add sibling packages with higher price
        if ($currentPackage->parent_package_id) {
            $siblings = Package::where('parent_package_id', $currentPackage->parent_package_id)
                ->where('id', '!=', $currentPackage->id)
                ->where('price', '>', $currentPackage->price)
                ->where('status', 'active')
                ->get();
            $upgrades = $upgrades->merge($siblings);
        }

        // Add child packages
        $children = $currentPackage->childPackages()
            ->where('status', 'active')
            ->get();
        $upgrades = $upgrades->merge($children);

        // Add packages with higher price from same master package
        if ($currentPackage->master_package_id) {
            $masterSiblings = Package::where('master_package_id', $currentPackage->master_package_id)
                ->where('id', '!=', $currentPackage->id)
                ->where('price', '>', $currentPackage->price)
                ->where('status', 'active')
                ->get();
            $upgrades = $upgrades->merge($masterSiblings);
        }

        return $upgrades->unique('id')->sortBy('price');
    }

    /**
     * Calculate the upgrade cost and benefits
     * 
     * @param Package $from Current package
     * @param Package $to Target package
     * @return array Upgrade details
     */
    public function calculateUpgrade(Package $from, Package $to): array
    {
        $priceDifference = $to->price - $from->price;
        $speedIncrease = $to->bandwidth_download - $from->bandwidth_download;
        $validityIncrease = $to->validity_days - $from->validity_days;

        return [
            'from_package' => [
                'id' => $from->id,
                'name' => $from->name,
                'price' => $from->price,
                'speed_download' => $from->bandwidth_download,
                'validity_days' => $from->validity_days,
            ],
            'to_package' => [
                'id' => $to->id,
                'name' => $to->name,
                'price' => $to->price,
                'speed_download' => $to->bandwidth_download,
                'validity_days' => $to->validity_days,
            ],
            'differences' => [
                'price' => $priceDifference,
                'price_percentage' => $from->price > 0 ? ($priceDifference / $from->price) * 100 : 0,
                'speed_download' => $speedIncrease,
                'speed_percentage' => $from->bandwidth_download > 0 
                    ? ($speedIncrease / $from->bandwidth_download) * 100 
                    : 0,
                'validity_days' => $validityIncrease,
            ],
            'is_upgrade' => $priceDifference > 0 || $speedIncrease > 0,
        ];
    }

    /**
     * Get all root packages (packages without parents)
     * 
     * @return Collection
     */
    public function getRootPackages(): Collection
    {
        return Package::whereNull('parent_package_id')
            ->with(['childPackages'])
            ->where('status', 'active')
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * Get package family (parent and all siblings)
     * 
     * @param Package $package
     * @return Collection
     */
    public function getPackageFamily(Package $package): Collection
    {
        if ($package->parent_package_id === null) {
            // This is a root package, return itself and children
            return collect([$package])->merge($package->childPackages);
        }

        // Get parent and all siblings
        $parent = $package->parentPackage;
        $siblings = Package::where('parent_package_id', $package->parent_package_id)
            ->where('id', '!=', $package->id)
            ->get();

        return collect([$parent])->merge($siblings)->push($package);
    }

    /**
     * Check if package B is a descendant of package A
     * 
     * @param Package $ancestor
     * @param Package $descendant
     * @return bool
     */
    public function isDescendant(Package $ancestor, Package $descendant): bool
    {
        if ($descendant->parent_package_id === null) {
            return false;
        }

        if ($descendant->parent_package_id === $ancestor->id) {
            return true;
        }

        $parent = $descendant->parentPackage;
        return $parent ? $this->isDescendant($ancestor, $parent) : false;
    }
}
