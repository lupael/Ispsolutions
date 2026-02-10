# Package Hierarchy Guide

## Overview

The Package Hierarchy feature enables ISPs to organize packages in a parent-child structure, allowing for package inheritance, upgrade paths, and better package management.

## Key Features

- **Parent-Child Relationships**: Packages can have parent and child relationships
- **Attribute Inheritance**: Child packages can inherit settings from their parent
- **Upgrade Paths**: Define which packages customers can upgrade to
- **Package Comparison**: Compare features between packages
- **Flexible Overrides**: Child packages can override parent settings

## Architecture

### Database Schema

```php
// packages table additions
'parent_package_id' => 'bigint unsigned nullable',  // Reference to parent package

// Migration
Schema::table('packages', function (Blueprint $table) {
    $table->foreignId('parent_package_id')->nullable()->after('id')
        ->constrained('packages')->onDelete('set null');
    $table->index('parent_package_id');
});
```

### Model Relationships

```php
// Package Model

/**
 * Get the parent package
 */
public function parentPackage(): BelongsTo
{
    return $this->belongsTo(Package::class, 'parent_package_id');
}

/**
 * Get child packages
 */
public function childPackages(): HasMany
{
    return $this->hasMany(Package::class, 'parent_package_id');
}

/**
 * Check if this package has a parent
 */
public function hasParent(): bool
{
    return $this->parent_package_id !== null;
}

/**
 * Check if this package has children
 */
public function hasChildren(): bool
{
    return $this->childPackages()->exists();
}
```

## Creating Package Hierarchies

### Example 1: Basic Hierarchy

```
Internet Plans (Root)
├── Basic Plans
│   ├── Home Basic 5 Mbps
│   └── Home Basic 10 Mbps
├── Standard Plans
│   ├── Home Standard 20 Mbps
│   └── Home Standard 50 Mbps
└── Premium Plans
    ├── Home Premium 100 Mbps
    └── Home Premium 200 Mbps
```

### Creating the Hierarchy

```php
// Create parent package
$internetPlans = Package::create([
    'name' => 'Internet Plans',
    'description' => 'Root category for all internet packages',
    'parent_package_id' => null,
    'status' => 'active',
    // ... other fields
]);

// Create child category
$basicPlans = Package::create([
    'name' => 'Basic Plans',
    'parent_package_id' => $internetPlans->id,
    'bandwidth_download' => 10,  // Default for category
    'bandwidth_upload' => 5,
    'status' => 'active',
]);

// Create actual packages
$basic5mbps = Package::create([
    'name' => 'Home Basic 5 Mbps',
    'parent_package_id' => $basicPlans->id,
    'bandwidth_download' => 5,
    'bandwidth_upload' => 2,
    'price' => 25.00,
    'validity_days' => 30,
    'status' => 'active',
]);
```

## Package Inheritance

### How Inheritance Works

Child packages inherit attributes from their parent when the child's attribute is null or not set:

```php
// Parent package
$parent = Package::create([
    'name' => 'Standard Plans',
    'bandwidth_download' => 50,
    'bandwidth_upload' => 25,
    'validity_days' => 30,
    'billing_type' => 'monthly',
]);

// Child inherits all from parent
$child1 = Package::create([
    'name' => 'Standard Plus',
    'parent_package_id' => $parent->id,
    'price' => 45.00,
    // bandwidth_download, bandwidth_upload, validity_days inherited
]);

// Child overrides some attributes
$child2 = Package::create([
    'name' => 'Standard Pro',
    'parent_package_id' => $parent->id,
    'bandwidth_download' => 100,  // Override
    'price' => 55.00,
    // bandwidth_upload and validity_days still inherited
]);
```

### Using Inherited Attributes

```php
// Get effective values (considering inheritance)
$effectiveBandwidth = $package->getEffectiveBandwidthDownload();
$effectiveUpload = $package->getEffectiveBandwidthUpload();
$effectivePrice = $package->getEffectivePrice();
$effectiveValidity = $package->getEffectiveValidity();

// Generic inheritance getter
$description = $package->getInheritedAttribute('description', 'No description');
```

### Inheritance Chain

For deeply nested hierarchies, inheritance flows down the chain:

```
Grandparent (speed: 100)
  └── Parent (speed: null → inherits 100)
      └── Child (speed: null → inherits 100 from grandparent via parent)
```

```php
public function getInheritedAttribute(string $attribute, $default = null)
{
    // If this package has the attribute, use it
    if ($this->{$attribute} !== null && $this->{$attribute} !== '') {
        return $this->{$attribute};
    }

    // Otherwise, try to inherit from parent
    if ($this->hasParent() && $this->parentPackage) {
        return $this->parentPackage->getInheritedAttribute($attribute, $default);
    }

    return $default;
}
```

## Package Upgrade Paths

### Defining Upgrade Paths

