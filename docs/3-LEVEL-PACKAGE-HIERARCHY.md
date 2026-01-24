# 3-Level Package Hierarchy System

## Overview

The 3-Level Package Hierarchy is a comprehensive system that allows ISP businesses to manage packages across three tiers:

1. **Master Packages** - Base package templates created by developers/super-admins
2. **Operator Package Rates** - Operator-specific pricing based on master packages
3. **Packages** - Final customer packages derived from operator rates

This system provides flexibility for multi-level operators while maintaining pricing control and consistency.

## Architecture

```
Developer/Super-Admin
    ↓ Creates
Master Package (Base Template)
    ├─ Name, Description
    ├─ Speed (Upload/Download)
    ├─ Volume Limit
    ├─ Validity Days
    ├─ Base Price (Maximum)
    └─ Status & Visibility
        ↓ Assigns to
Operator Package Rate
    ├─ Operator ID
    ├─ Operator Price (≤ Base Price)
    ├─ Commission %
    └─ Status
        ↓ Creates
Package (Customer-facing)
    ├─ Inherits from Master Package
    ├─ Custom pricing
    └─ Sold to customers
```

## Features

### 1. Master Package Management

**Accessible by:** Developer, Super-Admin

**Features:**
- Create base package templates
- Set maximum pricing (base price)
- Configure speed, volume, and validity
- Public/Private visibility control
- Trial package flag (prevents deletion/price modification)
- Track usage statistics (operators, customers, revenue)

**Routes:**
```php
GET    /panel/developer/master-packages           # List all
GET    /panel/developer/master-packages/create    # Create form
POST   /panel/developer/master-packages           # Store
GET    /panel/developer/master-packages/{id}      # View details
GET    /panel/developer/master-packages/{id}/edit # Edit form
PUT    /panel/developer/master-packages/{id}      # Update
DELETE /panel/developer/master-packages/{id}      # Delete
```

### 2. Operator Assignment

**Accessible by:** Developer, Super-Admin

**Features:**
- Assign master packages to specific operators
- Set operator-specific pricing (must be ≤ base price)
- Configure commission percentages
- Track margin calculations
- Low-margin warnings (< 10%)

**Routes:**
```php
GET    /panel/developer/master-packages/{id}/assign        # Assignment form
POST   /panel/developer/master-packages/{id}/assign        # Store assignment
DELETE /panel/developer/master-packages/{id}/operators/{rateId}  # Remove assignment
GET    /panel/developer/master-packages/{id}/stats         # Usage statistics
```

### 3. Operator Package Rate Management

**Accessible by:** Admin, Operator

**Features:**
- View available master packages
- Create operator-specific rates
- Edit existing rates (within base price limits)
- Real-time margin calculations
- Suggested retail price calculator
- Assign rates to sub-operators

**Routes:**
```php
GET    /panel/admin/operator-packages              # List available & configured
GET    /panel/admin/operator-packages/create       # Create form
POST   /panel/admin/operator-packages              # Store
GET    /panel/admin/operator-packages/{id}/edit    # Edit form
PUT    /panel/admin/operator-packages/{id}         # Update
DELETE /panel/admin/operator-packages/{id}         # Delete
```

## Validation Rules

### Master Package
```php
- name: required, max:100
- base_price: required, numeric, min:0
- speed_upload: nullable, integer, min:0
- speed_download: nullable, integer, min:0
- volume_limit: nullable, integer, min:0
- validity_days: required, integer, min:1
- visibility: required, in:public,private
- status: required, in:active,inactive
- is_trial_package: boolean
```

### Operator Package Rate
```php
- master_package_id: required, exists:master_packages,id
- operator_id: required, exists:users,id
- operator_price: required, numeric, min:0, max:base_price
- commission_percentage: nullable, numeric, min:0, max:100
```

### Business Rules

1. **Pricing Hierarchy:**
   - Operator price ≤ Master base price
   - Sub-operator price ≤ Operator price
   - Retail price (suggested) = Operator price × (1 + margin%)

2. **Trial Packages:**
   - Cannot be deleted
   - Cannot modify pricing
   - Useful for promotional campaigns

3. **Deletion Protection:**
   - Cannot delete master packages with assigned operators
   - Cannot delete master packages with active customers
   - Cannot delete operator rates with active packages
   - Migration path must be provided before deletion

4. **Margin Warnings:**
   - Warning if margin < 10%
   - Suggested retail price calculator (default 20% margin)
   - Real-time calculation in UI

## Database Schema

### master_packages
```sql
id                  BIGINT PRIMARY KEY
tenant_id           BIGINT NULL
created_by          BIGINT NOT NULL
name                VARCHAR(100)
description         TEXT NULL
speed_upload        INT NULL (kbps)
speed_download      INT NULL (kbps)
volume_limit        BIGINT NULL (MB)
validity_days       INT DEFAULT 30
base_price          DECIMAL(10,2)
visibility          ENUM('public', 'private')
is_trial_package    BOOLEAN DEFAULT FALSE
status              ENUM('active', 'inactive')
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
- tenant_id
- created_by
- status
- visibility
- is_trial_package
```

