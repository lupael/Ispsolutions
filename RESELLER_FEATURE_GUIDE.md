# Operator & Sub-Operator Hierarchy Guide

## Overview

The ISP Solution implements a hierarchical business model using **Operators** and **Sub-Operators** roles. This allows ISPs to create a multi-level management structure where Operators can manage Sub-Operators, and both can manage customer accounts within their scope.

> **Note**: The terms "Operator" and "Sub-Operator" are customizable. Admins can rename these roles to "Reseller", "Partner", "Agent", "Distributor", or any other term that fits their business model using the Role Label Settings feature.

## Key Features

- **Role-Based Hierarchy**: Clear role levels (Admin → Operator → Sub-Operator → Customer)
- **Parent-Child Relationships**: Hierarchical account structure for managing subordinates and customers
- **Custom Role Labels**: Admins can rename roles to match their business terminology
- **Permission Management**: Granular role-based permissions with special permissions support
- **Tenant Isolation**: Multi-tenancy support with strict data isolation
- **Operator Permissions**: Fine-grained control over operator capabilities

## Architecture

### Role Hierarchy

The system implements 8 distinct roles with clear hierarchy levels:

| Role | Level | Purpose |
|------|-------|---------|
| **Developer** | 0 | Supreme authority across all tenants |
| **Super Admin** | 10 | Tenancy owner, manages Admins |
| **Admin** | 20 | ISP Owner, manages Operators and Sub-Operators |
| **Operator** | 30 | Manages Sub-Operators and customers |
| **Sub-Operator** | 40 | Manages only their own customers |
| **Manager** | 50 | View-only with task-specific permissions |
| **Accountant** | 70 | Financial view-only access |
| **Staff** | 80 | Support staff with limited permissions |
| **Customer** | ~~100~~ | End users (identified by `is_subscriber` flag, not operator_level) |

> **Note**: The Customer role with `operator_level = 100` is **deprecated** since version 1.0. Customers are now identified by the `is_subscriber` flag instead of operator_level.

### Database Schema

```php
// users table - relevant operator/hierarchy fields
'parent_id' => 'bigint unsigned nullable',     // Reference to parent operator
'operator_level' => 'integer default 100',      // Role hierarchy level (see above) - DEPRECATED for customers
'operator_type' => 'string nullable',           // Role type identifier
'is_subscriber' => 'boolean',                   // TRUE for customers (replaces operator_level = 100)
'manager_id' => 'foreignId nullable',           // Assigned manager
'disabled_menus' => 'json nullable',            // Custom menu restrictions
'tenant_id' => 'bigint unsigned nullable',      // Tenant isolation
'created_by' => 'bigint unsigned nullable',     // Creator tracking
```

### Models

- **User Model** (`app/Models/User.php`):
  - `parent()`: BelongsTo relationship to parent operator
  - `childAccounts()`: HasMany relationship to child operators/customers
  - `isReseller()`: Check if user has child accounts
  - `hasPermission($permission)`: Check if user has specific permission
  - `hasRole($role)`: Check if user has specific role
  - `hasAnyRole($roles)`: Check if user has any of the specified roles

- **Role Model** (`app/Models/Role.php`):
  - Stores role definitions with permissions array
  - Each role has a `level` for hierarchy enforcement
  - Permissions stored as JSON array

- **RoleLabelSetting Model** (`app/Models/RoleLabelSetting.php`):
  - Allows Admins to customize role display names
  - Tenant-specific custom labels

- **OperatorPermission Model** (`app/Models/OperatorPermission.php`):
  - Operator-specific permission management

- **SpecialPermission Model** (`app/Models/SpecialPermission.php`):
  - Time-limited or resource-specific permissions
  - Supports expiry dates

### Policies

- **CustomerPolicy** (`app/Policies/CustomerPolicy.php`):
  - `viewAny()`: Check if user can view customers list
  - `view()`: Check if user can view specific customer (checks hierarchy)
  - `create()`: Check if user can create customers
  - `update()`: Check if user can update specific customer
  - `delete()`: Check if user can delete customer
  - `isInHierarchy()`: Check if customer is in user's management hierarchy

- **OperatorPolicy** (`app/Policies/OperatorPolicy.php`):
  - Enforces operator/sub-operator management hierarchy
  - Validates role level permissions

## Creating Operators and Sub-Operators

