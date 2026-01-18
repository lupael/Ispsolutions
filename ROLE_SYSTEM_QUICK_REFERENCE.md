# Role System Quick Reference

**Version:** 1.0  
**Last Updated:** 2026-01-18

This is a quick reference guide for developers working with the multi-tenancy role system.

---

## Role Hierarchy

```
Level 0:   Developer       (All Tenants - Supreme Authority)
Level 10:  Super Admin     (Own Tenants Only)
Level 20:  Admin           (ISP Owner - Own ISP Data)
Level 30:  Operator        (Own + Sub-Operator Customers)
Level 40:  Sub-Operator    (Only Own Customers)
Level 50:  Manager         (Permission-Based)
Level 60:  Card Distributor/Reseller
Level 65:  Sub-Reseller
Level 70:  Accountant      (Read-Only Financial)
Level 80:  Staff           (Permission-Based Support)
Level 100: Customer        (End User)
```

**Rule:** Lower level = Higher privilege

---

## Quick Checks

### In Controllers
```php
// Check role level
if (auth()->user()->isDeveloper()) {
    // Developer-only code
}

if (auth()->user()->isSuperAdmin()) {
    // Super Admin-only code
}

if (auth()->user()->isAdmin()) {
    // Admin-only code
}

if (auth()->user()->isOperatorRole()) {
    // Operator-only code
}

// Check if can manage another user
if (auth()->user()->canManage($otherUser)) {
    // Can manage this user
}

// Get accessible customers
$customers = auth()->user()->accessibleCustomers()->paginate(50);
```

### In Views (Blade)
```blade
@if(auth()->user()->isDeveloper())
    <!-- Developer-only UI -->
@endif

@if(auth()->user()->isAdmin())
    <!-- Admin-only UI -->
@endif

@if(auth()->user()->hasPermission('customers.create'))
    <a href="{{ route('customers.create') }}">Add Customer</a>
@endif

@if(!auth()->user()->isMenuDisabled('customers'))
    <!-- Show customer menu -->
@endif

@can('create', App\Models\Customer::class)
    <!-- Policy-based authorization -->
@endcan
```

### In Routes
```php
// Role-based middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin routes
});

Route::middleware(['auth', 'role:operator,sub-operator'])->group(function () {
    // Operator OR Sub-operator routes
});

// Permission-based middleware
Route::middleware(['auth', 'permission:customers.create'])->group(function () {
    // Routes requiring specific permission
});
```

### In Policies
```php
public function viewAny(User $user): bool
{
    return $user->operator_level <= 30; // Operator level or higher
}

public function update(User $user, Customer $customer): bool
{
    return $user->canManage($customer);
}
```

---

## Data Access Patterns

### Developer (Level 0)
```php
// Access ALL tenants
$tenants = Tenant::all();

// Access customers across all tenants
$customers = User::withoutGlobalScope('tenant')
    ->where('operator_level', 100)
    ->get();
```

### Super Admin (Level 10)
```php
// Only own tenants
$tenants = Tenant::where('created_by', auth()->id())->get();

// All users in own tenants
$tenantIds = $tenants->pluck('id');
$users = User::whereIn('tenant_id', $tenantIds)->get();
```

### Admin (Level 20)
```php
// All customers in own ISP/tenant
$customers = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_level', 100)
    ->get();

// All operators in own ISP
$operators = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_level', '<=', 40)
    ->get();
```

### Operator (Level 30)
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

### Sub-Operator (Level 40)
```php
// Only own customers
$customers = User::where('created_by', auth()->id())
    ->where('operator_level', 100)
    ->get();
```

### Manager/Staff/Accountant
```php
// Permission-based
if (auth()->user()->hasPermission('customers.view')) {
    $customers = User::where('tenant_id', auth()->user()->tenant_id)
        ->where('operator_level', 100)
        ->get();
}
```

---

## Permission Checking

### Standard Permissions
```php
// Via Role
if (auth()->user()->hasPermission('customers.create')) {
    // User's role has this permission
}

// Via Policy
if (auth()->user()->can('create', Customer::class)) {
    // User authorized by policy
}
```