Upgrade paths are automatically determined based on:
1. **Price**: Target package price >= current package price
2. **Speed**: Target package speed >= current package speed
3. **Status**: Target package must be active

```php
// Get available upgrades for a package
$upgrades = $currentPackage->getAvailableUpgrades();

// Check if can upgrade to specific package
$canUpgrade = $currentPackage->canUpgradeTo($targetPackage);
```

### Manual Upgrade Path Definition

For custom upgrade paths, you can add a relationship:

```php
// Add to packages table
'allowed_upgrade_to' => 'json',  // Array of package IDs

// In Package model
public function allowedUpgradeTo(): Collection
{
    $ids = $this->allowed_upgrade_to ?? [];
    return Package::whereIn('id', $ids)->get();
}
```

### Using Package Upgrade Service

```php
use App\Services\PackageUpgradeService;

$upgradeService = app(PackageUpgradeService::class);

// Get upgrade options for customer
$options = $upgradeService->getUpgradeOptions($customer);

// Calculate upgrade cost
$cost = $upgradeService->calculateUpgradeCost($customer, $targetPackage);

// Request upgrade
$request = $upgradeService->requestUpgrade(
    $customer,
    $targetPackage,
    'Customer wants faster speed'
);

// Approve and process upgrade
$result = $upgradeService->approveUpgrade($request);
```

## Package Comparison

### Comparing Two Packages

```php
use App\Services\PackageUpgradeService;

$upgradeService = app(PackageUpgradeService::class);

$comparison = $upgradeService->comparePackages($package1, $package2);

/*
Returns:
[
    'name' => [
        'package1' => 'Basic 10 Mbps',
        'package2' => 'Standard 50 Mbps',
    ],
    'price' => [
        'package1' => 25.00,
        'package2' => 50.00,
        'difference' => 25.00,
    ],
    'download_speed' => [
        'package1' => 10,
        'package2' => 50,
        'difference' => 40,
    ],
    // ... more comparisons
]
*/
```

### In Blade Templates

```blade
<div class="package-comparison">
    <table>
        <thead>
            <tr>
                <th>Feature</th>
                <th>{{ $package1->name }}</th>
                <th>{{ $package2->name }}</th>
                <th>Difference</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Price</td>
                <td>${{ $package1->price }}</td>
                <td>${{ $package2->price }}</td>
                <td class="{{ $package2->price > $package1->price ? 'text-red' : 'text-green' }}">
                    ${{ abs($package2->price - $package1->price) }}
                </td>
            </tr>
            <tr>
                <td>Download Speed</td>
                <td>{{ $package1->bandwidth_download }} Mbps</td>
                <td>{{ $package2->bandwidth_download }} Mbps</td>
                <td>{{ $package2->bandwidth_download - $package1->bandwidth_download }} Mbps</td>
            </tr>
        </tbody>
    </table>
</div>
```

## Displaying Package Hierarchy

### Tree View

```blade
<div class="package-tree">
    @foreach($rootPackages as $package)
        <div class="package-node">
            <div class="package-item">
                <span class="package-name">{{ $package->name }}</span>
                <span class="customer-count">{{ $package->customer_count }} customers</span>
            </div>
            
            @if($package->hasChildren())
                <div class="package-children" style="margin-left: 20px;">
                    @foreach($package->childPackages as $child)
                        @include('partials.package-node', ['package' => $child])
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
```

### Breadcrumb Navigation

```blade
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @php
            $breadcrumbs = [];
            $current = $package;
            while ($current) {
                array_unshift($breadcrumbs, $current);
                $current = $current->parentPackage;
            }
        @endphp
        
        @foreach($breadcrumbs as $crumb)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                @if($loop->last)
                    {{ $crumb->name }}
                @else
                    <a href="{{ route('packages.show', $crumb) }}">{{ $crumb->name }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
```

## Advanced Use Cases

### Package Families

Group related packages into families:

```php
// Family: Residential Internet
$residential = Package::create(['name' => 'Residential Internet']);

    // Tier: Bronze
    $bronze = Package::create([
        'name' => 'Bronze Tier',
        'parent_package_id' => $residential->id,
    ]);
        Package::create(['name' => 'Bronze 10 Mbps', 'parent_package_id' => $bronze->id]);
        Package::create(['name' => 'Bronze 20 Mbps', 'parent_package_id' => $bronze->id]);

    // Tier: Silver
    $silver = Package::create([
        'name' => 'Silver Tier',
        'parent_package_id' => $residential->id,
    ]);
        Package::create(['name' => 'Silver 50 Mbps', 'parent_package_id' => $silver->id]);
        Package::create(['name' => 'Silver 100 Mbps', 'parent_package_id' => $silver->id]);
```

### Geographic Packages

Organize packages by location:

