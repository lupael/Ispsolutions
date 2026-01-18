# Data Isolation & Role-Based Access Control

## Overview

This document defines the data isolation rules and role-based access control (RBAC) system for the ISP Solution multi-tenancy platform.

---

## Role Hierarchy & Data Access

### Operator Levels

The system uses a numeric level system where **lower numbers = higher privileges**:

```
Level 0:   Developer      (Supreme Authority)
Level 10:  Super Admin    (Tenant Owner)
Level 20:  Admin          (ISP Owner)
Level 30:  Operator       (Reseller)
Level 40:  Sub-Operator   (Sub-Reseller)
Level 50:  Manager        (Task-specific)
Level 60:  Card Distributor / Reseller
Level 65:  Sub-Reseller
Level 70:  Accountant
Level 80:  Staff
Level 100: Customer       (End User)
```

---

## Data Isolation Rules by Role

### 1. Developer (Level 0)
**Supreme Authority - All Tenants**

#### Data Access:
- ✅ **ALL tenants** (unrestricted)
- ✅ Can create and manage tenants
- ✅ Can access any user, customer, or resource across all tenants
- ✅ Source code owner with complete system access

#### Responsibilities:
- Create and manage tenants
- Define subscription pricing
- Access any panel for support
- View all customer details across tenants
- Access audit logs and system logs
- Suspend/activate tenancies
- Configure global system settings
- Manage API integrations

#### Restrictions:
- None - unrestricted access

---

### 2. Super Admin (Level 10)
**Only OWN Tenants - Tenant Context Owner**

#### Data Access:
- ✅ **Only OWN tenant(s)** they created/own
- ✅ Can create and manage Admins within their tenant(s)
- ✅ Can view all data within their tenant(s)
- ❌ **CANNOT** access other tenants' data
- ❌ **CANNOT** create new tenants (only Developer can)

#### Responsibilities:
- Add/remove ISPs (Admins) within their tenant
- Configure billing for Admins
- Manage payment gateways for tenant
- Manage SMS gateways for tenant
- View tenant-wide logs
- Manage subscriptions for Admins
- Configure tenant settings

#### Restrictions:
- Limited to own tenant(s) only
- Cannot create tenants
- Cannot access Developer functions

#### Query Scope:
```php
// Super Admin can only see their own tenants
$tenants = Tenant::where('created_by', auth()->id())->get();

// And all users/data within those tenants
$users = User::whereIn('tenant_id', $ownTenantIds)->get();
```

---

### 3. Admin (Level 20)
**ISP Owner - Own ISP Data Within Tenancy**

#### Data Access:
- ✅ **All data under their ISP** within their tenant
- ✅ Can see their own customers
- ✅ Can see Operator-created customers
- ✅ Can see Sub-operator-created customers
- ✅ Can create and manage Operators
- ❌ **CANNOT** access other Admins' data
- ❌ **CANNOT** access other tenants

#### Responsibilities:
- Create and manage Operators
- Create and manage Sub-operators
- Create and manage Managers
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

#### Restrictions:
- Limited to single tenant
- Cannot modify other Admins' data
- Cannot create/manage tenants
- Cannot create Super Admins

#### Data Isolation:
```php
// Admin sees all customers in their ISP within the tenant
$customers = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('created_by_admin', auth()->id()) // Or any operator under this admin
    ->where('operator_level', 100)
    ->get();

// Admin sees all their operators
$operators = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('created_by', auth()->id())
    ->whereIn('operator_level', [30, 40, 50])
    ->get();
```

---

### 4. Operator (Level 30)
**Own + Sub-Operator Customers**

#### Data Access:
- ✅ **Own customers** (customers they created)
- ✅ **Sub-operator customers** (customers created by their sub-operators)
- ✅ Can create and manage Sub-operators
- ❌ **CANNOT** access other Operators' customers
- ❌ **CANNOT** access Admin or Super Admin functions

#### Responsibilities:
- Create and manage Sub-operators
- Manage assigned customers
- Process bills and payments
- Generate invoices
- Handle customer complaints
- Use assigned packages and billing profiles
- Use recharge cards (if enabled)
- Send SMS to own customers
- View reports for own customers

