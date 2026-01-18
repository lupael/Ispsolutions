# Role-Based Menu System

This document describes the role-based menu system implementation for the ISP Solution.

## Overview

The system automatically generates appropriate menus based on the logged-in user's role using the `MenuService` class.

## Role Hierarchy

1. **Developer (Level 0)** - Technical infrastructure and API management
2. **Super Admin (Level 10)** - System-wide administrator across all tenants
3. **Admin (Level 20)** - Tenant administrator (ISP Admin)
4. **Operator (Level 30)** - Operational staff with configurable menus
5. **Sub-Operator (Level 40)** - Limited operator (subset of operator)
6. **Manager (Level 50)** - Task-specific access
7. **Card Distributor (Level 60)** - Card operations only (separate portal)
8. **Reseller (Level 65)** - Customer management and sales
9. **Accountant (Level 70)** - Financial reporting (read-only)
10. **Sub-Reseller (Level 75)** - Subordinate to reseller
11. **Staff (Level 80)** - Support staff
12. **Customer (Level 100)** - Self-service access

## Using the Menu Service

### In Controllers

```php
use App\Services\MenuService;

class DashboardController extends Controller
{
    public function index(MenuService $menuService)
    {
        $menu = $menuService->generateMenu();
        return view('dashboard', compact('menu'));
    }
}
```

### In Blade Templates

#### Option 1: Using the Component

```blade
<x-role-based-menu />
```

#### Option 2: Direct Service Injection

```blade
@inject('menuService', 'App\Services\MenuService')

@php
    $menu = $menuService->generateMenu();
@endphp

@foreach($menu as $item)
    <!-- Render menu item -->
@endforeach
```

## Permission Checking

### In Controllers

```php
// Check if user has specific permission
if (auth()->user()->hasPermission('users.manage')) {
    // Allow action
}

// Check if user has specific role
if (auth()->user()->hasRole('super-admin')) {
    // Allow action
}

// Check if user has any of the specified roles
if (auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
    // Allow action
}
```

### In Blade Templates

```blade
@if(auth()->user()->hasPermission('users.manage'))
    <!-- Show content -->
@endif

@if(auth()->user()->hasRole('admin'))
    <!-- Show admin content -->
@endif
```

## Route Protection

Routes are protected using the `role` middleware:

```php
// Single role
Route::middleware(['auth', 'role:developer'])->group(function () {
    // Developer-only routes
});

// Multiple roles
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    // Super Admin and Admin routes
});
```

## Customizing Menus

To customize menus for a specific role, edit the corresponding method in `app/Services/MenuService.php`:

```php
protected function getAdminMenu(): array
{
    return [
        [
            'title' => 'Dashboard',
            'icon' => 'ki-home-3',
            'route' => 'panel.admin.dashboard',
        ],
        [
            'title' => 'Users',
            'icon' => 'ki-profile-user',
            'children' => [
                ['title' => 'All Users', 'route' => 'panel.admin.users'],
                ['title' => 'Add User', 'route' => 'panel.admin.users.create'],
            ],
        ],
    ];
}
```

## Database Seeding

After deployment, seed the roles:

```bash
php artisan db:seed --class=RoleSeeder
```

## Menu Structure

Each menu item has the following structure:

```php
[
    'title' => 'Menu Title',      // Required: Display text
    'icon' => 'ki-icon-name',     // Required: Icon class
    'route' => 'route.name',      // Required for single items
    'children' => [               // Optional: Submenu items
        [
            'title' => 'Submenu Title',
            'route' => 'route.name',
        ],
    ],
]
```

## Developer Panel Routes

- Dashboard: `panel.developer.dashboard`
- Tenant Management: `panel.developer.tenancies.*`
- Subscription Management: `panel.developer.subscriptions.*`
- Global Configuration: `panel.developer.config.*`
- SMS Gateway: `panel.developer.sms-gateway.*`
- Payment Gateway: `panel.developer.payment-gateway.*`
- VPN Pools: `panel.developer.vpn-pools.*`
- System Logs: `panel.developer.logs`
- API Management: `panel.developer.api-docs`, `panel.developer.api-keys`

