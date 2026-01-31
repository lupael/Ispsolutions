# Routing Troubleshooting Guide

## Overview
This guide helps diagnose and fix 404 "Page not found" errors in the Laravel ISP Solution application.

## Common Causes of 404 Errors

### 1. Missing Route Definitions
**Symptom**: Page returns 404 even though you expect it to exist.

**Diagnosis**:
```bash
# List all registered routes
php artisan route:list

# Search for specific route
php artisan route:list | grep "customers"

# Search by route name
php artisan route:list --name=panel.developer

# Search by URI
php artisan route:list --path=panel/developer
```

**Solution**: Add the missing route to `routes/web.php`

### 2. Middleware Restrictions
**Symptom**: Route exists but returns 403 Unauthorized or redirects to login.

**Diagnosis**:
```bash
# Check route middleware
php artisan route:list --name=panel.developer.customers.index

# Look for middleware column showing 'auth', 'role:developer', etc.
```

**Common Middleware Issues**:
- `auth` - User must be logged in
- `role:developer` - User must have the 'developer' role
- `role:admin` - User must have the 'admin' role

**Solution**: 
1. Ensure user is logged in
2. Check user has correct role: `$user->hasRole('developer')`
3. Verify CheckRole middleware logic in `app/Http/Middleware/CheckRole.php`

### 3. Incorrect HTTP Verbs
**Symptom**: Route works with GET but not POST, or vice versa.

**Diagnosis**:
```bash
# Check HTTP method in route list
php artisan route:list | grep "customers.store"
```

**Solution**:
```php
// Ensure form uses correct method
<form method="POST" action="{{ route('panel.developer.customers.store') }}">
    @csrf
    <!-- form fields -->
</form>

// Ensure route accepts correct method
Route::post('/customers', [DeveloperController::class, 'store'])->name('customers.store');
```

### 4. Hardcoded URLs vs Route Helpers
**Symptom**: Links break when URL structure changes.

**Problem**:
```blade
<!-- BAD: Hardcoded URL -->
<a href="/panel/developer/customers">Customers</a>
```

**Solution**:
```blade
<!-- GOOD: Using route helper -->
<a href="{{ route('panel.developer.customers.index') }}">Customers</a>
```

### 5. Misconfigured APP_URL
**Symptom**: Routes work locally but not on dev/production server.

**Diagnosis**:
```bash
# Check .env file
grep APP_URL .env
```

**Solution**:
```env
# For development server
APP_URL=https://dev.ispbills.com

# For local development
APP_URL=http://localhost:8000
```

After changing, clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

## Debugging Steps for 404 Errors

### Step 1: Verify Route Exists
```bash
php artisan route:list --name=panel.developer.customers.index
```

If no output, the route doesn't exist. Add it to `routes/web.php`.

### Step 2: Check Route Definition
```bash
php artisan route:list | grep "panel/developer"
```

Verify:
- ✅ Route URI matches the URL you're accessing
- ✅ Route method (GET/POST/etc) matches your request
- ✅ Route name matches what you're using in `route()` helper

### Step 3: Test Route Without Middleware
Temporarily disable middleware to see if that's the issue:

```php
// In routes/web.php
Route::get('/test-route', function() {
    return 'Route works!';
});
```

If this works but your actual route doesn't, it's a middleware issue.

### Step 4: Check Middleware Logic

View the CheckRole middleware:
```bash
cat app/Http/Middleware/CheckRole.php
```

The middleware checks:
1. User is authenticated (`$request->user()`)
2. User has required role (`$request->user()->hasAnyRole($roles)`)

### Step 5: Verify User Roles
```bash
php artisan tinker
```

```php
// In tinker
$user = User::find(1);
$user->roles;  // See all roles
$user->hasRole('developer');  // Should return true
$user->hasAnyRole(['developer', 'admin']);  // Check multiple roles
```

## Route Organization in web.php

Routes are organized by panel type:

```php
// Developer Panel Routes
Route::prefix('panel/developer')->name('panel.developer.')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [DeveloperController::class, 'allCustomers'])->name('customers.index');
    // ... more routes
});

// Admin Panel Routes  
Route::prefix('panel/admin')->name('panel.admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // ... admin routes
});

// Manager Panel Routes
Route::prefix('panel/manager')->name('panel.manager.')->middleware(['auth', 'role:manager'])->group(function () {
    // ... manager routes
});
```