### Admin Creates Operator

Admins (level 20) can create Operators through the user management interface:

```php
// Creating an Operator account
$operator = User::create([
    'name' => 'John Partner',
    'email' => 'john@partner.com',
    'password' => Hash::make('password'),
    'operator_level' => 30,  // Operator level
    'operator_type' => 'operator',
    'tenant_id' => auth()->user()->tenant_id,
    'created_by' => auth()->id(),
    'parent_id' => null,  // Direct under Admin
]);

// Assign Operator role
$operatorRole = Role::where('slug', 'operator')->first();
$operator->roles()->attach($operatorRole, ['tenant_id' => $operator->tenant_id]);
```

### Operator Creates Sub-Operator

Operators (level 30) can create Sub-Operators under their management:

```php
// Creating a Sub-Operator account
$subOperator = User::create([
    'name' => 'Jane Agent',
    'email' => 'jane@agent.com',
    'password' => Hash::make('password'),
    'operator_level' => 40,  // Sub-Operator level
    'operator_type' => 'sub-operator',
    'tenant_id' => auth()->user()->tenant_id,
    'created_by' => auth()->id(),
    'parent_id' => auth()->id(),  // Under the Operator
]);

// Assign Sub-Operator role
$subOperatorRole = Role::where('slug', 'sub-operator')->first();
$subOperator->roles()->attach($subOperatorRole, ['tenant_id' => $subOperator->tenant_id]);
```

### Creating Customer Accounts

Both Operators and Sub-Operators can create customer accounts:

```php
// When creating a customer as an operator
$customer = User::create([
    'name' => 'Customer Name',
    'email' => 'customer@example.com',
    'password' => Hash::make('password'),
    'is_subscriber' => true,  // Mark as customer (preferred method)
    // Note: operator_level = 100 is DEPRECATED. Use is_subscriber flag instead.
    'parent_id' => auth()->id(),  // Link to operator/sub-operator
    'tenant_id' => auth()->user()->tenant_id,
    'created_by' => auth()->id(),
    // ... other customer fields (package, billing info, etc.)
]);
```

### Viewing Child Accounts

```php
// Get all child accounts (operators/customers under current user)
$operator = auth()->user();
$children = $operator->childAccounts;

// Count active children
$activeChildren = $operator->childAccounts()
    ->where('is_active', true)
    ->count();

// Get only sub-operators under an operator
$subOperators = $operator->childAccounts()
    ->where('operator_level', 40)
    ->get();

// Get only customers under an operator
$customers = $operator->childAccounts()
    ->where('is_subscriber', true)
    ->get();

// Check if user has child accounts (is acting as parent)
if ($operator->isReseller()) {
    // This operator has child accounts
}
```

## Customizing Role Labels

Admins can customize role display names to match their business model:

### Setting Custom Labels

```php
use App\Models\RoleLabelSetting;

// Rename "Operator" to "Reseller" for the tenant
RoleLabelSetting::updateOrCreate(
    [
        'tenant_id' => auth()->user()->tenant_id,
        'original_role' => 'operator',
    ],
    [
        'custom_label' => 'Reseller',
    ]
);

// Rename "Sub-Operator" to "Agent"
RoleLabelSetting::updateOrCreate(
    [
        'tenant_id' => auth()->user()->tenant_id,
        'original_role' => 'sub-operator',
    ],
    [
        'custom_label' => 'Agent',
    ]
);
```

### Using Custom Labels in UI

```php
// Get the display name for a role
$roleLabel = RoleLabelSetting::getLabel(auth()->user()->tenant_id, 'operator');
// Returns "Reseller" if custom label is set, otherwise "Operator"

// In Blade templates
{{ RoleLabelSetting::getLabel(auth()->user()->tenant_id, 'operator') }}
```

## Permission Management

### Role Permissions

Each role comes with predefined permissions. Here's what each level can do:

#### Operator Permissions (Level 30)

Operators can:
- ✅ Create and manage Sub-Operators
- ✅ View, create, and update customers
- ✅ Suspend/activate customers (if granted)
- ✅ View and process billing for their customers
- ✅ Receive payments from customers
- ✅ View available packages (set by Admin)
- ✅ Send SMS/notifications to their customers
- ✅ Generate reports for their segment
- ✅ Manage complaints from their customers
- ✅ Use recharge cards
- ✅ View their own commission (if custom billing implemented)