### operator_package_rates (updated)
```sql
id                    BIGINT PRIMARY KEY
tenant_id             BIGINT NULL
operator_id           BIGINT NOT NULL
package_id            BIGINT NULL (legacy)
master_package_id     BIGINT NULL
operator_price        DECIMAL(10,2) NULL
custom_price          DECIMAL(10,2) (legacy)
commission_percentage DECIMAL(5,2)
status                ENUM('active', 'inactive')
assigned_by           BIGINT NULL
created_at            TIMESTAMP
updated_at            TIMESTAMP

FOREIGN KEYS:
- operator_id → users.id
- master_package_id → master_packages.id
- assigned_by → users.id

INDEXES:
- tenant_id
- operator_id
- master_package_id
- status
```

### packages (updated)
```sql
[existing fields...]
master_package_id        BIGINT NULL
operator_package_rate_id BIGINT NULL

FOREIGN KEYS:
- master_package_id → master_packages.id
- operator_package_rate_id → operator_package_rates.id
```

## Usage Examples

### Example 1: Developer Creates Master Package

```php
// Developer creates a base package template
$masterPackage = MasterPackage::create([
    'tenant_id' => null, // Global package
    'created_by' => $developer->id,
    'name' => '100 Mbps Fiber',
    'description' => 'High-speed fiber internet',
    'speed_upload' => 102400, // 100 Mbps in kbps
    'speed_download' => 102400,
    'validity_days' => 30,
    'base_price' => 50.00, // Maximum price operators can charge
    'visibility' => 'public',
    'is_trial_package' => false,
    'status' => 'active',
]);
```

### Example 2: Assign to Operator

```php
// Developer assigns package to operator with pricing
OperatorPackageRate::create([
    'tenant_id' => $operator->tenant_id,
    'operator_id' => $operator->id,
    'master_package_id' => $masterPackage->id,
    'operator_price' => 45.00, // Operator's cost (≤ $50)
    'commission_percentage' => 10.00,
    'status' => 'active',
    'assigned_by' => $developer->id,
]);

// System calculates:
// Margin = (45 - 50) / 50 * 100 = -10% (negative means operator pays less)
// Suggested retail = 45 * 1.20 = $54.00
```

### Example 3: Operator Creates Customer Package

```php
// Operator creates package for customers
$package = Package::create([
    'tenant_id' => $operator->tenant_id,
    'operator_id' => $operator->id,
    'master_package_id' => $masterPackage->id,
    'operator_package_rate_id' => $operatorRate->id,
    'name' => '100M Fiber - Premium',
    'description' => 'Ultra-fast fiber for homes',
    'price' => 60.00, // Retail price
    'bandwidth_upload' => 102400,
    'bandwidth_download' => 102400,
    'validity_days' => 30,
    'status' => 'active',
]);

// Revenue breakdown:
// Customer pays: $60.00
// Operator cost: $45.00
// Operator profit: $15.00 (25% margin)
// Developer revenue: $50.00 (base price)
```

## UI/UX Features

### Real-time Price Calculation

All forms include JavaScript for real-time margin calculation:

```javascript
// Shows margin percentage
// Warns if margin < 10%
// Calculates suggested retail price
// Updates as user types
```

### Visual Indicators

- 🟢 Green badge: Active status
- 🟡 Yellow badge: Low margin warning
- 🔵 Blue badge: Public visibility
- ⚪ Gray badge: Inactive/Private
- 🟠 Orange badge: Trial package

### Statistics Dashboard

Master package show page displays:
- Number of operators using the package
- Total customers across all operators
- Total revenue generated
- Individual operator rates and margins

## Security Considerations

1. **Role-Based Access:**
   - Master packages: Developer, Super-Admin only
   - Operator rates: Admin, Operator only
   - Proper middleware on all routes

2. **Tenant Isolation:**
   - All queries filtered by tenant_id
   - Super-admins see only their tenant + global
   - Developers see all

3. **Price Validation:**
   - Server-side validation prevents price manipulation
   - Cannot exceed base price
   - Cannot modify trial package pricing

4. **Deletion Protection:**
   - Prevents orphaned records
   - Requires migration before deletion
   - Clear error messages

## Testing

Run basic tests:

```bash
# Test routes
php artisan route:list --path=master-packages
php artisan route:list --path=operator-packages

# Test models
php artisan tinker
>>> $mp = new App\Models\MasterPackage();
>>> $mp->getFillable()
>>> $mp = App\Models\MasterPackage::factory()->create();
>>> $mp->canDelete()
```

## Troubleshooting

### Issue: Operator cannot see master packages

**Solution:** Check visibility setting (public vs private) and tenant_id

### Issue: Cannot delete master package

**Solution:** Check for assigned operators and customers. Provide migration path.

### Issue: Price validation fails

**Solution:** Ensure operator_price ≤ master base_price

### Issue: Margin calculation incorrect

**Solution:** Margin = ((operator_price - base_price) / base_price) × 100

## Migration Guide

### From Legacy System

1. Existing packages continue to work
2. Create master packages for new structure
3. Migrate operators gradually
4. Legacy fields maintained for compatibility:
   - `operator_package_rates.package_id`
   - `operator_package_rates.custom_price`

## Future Enhancements

- [ ] Bulk operator assignment
- [ ] Package templates with multiple tiers
- [ ] Automated pricing suggestions based on market data
- [ ] Historical pricing tracking
- [ ] Package comparison tools
- [ ] Export/Import functionality
- [ ] Advanced reporting and analytics

## Support

For issues or questions:
1. Check this documentation
2. Review code comments in models/controllers
3. Check migration files for schema details
4. Test in development environment first