## Common Route Patterns

### Index (List)
```php
Route::get('/customers', [Controller::class, 'index'])->name('customers.index');
```

### Show (View Single)
```php
Route::get('/customers/{customer}', [Controller::class, 'show'])->name('customers.show');
```

### Create (Show Form)
```php
Route::get('/customers/create', [Controller::class, 'create'])->name('customers.create');
```

### Store (Process Form)
```php
Route::post('/customers', [Controller::class, 'store'])->name('customers.store');
```

### Edit (Show Edit Form)
```php
Route::get('/customers/{customer}/edit', [Controller::class, 'edit'])->name('customers.edit');
```

### Update
```php
Route::put('/customers/{customer}', [Controller::class, 'update'])->name('customers.update');
Route::patch('/customers/{customer}', [Controller::class, 'update'])->name('customers.update');
```

### Delete
```php
Route::delete('/customers/{customer}', [Controller::class, 'destroy'])->name('customers.destroy');
```

## Testing Routes

### Manual Testing
```bash
# Start development server
php artisan serve

# Visit route in browser
# For example: http://localhost:8000/panel/developer/dashboard
```

### Using curl
```bash
# Test GET request
curl -i http://localhost:8000/panel/developer/dashboard

# Test POST request (with CSRF token)
curl -X POST http://localhost:8000/panel/developer/customers \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "_token=YOUR_CSRF_TOKEN&name=Test"
```

## Quick Fixes

### Fix 1: Route Name Mismatch
```blade
<!-- Error: route not found -->
<a href="{{ route('developer.customers') }}">Customers</a>

<!-- Fixed: use full route name -->
<a href="{{ route('panel.developer.customers.index') }}">Customers</a>
```

### Fix 2: Missing CSRF Token
```blade
<!-- Error: 419 Page Expired -->
<form method="POST" action="{{ route('customers.store') }}">
    <!-- form fields -->
</form>

<!-- Fixed: add CSRF token -->
<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    <!-- form fields -->
</form>
```

### Fix 3: Wrong HTTP Method
```blade
<!-- Error: 405 Method Not Allowed -->
<form method="GET" action="{{ route('customers.store') }}">
    <!-- ... -->
</form>

<!-- Fixed: use POST for store -->
<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    <!-- ... -->
</form>
```

## Useful Artisan Commands

```bash
# List all routes
php artisan route:list

# List routes with specific middleware
php artisan route:list --middleware=auth

# Clear route cache (if routes aren't updating)
php artisan route:clear

# Cache routes (for production performance)
php artisan route:cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Role Hierarchy Reference

From highest to lowest privilege:

1. **developer** - System-wide access, all ISP tenants (Level 0)
2. **super-admin** - Tenancy owner, manages Admins (Level 10)
3. **admin** - ISP owner, manages operations (Level 20)
4. **operator** - Manages Sub-Operators and Customers (Level 30)
5. **sub-operator** - Manages own customers only (Level 40)
6. **manager** - View/Edit if permitted by Admin (Level 50)
7. **accountant** - View-only financial access (Level 70)
8. **staff** - View/Edit if permitted by Admin (Level 80)
9. **customer** - End user/subscriber (identified by `is_subscriber = true`)

Each role can only access routes with matching middleware.

### ⚠️ Deprecated Roles (DO NOT USE)
The following roles are **deprecated** and should NOT be used:
- ❌ **reseller** - Replaced by **operator** (Level 30)
- ❌ **sub-reseller** - Replaced by **sub-operator** (Level 40)
- ❌ **card-distributor** - Functionality removed
- ❌ **group-admin** - Replaced by **admin** (Level 20)
- ❌ **network-user** - Customers are now identified by `is_subscriber = true`

For current role system documentation, see [ROLE_SYSTEM.md](docs/technical/ROLE_SYSTEM.md).

## Related Files
- Routes definition: `routes/web.php`
- CheckRole middleware: `app/Http/Middleware/CheckRole.php`
- Panel controllers: `app/Http/Controllers/Panel/*.php`
- Auth controller: `app/Http/Controllers/AuthController.php`