Operators cannot:
- ❌ View accounts of other Operators
- ❌ Delete customer accounts
- ❌ Access admin functions
- ❌ Create or modify packages
- ❌ Manage network devices (NAS, OLT, Router)
- ❌ Access system settings
- ❌ Create other Operators

#### Sub-Operator Permissions (Level 40)

Sub-Operators can:
- ✅ View only their own customers
- ✅ Create new customers under their account
- ✅ Update their own customers
- ✅ View billing for their customers
- ✅ Process billing for their customers
- ✅ Receive payments from their customers
- ✅ View available packages
- ✅ View their own commission
- ✅ Generate reports for their own customers

Sub-Operators cannot:
- ❌ Create other Sub-Operators
- ❌ View customers of other Sub-Operators
- ❌ Access customers of their parent Operator (unless explicitly assigned)
- ❌ Access any admin or operator functions
- ❌ Modify packages or pricing

### Checking Permissions

```php
// Check if user has specific permission
if (auth()->user()->hasPermission('customers.create')) {
    // User can create customers
}

// Check if user has specific role
if (auth()->user()->hasRole('operator')) {
    // User is an operator
}

// Check if user has any of the specified roles
if (auth()->user()->hasAnyRole(['operator', 'sub-operator'])) {
    // User is either operator or sub-operator
}

// In controllers - check hierarchy
use App\Policies\CustomerPolicy;

if ($user->can('view', $customer)) {
    // User can view this customer (policy checks hierarchy)
}

// In Blade templates
@can('update', $customer)
    <!-- Show edit options -->
@endcan

// Check operator level directly
if (auth()->user()->operator_level <= 30) {
    // User is Operator level or higher
}
```

### Special Permissions

The system supports time-limited and resource-specific permissions:

```php
use App\Models\SpecialPermission;

// Grant temporary permission to access all customers for 30 days
SpecialPermission::create([
    'user_id' => $user->id,
    'permission' => 'access_all_customers',
    'expires_at' => now()->addDays(30),
]);

// Check if user has special permission
if (SpecialPermission::hasPermission($user, 'access_all_customers')) {
    // User has special permission (not expired)
}
```

### Operator-Specific Permissions

```php
use App\Models\OperatorPermission;

// Grant operator permission to manage specific resource
OperatorPermission::create([
    'user_id' => $operator->id,
    'permission' => 'manage_packages',
    'is_active' => true,
]);

// Check operator permission
if (OperatorPermission::hasPermission($operator, 'manage_packages')) {
    // Operator has this specific permission
}
```

## Nested Hierarchy

The system supports multi-level hierarchies:

```
Admin (Level 20)
  ├── Operator A (Level 30)
  │   ├── Sub-Operator A1 (Level 40)
  │   │   ├── Customer 1
  │   │   └── Customer 2
  │   ├── Sub-Operator A2 (Level 40)
  │   │   ├── Customer 3
  │   │   └── Customer 4
  │   └── Customer 5 (Direct)
  └── Operator B (Level 30)
      └── Customer 6 (Direct)
```

### Working with Hierarchy

```php
// Get all descendants (recursive)
function getAllDescendants(User $user): Collection
{
    $descendants = collect();
    
    foreach ($user->childAccounts as $child) {
        $descendants->push($child);
        if ($child->childAccounts()->exists()) {
            $descendants = $descendants->merge(getAllDescendants($child));
        }
    }
    
    return $descendants;
}

// Get hierarchy path to root
function getHierarchyPath(User $user): Collection
{
    $path = collect([$user]);
    $current = $user;
    
    while ($current->parent) {
        $path->push($current->parent);
        $current = $current->parent;
    }
    
    return $path->reverse();
}

// Check if user is in another user's hierarchy
function isInHierarchy(User $parent, User $child): bool
{
    $current = $child;
    
    while ($current->parent_id) {
        if ($current->parent_id === $parent->id) {
            return true;
        }
        $current = $current->parent;
    }
    
    return false;
}
```

## Best Practices

### Security

1. **Validate Role Levels**: Always check `operator_level` before granting access
2. **Verify Parent-Child Relationships**: Ensure operators can only access their hierarchy
3. **Tenant Isolation**: Always filter by `tenant_id` to prevent cross-tenant access
4. **Audit Trail**: Log all operator actions using `created_by` field
5. **Rate Limiting**: Implement rate limits on operator APIs
6. **Check Policies**: Use Laravel policies for authorization checks