#### Menu Control:
- Admin can disable specific menus:
  - Resellers & Managers
  - Routers & Packages
  - Recharge Cards
  - Customers
  - Bills & Payments
  - Incomes & Expenses
  - Affiliate Program
  - VAT Management

#### Data Isolation:
```php
// Operator sees own customers + sub-operator customers
$subOperators = User::where('created_by', auth()->id())
    ->where('operator_level', 40)
    ->pluck('id');

$customers = User::where(function($query) use ($subOperators) {
    $query->where('created_by', auth()->id())
          ->orWhereIn('created_by', $subOperators);
})->where('operator_level', 100)->get();
```

---

### 5. Sub-Operator (Level 40)
**Only Own Customers**

#### Data Access:
- ✅ **Only own customers** (customers they created)
- ❌ **CANNOT** access Operator's other customers
- ❌ **CANNOT** create Sub-operators
- ❌ **CANNOT** manage packages or network settings

#### Responsibilities:
- Manage own customer subset
- Process customer bills and payments
- Handle customer support for own customers
- View basic reports for own customers

#### Restrictions:
- Cannot create any operators
- Cannot manage packages or profiles
- Limited to assigned customers only
- Most administrative features disabled
- Further restricted panel access

#### Data Isolation:
```php
// Sub-operator sees ONLY their own customers
$customers = User::where('created_by', auth()->id())
    ->where('operator_level', 100)
    ->get();
```

---

### 6. Manager/Staff (Level 50-80)
**View Based on Permissions**

#### Data Access:
- ✅ **View operators' or sub-operators' customers** (read-only typically)
- ✅ Permission-based feature access
- ❌ **CANNOT** modify operators or sub-operators
- ❌ **CANNOT** modify packages or configurations

#### Manager Responsibilities (Level 50):
- View customers based on permissions
- Process payments (if authorized)
- Manage assigned department complaints
- View performance reports
- Monitor network sessions

#### Staff Responsibilities (Level 80):
- View customer information
- Respond to complaints
- View network status
- Limited billing access

#### Accountant Responsibilities (Level 70):
- View all financial reports
- View transactions (read-only)
- View VAT collections
- Export financial data
- View customer statements

#### Restrictions:
- Typically read-only access
- Cannot modify customer data
- Cannot modify system configuration
- Permission-based access only

#### Data Isolation:
```php
// Manager can view based on assigned permissions
if (auth()->user()->hasPermission('customers.view')) {
    // Can view customers within their admin's scope
    $customers = User::where('tenant_id', auth()->user()->tenant_id)
        ->where('operator_level', 100)
        ->get();
}
```

---

## Permission System

### Standard Permissions
All operational roles have access to standard permissions:

```php
[
    'customers.view',
    'customers.create',
    'customers.update',
    'customers.suspend',
    'customers.activate',
    'billing.view',
    'billing.process',
    'payments.receive',
    'reports.view',
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

---

## Tenant Isolation

### Automatic Tenant Scoping

All models using the `BelongsToTenant` trait automatically filter queries by `tenant_id`:

```php
use App\Traits\BelongsToTenant;

class Customer extends Model
{
    use BelongsToTenant;
    
    // Automatically scoped to current tenant
}