## Super Admin Panel Routes

- Dashboard: `panel.super-admin.dashboard`
- Tenant Management: `panel.super-admin.isp.*`
- Admin Management: `panel.super-admin.users`
- Subscription Management: `panel.super-admin.billing.*`
- Global Configuration: `panel.super-admin.settings`, `panel.super-admin.payment-gateway.*`, `panel.super-admin.sms-gateway.*`
- System Logs: `panel.super-admin.logs`
- Monitoring: `panel.super-admin.monitoring`

## Admin Panel Routes

- Dashboard: `panel.admin.dashboard`
- Resellers & Managers: `panel.admin.operators.*`
- Routers & Packages: `panel.admin.packages.*`, `panel.admin.network.*`
- Recharge Cards: `panel.admin.cards.*`
- Customers: `panel.admin.customers.*`
- Bills & Payments: `panel.admin.bills.*`, `panel.admin.payments.*`
- Incomes & Expenses: `panel.admin.accounting.*`
- Complaints & Support: `panel.admin.tickets.*`
- Reports: `panel.admin.reports.*`
- Affiliate Program: `panel.admin.affiliate.*`
- VAT Management: `panel.admin.vat.*`
- SMS Services: `panel.admin.sms.*`
- Configuration: `panel.admin.config.*`
- Activity Logs: `panel.admin.logs.*`

## Operator Panel Routes

- Dashboard: `panel.operator.dashboard`
- Sub-Operators: `panel.operator.sub-operators.*`
- Customers: `panel.operator.customers.*`
- Bills & Payments: `panel.operator.bills.*`, `panel.operator.payments.*`
- Recharge Cards: `panel.operator.cards.*`
- Complaints: `panel.operator.complaints.*`
- Reports: `panel.operator.reports.*`
- SMS: `panel.operator.sms.*`

## Sub-Operator Panel Routes

- Dashboard: `panel.sub-operator.dashboard`
- Customers: `panel.sub-operator.customers.*`
- Bills & Payments: `panel.sub-operator.bills.*`, `panel.sub-operator.payments.*`
- Reports: `panel.sub-operator.reports.*`

## Manager Panel Routes

- Dashboard: `panel.manager.dashboard`
- Customer Viewing: `panel.manager.customers.*`
- Payment Processing: `panel.manager.payments.*`
- Complaint Management: `panel.manager.complaints.*`
- Reports: `panel.manager.reports.*`

## Accountant Panel Routes

- Dashboard: `panel.accountant.dashboard`
- Financial Reports: `panel.accountant.reports.*`
- Income/Expense: `panel.accountant.transactions.*`, `panel.accountant.expenses.*`
- VAT Collections: `panel.accountant.vat.*`
- Payment History: `panel.accountant.payments.*`
- Customer Statements: `panel.accountant.customers.statements`

## Best Practices

1. **Always check permissions** in controllers, even if routes are protected
2. **Use role middleware** to protect routes at the route level
3. **Keep menu definitions updated** when adding new features
4. **Test with different roles** to ensure proper access control
5. **Document new permissions** when creating new features

## Troubleshooting

### Menu Not Showing

1. Ensure user is authenticated: `auth()->check()`
2. Verify user has assigned role: `$user->roles`
3. Check route names match in `MenuService` and `routes/web.php`

### Permission Denied Errors

1. Verify user has the required role
2. Check middleware is properly registered
3. Ensure role permissions are correctly seeded

### Route Not Found

1. Clear route cache: `php artisan route:clear`
2. Verify route is defined in `routes/web.php`
3. Check route name matches menu definition

## Security Considerations

1. **Never bypass permission checks** in controllers
2. **Always validate user input** in forms
3. **Use CSRF protection** on all POST/PUT/DELETE requests
4. **Keep API credentials secure** (payment/SMS gateways)
5. **Log sensitive actions** for audit purposes