### Special Permissions
```php
// Check special operator permission
if (auth()->user()->hasSpecialPermission('access_all_customers')) {
    // Has special permission granted by Admin
}

if (auth()->user()->hasSpecialPermission('override_package_pricing')) {
    // Can set custom pricing
}
```

### Available Special Permissions
- `access_all_customers`
- `bypass_credit_limit`
- `manual_discount`
- `delete_transactions`
- `modify_billing_cycle`
- `access_logs`
- `bulk_operations`
- `router_config_access`
- `override_package_pricing`
- `view_sensitive_data`
- `export_all_data`
- `manage_resellers`

---

## Menu Control

### Controllable Menus
```php
$controllableMenus = [
    'resellers_managers',
    'routers_packages',
    'recharge_cards',
    'customers',
    'bills_payments',
    'incomes_expenses',
    'affiliate_program',
    'vat_management',
];
```

### Check if Menu is Disabled
```php
if (!auth()->user()->isMenuDisabled('customers')) {
    // Show customer menu
}
```

### In Blade
```blade
@if(!auth()->user()->isMenuDisabled('routers_packages'))
    <a href="{{ route('panel.admin.network.routers') }}">Routers</a>
@endif
```

---

## Common Queries

### Get Users I Can Manage
```php
$manageableUsers = auth()->user()->manageableUsers()->get();
```

### Get My Created Customers
```php
$myCustomers = auth()->user()->createdCustomers()->get();
```

### Get All Accessible Customers
```php
// Automatically scoped by role
$customers = auth()->user()->accessibleCustomers()->paginate(50);
```

### Check Hierarchy
```php
if (auth()->user()->canManage($otherUser)) {
    // Can manage/edit this user
}
```

---

## Tenant Scoping

### Automatic Scoping
Models with `BelongsToTenant` trait are automatically scoped:

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

## Configuration Files

### Role Levels
```php
// config/operators_permissions.php
'levels' => [
    'developer' => 0,
    'super_admin' => 10,
    'admin' => 20,
    'operator' => 30,
    'sub_operator' => 40,
    'manager' => 50,
    // ... etc
],
```

### Standard Permissions
```php
// config/operators_permissions.php
'customers' => [
    'view_customers',
    'create_customers',
    'edit_customers',
    // ... etc
],
```

### Special Permissions
```php
// config/special_permissions.php
'access_all_customers' => [
    'label' => 'Access All Customers',
    'description' => '...',
    'default' => false,
],
```

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

## Testing Patterns

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

## Common Mistakes to Avoid

### ❌ Don't Do This
```php
// Don't bypass tenant scoping without checking role
$allCustomers = Customer::withoutGlobalScope('tenant')->get();

// Don't hardcode role checks
if (auth()->user()->operator_level == 30) { ... }

// Don't forget to check permissions
Customer::create($data); // Missing authorization check
```

### ✅ Do This Instead
```php
// Check role first
if (auth()->user()->isDeveloper()) {
    $allCustomers = Customer::withoutGlobalScope('tenant')->get();
}

// Use helper methods
if (auth()->user()->isOperatorRole()) { ... }

// Use policies
$this->authorize('create', Customer::class);
Customer::create($data);
```

---

## Useful Commands

### Seed Roles
```bash
php artisan db:seed --class=RoleSeeder
```

### Check User Roles
```bash
php artisan tinker
>>> $user = User::find(1)
>>> $user->roles
>>> $user->isDeveloper()
>>> $user->accessibleCustomers()->count()
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## References

- **Full Documentation:** [DATA_ISOLATION.md](DATA_ISOLATION.md)
- **Implementation Status:** [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md)
- **Feature Spec:** [TODO_FEATURES_A2Z.md](TODO_FEATURES_A2Z.md)
- **Panel Specs:** [PANELS_SPECIFICATION.md](PANELS_SPECIFICATION.md)

---

**Need Help?** Refer to the comprehensive [DATA_ISOLATION.md](DATA_ISOLATION.md) for detailed explanations and examples.