// All queries automatically filtered:
$customers = Customer::all(); // Only current tenant's customers
```

### Developer Bypass

Developers can bypass tenant filtering:

```php
// Developer accessing all tenants
if (auth()->user()->isDeveloper()) {
    $allCustomers = Customer::withoutGlobalScope('tenant')->get();
}
```

---

## Data Access Matrix

| Resource Type | Developer | Super Admin | Admin | Operator | Sub-Op | Manager | Staff | Accountant |
|--------------|-----------|-------------|-------|----------|--------|---------|-------|------------|
| **All Tenants** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Own Tenant(s)** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Tenant Data** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **ISP Data** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **All Customers in ISP** | ✅ | ✅ | ✅ | ❌ | ❌ | ⚠️ | ⚠️ | ⚠️ |
| **Own Customers** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Sub-Op Customers** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Create Tenants** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Admins** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Operators** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Create Sub-Ops** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Create Managers** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Financial Reports** | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ❌ | ✅ |
| **System Logs** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ Full Access
- ❌ No Access
- ⚠️ Limited/Permission-based

---

## Implementation Examples

### 1. Tenant Resolution Middleware

```php
// app/Http/Middleware/ResolveTenant.php
public function handle($request, Closure $next)
{
    $subdomain = $this->getSubdomain($request);
    $tenant = Tenant::where('subdomain', $subdomain)->first();
    
    if (!$tenant) {
        abort(404, 'Tenant not found');
    }
    
    app()->instance('tenant', $tenant);
    $request->attributes->set('tenant', $tenant);
    
    return $next($request);
}
```

### 2. Customer Query Scoping

```php
// In Controller
public function index()
{
    $user = auth()->user();
    
    // Automatically scoped based on role
    $query = Customer::query();
    
    if ($user->operator_level === 30) { // Operator
        $subOpIds = User::where('created_by', $user->id)
            ->where('operator_level', 40)
            ->pluck('id');
            
        $query->where(function($q) use ($user, $subOpIds) {
            $q->where('created_by', $user->id)
              ->orWhereIn('created_by', $subOpIds);
        });
    } elseif ($user->operator_level === 40) { // Sub-Operator
        $query->where('created_by', $user->id);
    }
    // Admin and above see all
    
    return $query->paginate(50);
}
```

### 3. Permission Checking

```php
// In Blade View
@can('customers.create')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        Add Customer
    </a>
@endcan

// In Controller
public function store(Request $request)
{
    $this->authorize('customers.create');
    
    // Create customer
}

// In Policy
public function create(User $user)
{
    return $user->operator_level <= 40
        && $user->hasPermission('customers.create');
}
```

---

## Security Considerations

### 1. Query-Level Isolation
- All queries automatically filtered by tenant_id
- Global scopes prevent cross-tenant data leaks
- Developer bypass requires explicit check

### 2. Authorization Policies
- Policy classes enforce hierarchical access
- Permission checks at multiple levels
- Consistent authorization across controllers

### 3. Audit Logging
- All administrative actions logged
- Tracks user, action, resource, and result
- Immutable audit trail

### 4. Session Management
- Tenant context stored in session
- Automatic tenant resolution per request
- Secure session handling

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

## Testing Data Isolation

### Test Cases

```php
// 1. Tenant Isolation
public function test_users_cannot_access_other_tenant_data()
{
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    app()->instance('tenant', $tenant1);
    
    $customer1 = Customer::factory()->create(['tenant_id' => $tenant1->id]);
    $customer2 = Customer::factory()->create(['tenant_id' => $tenant2->id]);
    
    $customers = Customer::all();
    
    $this->assertCount(1, $customers);
    $this->assertEquals($tenant1->id, $customers->first()->tenant_id);
}

// 2. Operator Data Isolation
public function test_operator_can_only_see_own_customers()
{
    $admin = User::factory()->create(['operator_level' => 20]);
    $operator1 = User::factory()->create([
        'operator_level' => 30,
        'created_by' => $admin->id
    ]);
    $operator2 = User::factory()->create([
        'operator_level' => 30,
        'created_by' => $admin->id
    ]);
    
    $customer1 = Customer::factory()->create(['created_by' => $operator1->id]);
    $customer2 = Customer::factory()->create(['created_by' => $operator2->id]);
    
    $this->actingAs($operator1);
    
    $customers = Customer::where('created_by', auth()->id())->get();
    
    $this->assertCount(1, $customers);
    $this->assertEquals($customer1->id, $customers->first()->id);
}

// 3. Developer Bypass
public function test_developer_can_access_all_tenant_data()
{
    $developer = User::factory()->create([
        'operator_level' => 0,
        'is_developer' => true
    ]);
    
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    Customer::factory()->create(['tenant_id' => $tenant1->id]);
    Customer::factory()->create(['tenant_id' => $tenant2->id]);
    
    $this->actingAs($developer);
    
    $customers = Customer::withoutGlobalScope('tenant')->get();
    
    $this->assertCount(2, $customers);
}
```

---

## Conclusion

This data isolation system ensures:
- **Secure multi-tenancy** with automatic tenant scoping
- **Hierarchical role-based access** with clear data boundaries
- **Flexible permissions** for granular control
- **Developer override** for system administration
- **Audit trail** for compliance and security

All roles have clearly defined data access patterns, preventing unauthorized access while allowing necessary operational flexibility.
