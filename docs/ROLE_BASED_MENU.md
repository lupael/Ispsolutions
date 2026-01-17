# Role-Based Menu System

This document describes the role-based menu system implementation for the ISP Solution.

## Overview

The system automatically generates appropriate menus based on the logged-in user's role using the `MenuService` class.

## Role Hierarchy

1. **Developer (Level 1000)** - Supreme authority with all permissions
2. **Super Admin (Level 100)** - Tenancy administrator
3. **Admin (Level 90)** - Tenant administrator
4. **Manager (Level 70)** - Operational permissions
5. **Reseller (Level 60)** - Customer management
6. **Sub-Reseller (Level 55)** - Subordinate to reseller
7. **Staff (Level 50)** - Limited operational access
8. **Card Distributor (Level 40)** - Recharge card management
9. **Customer (Level 10)** - Self-service access

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
- Tenancy Management: `panel.developer.tenancies.*`
- System Access: `panel.developer.access-panel`
- Customer Search: `panel.developer.customers.search`
- Audit Logs: `panel.developer.audit-logs`

## Super Admin Panel Routes

- Dashboard: `panel.super-admin.dashboard`
- ISP Management: `panel.super-admin.isp.*`
- Billing Configuration: `panel.super-admin.billing.*`
- Payment Gateway: `panel.super-admin.payment-gateway.*`
- SMS Gateway: `panel.super-admin.sms-gateway.*`

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