```php
// Always check tenant isolation
$customers = User::where('is_subscriber', true)
    ->where('tenant_id', auth()->user()->tenant_id)
    ->get();

// Always check hierarchy
if (!$this->isInHierarchy(auth()->user(), $customer)) {
    abort(403, 'Unauthorized access to customer');
}

// Use policies
$this->authorize('view', $customer);
```

### Performance

1. **Cache Hierarchy Data**: Cache child counts and hierarchy paths
2. **Eager Load Relationships**: Use `with('childAccounts', 'parent')` to avoid N+1 queries
3. **Index Database**: Ensure indexes on `parent_id`, `operator_level`, `tenant_id` columns
4. **Paginate Results**: Always paginate large result sets
5. **Use Scopes**: Create query scopes for common hierarchy queries

```php
// Efficient hierarchy query
$operator = User::with(['childAccounts.childAccounts'])
    ->find($operatorId);

// Use scopes
class User extends Authenticatable
{
    public function scopeOperators($query)
    {
        return $query->where('operator_level', 30);
    }
    
    public function scopeInTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}

// Usage
$operators = User::operators()
    ->inTenant(auth()->user()->tenant_id)
    ->paginate(20);
```

### User Experience

1. **Clear Role Labels**: Use custom labels that match business terminology
2. **Hierarchy Visualization**: Show clear hierarchy in UI
3. **Help Documentation**: Include contextual help for operators
4. **Dashboard Analytics**: Provide meaningful metrics for operators
5. **Menu Customization**: Use `disabled_menus` to customize operator interface

## Troubleshooting

### Operator Cannot See Child Accounts

**Possible Causes:**
- `parent_id` not set correctly on child accounts
- Tenant isolation preventing access
- Permission issues
- Policy checks failing

**Solution:**
```php
// Check operator level
$operator->operator_level === 30  // Should be 30 for Operator

// Verify child accounts
$operator->childAccounts()->count()

// Check tenant match
$operator->tenant_id === $child->tenant_id

// Check permissions
$operator->hasPermission('customers.view')

// Debug policy
$operator->can('view', $customer)
```

### Cannot Create Sub-Operator

**Possible Causes:**
- User is not an Operator (level 30)
- Missing permissions
- Tenant mismatch

**Solution:**
```php
// Verify user is Operator
if (auth()->user()->operator_level !== 30) {
    return 'Only Operators can create Sub-Operators';
}

// Check permission
if (!auth()->user()->hasPermission('sub-operators.create')) {
    return 'Missing permission';
}

// Ensure same tenant
$subOperator->tenant_id = auth()->user()->tenant_id;
```

### Hierarchy Not Working Correctly

**Possible Causes:**
- `parent_id` not set during creation
- Circular references in hierarchy
- Soft-deleted parent users

**Solution:**
```php
// Always set parent_id when creating child accounts
$child->parent_id = auth()->id();

// Check for circular references
if ($this->wouldCreateCircularReference($parent, $child)) {
    throw new Exception('Cannot create circular hierarchy');
}

// Include soft-deleted in queries if needed
User::withTrashed()->find($parentId);
```

### Permission Denied Errors

**Possible Causes:**
- Role not assigned correctly
- Missing permissions in role definition
- Policy checks failing
- Special permissions expired

**Solution:**
```php
// Verify role assignment
$user->roles()->where('tenant_id', $user->tenant_id)->get();

// Check role permissions
$role = Role::where('slug', 'operator')->first();
dd($role->permissions);

// Grant special permission
SpecialPermission::create([
    'user_id' => $user->id,
    'permission' => 'needed_permission',
    'expires_at' => null, // No expiry
]);

// Check middleware
// Ensure CheckRole and CheckPermission middleware are applied
```

## Database Migrations

### Key Migrations

```bash
# Operator fields migration
database/migrations/2026_01_17_210100_add_operator_fields_to_users_table.php

# Role system
database/migrations/2026_01_16_205100_create_roles_table.php
database/migrations/2026_01_16_205200_create_role_user_table.php

# Custom role labels
database/migrations/2026_01_21_202207_create_role_label_settings_table.php

# Special permissions
database/migrations/2026_01_21_181000_create_special_permissions_table.php

# Operator permissions
database/migrations/2026_01_21_193500_create_operator_permissions_table.php
```

