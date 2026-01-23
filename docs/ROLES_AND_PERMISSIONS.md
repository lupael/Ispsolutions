# Roles and Permissions - Complete Guide

**Version**: 2.0  
**Last Updated**: 2026-01-18  
**Status**: ✅ Complete

This comprehensive guide covers the multi-tenancy role-based access control (RBAC) system, data isolation, and permissions.

---

## Table of Contents

1. [Overview](#overview)
2. [Role Hierarchy](#role-hierarchy)
3. [Data Isolation Rules](#data-isolation-rules)
4. [Permission System](#permission-system)
5. [Implementation Guide](#implementation-guide)
6. [Usage Examples](#usage-examples)
7. [API Reference](#api-reference)
8. [Testing](#testing)
9. [Security](#security)
10. [Troubleshooting](#troubleshooting)

---

## Overview

The ISP Solution implements a comprehensive 12-role hierarchy with strict data isolation and automatic tenant scoping. Each role has clearly defined data access patterns and permissions.

### Key Features

- **12 distinct roles** with hierarchical levels (0-100)
- **Automatic tenant scoping** via global scopes and traits
- **Hierarchical permissions** with policy enforcement
- **Special permissions** for granular operator control
- **Controllable menus** for UI customization
- **Query-level isolation** preventing data leaks
- **Developer bypass** for system administration

### Architecture Principles

- **Lower level number = Higher privilege**
- **Automatic tenant filtering** for all queries
- **Policy-based authorization** at controller level
- **Explicit bypass** required for cross-tenant access

---

## Role Hierarchy

### Complete Role List

```
Level 0:   Developer       (Supreme Authority - All Tenants)
Level 10:  Super Admin     (Own Tenants Only)
Level 20:  Admin           (ISP Owner - Own ISP Data)
Level 30:  Operator        (Own + Sub-Operator Customers)
Level 40:  Sub-Operator    (Only Own Customers)
Level 50:  Manager         (Permission-Based)

Level 70:  Accountant      (Read-Only Financial)
Level 80:  Staff           (Permission-Based Support)
Level 100: Customer        (End User)
```

### Visual Hierarchy

```
┌─────────────────────────────────────────────────────────┐
│ Developer (0) - All Tenants                             │
│  └─ Super Admin (10) - Own Tenants                      │
│      └─ Admin (20) - ISP Data                           │
│          ├─ Operator (30) - Own + Sub-Op Customers      │
│          │   └─ Sub-Operator (40) - Own Customers       │
│          ├─ Manager (50) - Permission-Based             │
│          ├─ Accountant (70) - Financial Reports         │
│          └─ Staff (80) - Limited Support                │
│              └─ Customer (100) - Self-Service           │
└─────────────────────────────────────────────────────────┘
```

---

## Data Isolation Rules

### 1. Developer (Level 0)
**Supreme Authority - All Tenants**

#### Data Access
- ✅ **ALL tenants** (unrestricted)
- ✅ Can create and manage tenants
- ✅ Can access any user, customer, or resource across all tenants
- ✅ Source code owner with complete system access

#### Responsibilities
- Create and manage tenants
- Define subscription pricing
- Access any panel for support
- View all customer details across tenants
- Access audit logs and system logs
- Suspend/activate tenancies
- Configure global system settings
- Manage API integrations

#### Query Example
```php
// Access ALL tenants
$tenants = Tenant::all();

// Access customers across all tenants
$customers = User::withoutGlobalScope('tenant')
    ->where('operator_level', 100)
    ->get();
```

---

### 2. Super Admin (Level 10)
**Only OWN Tenants - Tenant Context Owner**

#### Data Access
- ✅ **Only OWN tenant(s)** they created/own
- ✅ Can create and manage Admins within their tenant(s)
- ✅ Can view all data within their tenant(s)
- ❌ **CANNOT** access other tenants' data
- ❌ **CANNOT** create new tenants (only Developer can)

#### Responsibilities
- Add/remove ISPs (Admins) within their tenant
- Configure billing for Admins
- Manage payment gateways for tenant
- Manage SMS gateways for tenant
- View tenant-wide logs
- Manage subscriptions for Admins
- Configure tenant settings

#### Query Example
```php
// Only own tenants
$tenants = Tenant::where('created_by', auth()->id())->get();

// All users in own tenants
$users = User::whereIn('tenant_id', $ownTenantIds)->get();
```

---

### 3. Admin (Level 20)
**ISP Owner - Own ISP Data Within Tenancy**

#### Data Access
- ✅ **All data under their ISP** within their tenant
- ✅ Can see their own customers
- ✅ Can see Operator-created customers
- ✅ Can see Sub-operator-created customers
- ✅ Can create and manage Operators
- ❌ **CANNOT** access other Admins' data
- ❌ **CANNOT** access other tenants

#### Responsibilities
- Create and manage Operators and Sub-operators
- Create and manage Managers and Staff
- Manage all customers in their ISP
- Configure packages and pricing
- Manage network devices (MikroTik, NAS, OLT, Cisco)
- Configure billing profiles
- Generate reports
- Manage recharge cards
- Control VAT settings
- Configure affiliate program
- Assign special permissions to Operators
- Control menu visibility for Operators

#### Query Example
```php
// All customers in own ISP within tenant
$customers = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_level', 100)
    ->get();

// All operators under this admin
$operators = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('created_by', auth()->id())
    ->whereIn('operator_level', [30, 40, 50])
    ->get();
```

---

### 4. Operator (Level 30)
**Own + Sub-Operator Customers**

#### Data Access
- ✅ **Own customers** (customers they created)
- ✅ **Sub-operator customers** (customers created by their sub-operators)
- ✅ Can create and manage Sub-operators
- ❌ **CANNOT** access other Operators' customers
- ❌ **CANNOT** access Admin or Super Admin functions

#### Responsibilities
- Create and manage Sub-operators
- Manage assigned customers
- Process bills and payments
- Generate invoices
- Handle customer complaints
- Use assigned packages and billing profiles
- Use recharge cards (if enabled)
- Send SMS to own customers
- View reports for own customers

#### Controllable Menus
Admin can disable specific menus:
- Resellers & Managers
- Routers & Packages
- Recharge Cards
- Customers
- Bills & Payments
- Incomes & Expenses
- Affiliate Program
- VAT Management

#### Query Example
```php
// Own customers + sub-operator customers
$subOperatorIds = User::where('created_by', auth()->id())
    ->where('operator_level', 40)
    ->pluck('id');

$customers = User::where(function($query) use ($subOperatorIds) {
    $query->where('created_by', auth()->id())
          ->orWhereIn('created_by', $subOperatorIds);
})->where('operator_level', 100)->get();
```

---

### 5. Sub-Operator (Level 40)
**Only Own Customers**

#### Data Access
- ✅ **Only own customers** (customers they created)
- ❌ **CANNOT** access Operator's other customers
- ❌ **CANNOT** create Sub-operators
- ❌ **CANNOT** manage packages or network settings

#### Responsibilities
- Manage own customer subset
- Process customer bills and payments
- Handle customer support for own customers
- View basic reports for own customers

#### Restrictions
- Cannot create any operators
- Cannot manage packages or profiles
- Limited to assigned customers only
- Most administrative features disabled
- Further restricted panel access

#### Query Example
```php
// Only own customers
$customers = User::where('created_by', auth()->id())
    ->where('operator_level', 100)
    ->get();
```

---

### 6. Manager (Level 50)
**View Based on Permissions**

#### Data Access
- ✅ **View operators' or sub-operators' customers** (read-only typically)
- ✅ Permission-based feature access
- ❌ **CANNOT** modify operators or sub-operators
- ❌ **CANNOT** modify packages or configurations

#### Responsibilities
- View customers based on permissions
- Process payments (if authorized)
- Manage assigned department complaints
- View performance reports
- Monitor network sessions

#### Query Example
```php
// View based on assigned permissions
if (auth()->user()->hasPermission('customers.view')) {
    $customers = User::where('tenant_id', auth()->user()->tenant_id)
        ->where('operator_level', 100)
        ->get();
}
```

---

### 7. Accountant (Level 70)
**Read-Only Financial**

#### Responsibilities
- View all financial reports
- View transactions (read-only)
- View VAT collections
- Export financial data
- View customer statements

#### Restrictions
- Read-only access to financial data
- Cannot modify transactions or settings

---

### 8. Staff (Level 80)
**Limited Operational Access**

#### Responsibilities
- View customer information
- Respond to complaints
- View network status
- Limited billing access

#### Restrictions
- Typically read-only access
- Cannot modify customer data
- Permission-based access only

---

### 9. Customer (Level 100)
**End User - Self-Service**

#### Data Access
- ✅ **Only own account data**
- ❌ Cannot access other customers' data

#### Capabilities
- View own profile and services
- View own billing and invoices
- Make payments
- Create support tickets
- View own network usage

---

## Permission System

### Standard Permissions

All operational roles have access to standard permissions:

```php
[
    // Customer Management
    'customers.view',
    'customers.create',
    'customers.update',
    'customers.suspend',
    'customers.activate',
    
    // Billing
    'billing.view',
    'billing.process',
    
    // Payments
    'payments.receive',
    
    // Reports
    'reports.view',
    
    // Support
    'complaints.manage',
]
```

### Special Permissions

Advanced permissions that must be explicitly granted by Admin:

```php
[
    'access_all_customers',      // View customers across all zones
    'bypass_credit_limit',       // Process over-limit payments
    'manual_discount',           // Apply discounts
    'delete_transactions',       // Delete payment records (dangerous)
    'modify_billing_cycle',      // Change billing cycles
    'access_logs',               // View system logs
    'bulk_operations',           // Bulk suspend/activate/bill
    'router_config_access',      // Access router configurations
    'override_package_pricing',  // Customer-specific pricing
    'view_sensitive_data',       // View sensitive information
    'export_all_data',           // Export database dumps
    'manage_resellers',          // Create/manage resellers
]
```

### Configuration

```php
// config/operators_permissions.php
'levels' => [
    'developer' => 0,        // Supreme authority. All tenants
    'super_admin' => 10,     // Only OWN tenants
    'admin' => 20,           // Own ISP data
    'operator' => 30,        // Own + sub-operator customers
    'sub_operator' => 40,    // Only own customers
    'manager' => 50,
    'accountant' => 70,
    'staff' => 80,
    'customer' => 100,
]
```

---

## Implementation Guide

### Using the User Model Helpers

The `User` model provides numerous helper methods:

#### Role Checking
```php
// Check specific role levels
if (auth()->user()->isDeveloper()) { ... }      // Level 0
if (auth()->user()->isSuperAdmin()) { ... }     // Level 10
if (auth()->user()->isAdmin()) { ... }          // Level 20
if (auth()->user()->isOperatorRole()) { ... }   // Level 30
if (auth()->user()->isSubOperator()) { ... }    // Level 40
if (auth()->user()->isManager()) { ... }        // Level 50
if (auth()->user()->isAccountant()) { ... }     // Level 70
if (auth()->user()->isStaff()) { ... }          // Level 80
if (auth()->user()->isCustomer()) { ... }       // Level 100
if (auth()->user()->isOperator()) { ... }       // < Level 100
```

#### Data Access
```php
// Check if can manage another user
if (auth()->user()->canManage($otherUser)) {
    // Can edit/delete this user
}

// Get users this user can manage
$manageableUsers = auth()->user()->manageableUsers()->get();

// Get customers created by this user
$myCustomers = auth()->user()->createdCustomers()->get();

// Get all accessible customers (auto-scoped by role)
$customers = auth()->user()->accessibleCustomers()->paginate(50);
```

#### Permission Checking
```php
// Check special operator permission
if (auth()->user()->hasSpecialPermission('access_all_customers')) {
    // Has special permission
}

// Check if menu is disabled
if (!auth()->user()->isMenuDisabled('customers')) {
    // Show customer menu
}
```

### Automatic Tenant Scoping

Models using the `BelongsToTenant` trait automatically filter by tenant:

```php
use App\Traits\BelongsToTenant;

class Customer extends Model
{
    use BelongsToTenant;
}

// Automatically filtered by current tenant
$customers = Customer::all();
```

### Bypass Tenant Scope (Developer Only)

```php
if (auth()->user()->isDeveloper()) {
    $allCustomers = Customer::withoutGlobalScope('tenant')->get();
}
```

---

## Usage Examples

### In Controllers

```php
public function index()
{
    // Automatically scoped by role
    $customers = auth()->user()->accessibleCustomers()->paginate(50);
    
    return view('panel.operator.customers.index', compact('customers'));
}

public function update(Request $request, User $user)
{
    // Check if can manage this user
    if (!auth()->user()->canManage($user)) {
        abort(403, 'Unauthorized');
    }
    
    $user->update($request->validated());
    
    return redirect()->back()->with('success', 'User updated');
}
```

### In Views (Blade)

```blade
@if(auth()->user()->isAdmin())
    <!-- Admin-only UI -->
    <a href="{{ route('admin.operators.create') }}">Add Operator</a>
@endif

@if(auth()->user()->hasPermission('customers.create'))
    <a href="{{ route('customers.create') }}">Add Customer</a>
@endif

@if(!auth()->user()->isMenuDisabled('customers'))
    <li><a href="{{ route('customers.index') }}">Customers</a></li>
@endif

@can('create', App\Models\Customer::class)
    <!-- Policy-based authorization -->
    <button>Create Customer</button>
@endcan
```

### In Routes

```php
// Role-based middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin routes
    Route::resource('operators', OperatorController::class);
});

Route::middleware(['auth', 'role:operator,sub-operator'])->group(function () {
    // Operator OR Sub-operator routes
    Route::resource('customers', CustomerController::class);
});

// Permission-based middleware
Route::middleware(['auth', 'permission:customers.create'])->group(function () {
    Route::post('customers', [CustomerController::class, 'store']);
});
```

### In Policies

```php
public class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->operator_level <= 30; // Operator level or higher
    }
    
    public function update(User $user, Customer $customer): bool
    {
        return $user->canManage($customer) 
            && $user->hasPermission('customers.update');
    }
    
    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin() 
            && $user->hasPermission('customers.delete');
    }
}
```

---

## API Reference

### User Model Methods

#### Role Checking Methods
- `isDeveloper()` - Check if Level 0
- `isSuperAdmin()` - Check if Level 10
- `isAdmin()` - Check if Level 20
- `isOperatorRole()` - Check if Level 30
- `isSubOperator()` - Check if Level 40
- `isManager()` - Check if Level 50
- `isAccountant()` - Check if Level 70
- `isStaff()` - Check if Level 80
- `isCustomer()` - Check if Level 100
- `isOperator()` - Check if < Level 100 (any staff)

#### Data Access Methods
- `canManage(User $user)` - Check if can manage another user
- `manageableUsers()` - Get users this user can manage
- `createdCustomers()` - Get customers created by this user
- `accessibleCustomers()` - Comprehensive role-based customer query

#### Permission Methods
- `hasSpecialPermission(string $key)` - Check special operator permissions
- `isMenuDisabled(string $menu)` - Check if menu is disabled

### BelongsToTenant Trait

#### Scopes
- `forTenant(int $tenantId)` - Filter by specific tenant
- `allTenants()` - Bypass global tenant scope

#### Methods
- `isOwnedByCurrentTenant()` - Check if record belongs to current tenant

---

## Testing

### Test Role Hierarchy

```php
public function test_developer_can_access_all_tenants()
{
    $developer = User::factory()->create([
        'operator_level' => 0,
        'is_developer' => true
    ]);
    
    $this->actingAs($developer);
    
    $tenants = Tenant::all();
    $this->assertGreaterThan(0, $tenants->count());
}
```

### Test Data Isolation

```php
public function test_operator_cannot_see_other_operator_customers()
{
    $operator1 = User::factory()->create(['operator_level' => 30]);
    $operator2 = User::factory()->create(['operator_level' => 30]);
    
    $customer1 = Customer::factory()->create(['created_by' => $operator1->id]);
    $customer2 = Customer::factory()->create(['created_by' => $operator2->id]);
    
    $this->actingAs($operator1);
    
    $customers = auth()->user()->accessibleCustomers()->get();
    
    $this->assertTrue($customers->contains($customer1));
    $this->assertFalse($customers->contains($customer2));
}
```

### Test Permissions

```php
public function test_special_permission_works()
{
    $operator = User::factory()->create(['operator_level' => 30]);
    
    OperatorPermission::create([
        'user_id' => $operator->id,
        'permission_key' => 'access_all_customers',
        'is_enabled' => true,
    ]);
    
    $this->assertTrue($operator->hasSpecialPermission('access_all_customers'));
}
```

---

## Security

### Query-Level Isolation
- All queries automatically filtered by tenant_id
- Global scopes prevent cross-tenant data leaks
- Developer bypass requires explicit check

### Authorization Policies
- Policy classes enforce hierarchical access
- Permission checks at multiple levels
- Consistent authorization across controllers

### Audit Logging
- All administrative actions logged
- Tracks user, action, resource, and result
- Immutable audit trail

### Session Management
- Tenant context stored in session
- Automatic tenant resolution per request
- Secure session handling

---

## Troubleshooting

### Issue: User Can't Access Expected Data

**Solution**: Check role level and permissions:
```php
// In tinker
$user = User::find(1);
$user->operator_level;
$user->accessibleCustomers()->count();
```

### Issue: Menu Not Showing

**Solution**: Check if menu is disabled:
```php
$user->isMenuDisabled('customers'); // Should return false
```

### Issue: Permission Denied

**Solution**: Verify permission assignment:
```php
$user->hasSpecialPermission('access_all_customers');
$user->hasPermission('customers.create');
```

### Issue: Cross-Tenant Data Leak

**Solution**: Ensure model uses BelongsToTenant trait:
```php
class YourModel extends Model
{
    use BelongsToTenant; // Must have this
}
```

---

## Data Access Matrix

| Resource Type | Developer | Super Admin | Admin | Operator | Sub-Op | Manager | Staff | Accountant | Customer |
|--------------|-----------|-------------|-------|----------|--------|---------|-------|------------|----------|
| **All Tenants** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Own Tenant(s)** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Tenant Data** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **ISP Data** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **All ISP Customers** | ✅ | ✅ | ✅ | ❌ | ❌ | ⚠️ | ⚠️ | ⚠️ | ❌ |
| **Own Customers** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Sub-Op Customers** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Tenants** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Admins** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Operators** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Sub-Ops** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Financial Reports** | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ❌ | ✅ | ❌ |
| **System Logs** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ Full Access
- ❌ No Access
- ⚠️ Limited/Permission-based

---

## Best Practices

### For Developers
1. Always use `BelongsToTenant` trait for tenant-scoped models
2. Test data isolation with different role levels
3. Use policies for authorization checks
4. Log sensitive operations

### For Super Admins
1. Carefully assign Admin roles
2. Monitor tenant usage and billing
3. Review audit logs regularly
4. Keep tenant configurations updated

### For Admins
1. Only grant special permissions when necessary
2. Disable unused menus for operators
3. Monitor operator access logs
4. Set appropriate credit limits

### For Operators
1. Create sub-operators for regional management
2. Request special permissions from Admin when needed
3. Organize customers using zones and custom fields
4. Generate reports regularly

---

## Database Schema

### Users Table
```sql
users
├── id
├── name
├── email
├── password
├── tenant_id (FK)
├── service_package_id (FK)
├── is_active
├── activated_at
├── created_by (FK users)
├── operator_level (0-100)
├── disabled_menus (JSON)
├── manager_id (FK users)
├── operator_type (string)
└── timestamps
```

### Roles Table
```sql
roles
├── id
├── name
├── slug
├── description
├── permissions (JSON)
├── level (0-100)
└── timestamps
```

### Role-User Pivot
```sql
role_user
├── id
├── user_id (FK)
├── role_id (FK)
├── tenant_id (FK, nullable)
└── timestamps
```

### Operator Permissions
```sql
operator_permissions
├── id
├── user_id (FK)
├── tenant_id (FK, nullable)
├── permission_key
├── is_enabled
├── metadata (JSON)
└── timestamps
```

---

## Seeding Roles

```bash
# Seed all roles
php artisan db:seed --class=RoleSeeder

# Check seeded roles
php artisan tinker
>>> Role::all()->pluck('name', 'level')
```

---

## Related Documentation

- **[Multi-Tenancy Architecture](tenancy.md)** - Original tenancy implementation
- **[API Documentation](API.md)** - API endpoints and authentication
- **[Testing Guide](TESTING.md)** - How to test roles and permissions

---

**For Questions**: Review this guide or open an issue on GitHub.

**Last Updated**: 2026-01-18  
**Maintained By**: ISP Solution Team