```php
// Region: North Zone
$northZone = Package::create(['name' => 'North Zone Packages']);
    Package::create(['name' => 'North Zone Basic', 'parent_package_id' => $northZone->id]);
    Package::create(['name' => 'North Zone Pro', 'parent_package_id' => $northZone->id]);

// Region: South Zone
$southZone = Package::create(['name' => 'South Zone Packages']);
    Package::create(['name' => 'South Zone Basic', 'parent_package_id' => $southZone->id]);
    Package::create(['name' => 'South Zone Pro', 'parent_package_id' => $southZone->id]);
```

### Seasonal Packages

Create temporary promotional packages:

```php
$standardPackage = Package::find(10);

// Create promotional child
$promoPackage = Package::create([
    'name' => 'Summer Promo - ' . $standardPackage->name,
    'parent_package_id' => $standardPackage->id,
    'price' => $standardPackage->price * 0.8,  // 20% off
    'valid_until' => '2026-08-31',
]);
```

## Best Practices

### 1. Limit Hierarchy Depth

Keep hierarchies to 2-3 levels deep for simplicity:
```
✅ Good:
Root → Category → Package

❌ Too Deep:
Root → Region → Category → Subcategory → Package → Variant → Addon
```

### 2. Use Descriptive Names

```
✅ Good: "Residential - Premium - 100 Mbps"
❌ Bad: "Pkg_v3_tier2"
```

### 3. Maintain Consistent Structure

All packages in the same level should have similar attributes:

```php
// All premium packages should have:
- High bandwidth
- Low latency options
- Priority support
- Higher price point
```

### 4. Document Inheritance Rules

Clearly document which attributes inherit and which don't:

```php
// Inheritable attributes
- bandwidth_download
- bandwidth_upload
- validity_days
- billing_type

// Non-inheritable attributes
- price (always explicit)
- name (always unique)
- status (package-specific)
```

### 5. Handle Orphaned Children

When deleting a parent package:

```php
// Option 1: Set children to null parent
$package->childPackages()->update(['parent_package_id' => null]);
$package->delete();

// Option 2: Prevent deletion if has children
if ($package->hasChildren()) {
    throw new Exception('Cannot delete package with children');
}

// Option 3: Move children to grandparent
$grandparentId = $package->parent_package_id;
$package->childPackages()->update(['parent_package_id' => $grandparentId]);
$package->delete();
```

## Troubleshooting

### Inheritance Not Working

**Problem**: Child package not inheriting parent attributes

**Solution**:
```php
// Check if parent relationship exists
$child->parentPackage()->exists();

// Verify parent has the attribute
$child->parentPackage->bandwidth_download;

// Check if child attribute is null (required for inheritance)
$child->bandwidth_download === null;
```

### Circular References

**Problem**: Package A → Package B → Package A

**Prevention**:
```php
public function setParentPackageIdAttribute($value)
{
    if ($value && $this->wouldCreateCircularReference($value)) {
        throw new Exception('Circular package reference not allowed');
    }
    
    $this->attributes['parent_package_id'] = $value;
}

private function wouldCreateCircularReference($parentId): bool
{
    $parent = Package::find($parentId);
    
    while ($parent) {
        if ($parent->id === $this->id) {
            return true;
        }
        $parent = $parent->parentPackage;
    }
    
    return false;
}
```

### Upgrade Path Conflicts

**Problem**: Multiple valid upgrade paths

**Solution**:
```php
// Prioritize upgrades by:
1. Smallest price increase
2. Closest match to current speed
3. Same package family

$upgrades = $package->getAvailableUpgrades()
    ->sortBy(fn($p) => $p->price - $package->price)
    ->take(5);
```

## Performance Optimization

### Eager Loading

```php
// Load parent and children
$packages = Package::with(['parentPackage', 'childPackages'])->get();

// Load entire hierarchy
$packages = Package::with(['parentPackage.parentPackage', 'childPackages.childPackages'])->get();
```

### Caching Hierarchies

```php
use Illuminate\Support\Facades\Cache;

$hierarchy = Cache::remember('package-hierarchy', 3600, function () {
    return Package::whereNull('parent_package_id')
        ->with('childPackages.childPackages')
        ->get();
});
```

### Database Indexing

```php
Schema::table('packages', function (Blueprint $table) {
    $table->index('parent_package_id');
    $table->index(['parent_package_id', 'status']);
});
```

## API Examples

### Get Package Hierarchy

```
GET /api/packages/hierarchy

Response:
{
    "data": [
        {
            "id": 1,
            "name": "Internet Plans",
            "children": [
                {
                    "id": 2,
                    "name": "Basic Plans",
                    "children": [...]
                }
            ]
        }
    ]
}
```

### Get Upgrade Options

```
GET /api/customers/{id}/upgrade-options

Response:
{
    "current_package": {...},
    "available_upgrades": [
        {
            "id": 10,
            "name": "Standard 50 Mbps",
            "price": 50.00,
            "upgrade_cost": 25.00,
            "benefits": [...]
        }
    ]
}
```

---

For additional information or questions, please refer to the main documentation or contact the development team.