### Seeding Roles

```bash
# Seed the 8 standard roles
php artisan db:seed --class=RoleSeeder

# This creates:
# - Developer (level 0)
# - Super Admin (level 10)
# - Admin (level 20)
# - Operator (level 30)
# - Sub-Operator (level 40)
# - Manager (level 50)
# - Accountant (level 70)
# - Staff (level 80)
# - Customer (level 100 - DEPRECATED, use is_subscriber flag instead)
```

## Key Files Reference

### Models
- `app/Models/User.php` - User model with hierarchy methods
- `app/Models/Role.php` - Role definitions
- `app/Models/RoleLabelSetting.php` - Custom role labels
- `app/Models/SpecialPermission.php` - Time-limited permissions
- `app/Models/OperatorPermission.php` - Operator-specific permissions

### Policies
- `app/Policies/CustomerPolicy.php` - Customer authorization
- `app/Policies/OperatorPolicy.php` - Operator management authorization

### Middleware
- `app/Http/Middleware/CheckRole.php` - Role validation
- `app/Http/Middleware/CheckPermission.php` - Permission validation

### Seeders
- `database/seeders/RoleSeeder.php` - Role definitions and permissions

## Implementing Commission/Billing (Optional)

The base system does not include commission tracking, but you can implement it using the existing hierarchy:

### Custom Implementation Example

```php
namespace App\Services;

class OperatorCommissionService
{
    /**
     * Calculate revenue from child accounts
     */
    public function calculateChildRevenue(User $operator, $startDate, $endDate)
    {
        return Payment::whereIn('user_id', function ($query) use ($operator) {
                $query->select('id')
                    ->from('users')
                    ->where('parent_id', $operator->id)
                    ->where('is_subscriber', true);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
    }
    
    /**
     * Calculate commission based on custom rate
     */
    public function calculateCommission(User $operator, $amount, $rate = 0.10)
    {
        return $amount * $rate;
    }
    
    /**
     * Get comprehensive report
     */
    public function generateReport(User $operator, $startDate, $endDate)
    {
        $revenue = $this->calculateChildRevenue($operator, $startDate, $endDate);
        $commission = $this->calculateCommission($operator, $revenue);
        
        return [
            'operator' => $operator,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_revenue' => $revenue,
            'commission_rate' => 0.10,
            'commission_amount' => $commission,
            'child_count' => $operator->childAccounts()->where('is_subscriber', true)->count(),
        ];
    }
}
```

## Future Enhancements

The following features can be added to enhance the operator/hierarchy system:

- [ ] Commission tracking and automated payments
- [ ] Performance analytics dashboards for operators
- [ ] Operator-specific branding/white-labeling
- [ ] Training modules and certification for operators
- [ ] Automated tier adjustments based on performance
- [ ] Operator application/approval workflow
- [ ] Revenue sharing and multi-level commission distribution
- [ ] Operator portal mobile app
- [ ] Marketing materials and resources for operators
- [ ] Operator performance leaderboards

## Related Documentation

- [ROLE_HIERARCHY_SECURITY_FIXES.md](ROLE_HIERARCHY_SECURITY_FIXES.md) - Security fixes for role hierarchy
- [PANEL_README.md](PANEL_README.md) - Panel access and permissions
- User Model documentation (inline comments in `app/Models/User.php`)
- Role Seeder (`database/seeders/RoleSeeder.php`)

---

**Important Notes:**

1. **"Reseller" terminology**: Throughout this documentation, "Operator" and "Sub-Operator" are the technical role names. Your business may refer to these as "Resellers", "Partners", "Agents", "Distributors", etc. Use the Role Label Settings feature to customize the display names.

2. **Commission System**: The base system does not include automated commission tracking. If you need commission features, implement them using the existing hierarchy structure (see "Implementing Commission/Billing" section).

3. **Hierarchy Depth**: While the system supports unlimited hierarchy depth, consider limiting it to 2-3 levels for simplicity and performance.

4. **Tenant Isolation**: Always ensure tenant isolation is respected in all queries and operations to maintain data security.

For additional support or questions, please contact the development team or refer to the inline documentation in the codebase.
