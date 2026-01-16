# PR1: Tenancy Skeleton and Roles Implementation

## Overview

This PR implements the foundational multi-tenancy infrastructure and role-based access control for the ISP Solution project.

## What's Included

### 1. Multi-Tenancy Infrastructure

#### Models
- **Tenant Model** (`app/Models/Tenant.php`)
  - Manages tenant data (name, domain, subdomain, database, settings, status)
  - Supports soft deletes
  - Includes relationships to users, IP pools, service packages, and network users
  - Provides `isActive()` helper and `active` scope

- **Role Model** (`app/Models/Role.php`)
  - Manages role definitions with permissions
  - Supports hierarchical levels (0-100)
  - Includes `hasPermission()` and `getPermissions()` helpers
  - Many-to-many relationship with users through pivot table

#### Migrations
- **`create_tenants_table`** - Creates tenants table with domain/subdomain fields
- **`create_roles_table`** - Creates roles and role_user pivot tables
- **`add_tenant_id_to_tables`** - Adds tenant_id foreign key to all relevant tables:
  - users
  - service_packages
  - ip_pools
  - ip_subnets
  - ip_allocations
  - network_users
  - mikrotik_routers

#### Services
- **TenancyService** (`app/Services/TenancyService.php`)
  - Manages current tenant context
  - Resolves tenant by domain/subdomain
  - Provides `runForTenant()` for executing callbacks in tenant context
  - Includes caching for performance

#### Traits
- **BelongsToTenant** (`app/Traits/BelongsToTenant.php`)
  - Automatically sets tenant_id on model creation
  - Adds global scope to filter queries by current tenant
  - Provides `forTenant()` and `allTenants()` scopes
  - Includes tenant relationship

#### Middleware
- **ResolveTenant** (`app/Http/Middleware/ResolveTenant.php`)
  - Resolves tenant from request host
  - Sets current tenant in TenancyService
  - Allows public routes without tenant
  - Returns 404 for requests without valid tenant (when required)

#### Service Provider
- **TenancyServiceProvider** (`app/Providers/TenancyServiceProvider.php`)
  - Registers TenancyService as singleton
  - Registered in `bootstrap/providers.php`

### 2. Role-Based Access Control

#### 9 Roles Seeded
1. **Super Admin** (Level 100)
   - Full system access across all tenants
   - All permissions (*)

2. **Developer** (Level 95)
   - API access, system debugging, logs
   - Settings management

3. **Admin** (Level 90)
   - Tenant administrator
   - Full access within tenant
   - User, role, network, billing, report, settings management

4. **Manager** (Level 70)
   - Operational permissions
   - User and network management
   - View billing and reports

5. **Reseller** (Level 60)
   - Customer management
   - View packages, billing, reports
   - Commission access

6. **Sub-Reseller** (Level 55)
   - Similar to reseller but subordinate
   - Customer and package management
   - Commission view

7. **Staff** (Level 50)
   - Limited operational access
   - View users, network, billing
   - Manage tickets

8. **Card Distributor** (Level 40)
   - Recharge card management
   - Sell cards and view balance

9. **Customer** (Level 10)
   - Self-service access
   - Profile and billing view
   - Ticket creation

#### Role Seeder
- **RoleSeeder** (`database/seeders/RoleSeeder.php`)
  - Seeds all 9 roles with permissions
  - Uses `updateOrCreate` for idempotency
  - Run with: `php artisan db:seed --class=RoleSeeder`

### 3. Configuration Updates

#### Environment Variables
Added to `.env.example`:
```env
# Tenancy Configuration
TENANCY_ENABLED=true
TENANCY_DEFAULT_DOMAIN=localhost
TENANCY_REQUIRE_TENANT=false
```

## Installation & Migration

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Roles
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Register Middleware (Manual Step Required)

⚠️ **IMPORTANT**: The `ResolveTenant` middleware must be manually registered in your application's HTTP kernel.

#### For Laravel 11+:
Add to `bootstrap/app.php`:

```php
use App\Http\Middleware\ResolveTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            ResolveTenant::class,
        ]);
        $middleware->api(append: [
            ResolveTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Usage Examples

### Using BelongsToTenant Trait

Add to any model that should be tenant-scoped:

```php
use App\Traits\BelongsToTenant;

class ServicePackage extends Model
{
    use BelongsToTenant;
    
    // ... rest of model
}
```

### Getting Current Tenant

```php
use App\Services\TenancyService;

$tenancyService = app(TenancyService::class);
$currentTenant = $tenancyService->getCurrentTenant();

if ($currentTenant) {
    echo "Current tenant: " . $currentTenant->name;
}
```

### Running Code in Tenant Context

```php
use App\Services\TenancyService;
use App\Models\Tenant;

$tenancyService = app(TenancyService::class);
$tenant = Tenant::find(1);

$tenancyService->runForTenant($tenant, function() {
    // This code runs in the context of $tenant
    $users = User::all(); // Automatically filtered by tenant
});
```

### Checking User Roles

```php
$user = auth()->user();
$user->roles; // Get all roles

// Check if user has specific role
if ($user->roles->contains('slug', 'admin')) {
    // User is admin
}
```

## Testing

### Create a Test Tenant

```php
use App\Models\Tenant;

$tenant = Tenant::create([
    'name' => 'Test ISP',
    'domain' => 'test-isp.com',
    'subdomain' => 'test',
    'status' => 'active',
]);
```

### Assign Role to User

```php
use App\Models\User;
use App\Models\Role;

$user = User::find(1);
$role = Role::where('slug', 'admin')->first();
$tenant = Tenant::find(1);

$user->roles()->attach($role->id, ['tenant_id' => $tenant->id]);
```

## Security Considerations

1. **Tenant Isolation**: Global scopes ensure queries are automatically filtered by tenant
2. **Automatic Tenant Assignment**: Models with `BelongsToTenant` automatically set tenant_id
3. **Domain/Subdomain Resolution**: Middleware resolves tenant from request host
4. **Permission System**: Role-based permissions for fine-grained access control
5. **Soft Deletes**: Tenants use soft deletes to preserve data integrity

## Next Steps

- PR2: Implement multi-tenant aware IPAM service
- PR3: Add RADIUS and MikroTik services with integration tests
- PR4: Build API endpoints, UI, and CI workflows

## Migration Notes

- All existing data will need tenant_id assignment
- Existing users should be assigned to default tenant
- Review and adjust middleware priority if needed
- Update API routes to handle tenant context

## Breaking Changes

- All models now require tenant_id (nullable for backward compatibility)
- Queries are automatically scoped by tenant (can be bypassed with `allTenants()`)
- Middleware must be registered manually

## Support

For questions or issues:
- Review `docs/TODO_REIMPLEMENT.md` for implementation plan
- Check `MULTI_TENANCY_ISOLATION.md` for isolation details
- Open an issue on GitHub
