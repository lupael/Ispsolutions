# Multi-Tenancy Isolation Enhancement Documentation

## Overview

The enhanced multi-tenancy isolation system provides automatic tenant scoping for all database queries, ensuring complete data separation between tenants while allowing developer access for administration.

## Features

### 1. Automatic Query Scoping
All models using the `BelongsToTenant` trait automatically filter queries to only return data for the current tenant context.

### 2. Automatic Tenant Assignment
When creating new records, the `tenant_id` is automatically assigned from the current tenant context.

### 3. Developer Bypass
Users with the `is_developer` flag can access data across all tenants for administration purposes.

### 4. Explicit Tenant Filtering
Additional scopes available for explicit tenant filtering when needed.

## Implementation

### Using the BelongsToTenant Trait

Add the trait to any model that should be tenant-scoped:

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToTenant;
    
    // Your model code...
}
```

### Trait Features

The `BelongsToTenant` trait provides:

1. **Global Scope**: Automatically filters all queries by `tenant_id`
2. **Auto-Assignment**: Sets `tenant_id` on model creation
3. **Tenant Relationship**: Adds `tenant()` relationship method
4. **Scopes**: `forTenant($id)` and `withoutTenantScope()`
5. **Validation**: `isOwnedByCurrentTenant()` method

## Usage Examples

### Automatic Scoping

```php
// Current tenant is set in middleware
app()->instance('tenant', $tenant);

// All queries automatically scoped to current tenant
$invoices = Invoice::all(); // Only returns current tenant's invoices

// Create invoice - tenant_id automatically assigned
$invoice = Invoice::create([
    'customer_id' => $customer->id,
    'amount' => 100.00,
    // tenant_id is automatically set
]);
```

### Developer Access

```php
// Developer user can see all tenant data
$developer = User::where('is_developer', true)->first();
Auth::login($developer);

// Returns invoices from all tenants
$allInvoices = Invoice::all();
```

### Explicit Tenant Filtering

```php
// Query specific tenant
$tenant1Invoices = Invoice::forTenant($tenant1->id)->get();

// Query without tenant scope (requires developer access)
$allInvoices = Invoice::withoutTenantScope()->get();
```

### Ownership Validation

```php
$invoice = Invoice::find($id);

// Check if invoice belongs to current tenant
if ($invoice->isOwnedByCurrentTenant()) {
    // Safe to display/edit
}
```

## Middleware Integration

The `TenantMiddleware` sets the tenant context:

```php
// In TenantMiddleware
$tenant = Tenant::where('slug', $tenantSlug)->first();
$request->attributes->set('tenant', $tenant);
app()->instance('tenant', $tenant);
```

## Models with Tenant Isolation

The following models have been enhanced with automatic tenant scoping:

- Invoice
- Payment
- ServicePackage
- Ticket
- Router
- NetworkSession
- CommissionDistribution
- Olt
- OltOnu
- RechargeCard
- CustomerPackage

## Security Benefits

1. **Automatic Protection**: No risk of accidentally querying cross-tenant data
2. **Developer Control**: Centralized admin access when needed
3. **Explicit Bypass**: Clear indication when querying across tenants
4. **Relationship Safety**: Related models automatically scoped
5. **Creation Safety**: New records always assigned correct tenant

## Testing

### Test Tenant Isolation

```php
public function test_tenant_scoped_models_only_return_tenant_data()
{
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    app()->instance('tenant', $tenant1);
    
    $invoice1 = Invoice::factory()->create(['tenant_id' => $tenant1->id]);
    $invoice2 = Invoice::factory()->create(['tenant_id' => $tenant2->id]);
    
    $invoices = Invoice::all();
    
    $this->assertCount(1, $invoices);
    $this->assertEquals($tenant1->id, $invoices->first()->tenant_id);
}
```

### Test Developer Access

```php
public function test_developer_can_access_all_tenant_data()
{
    $developer = User::factory()->create(['is_developer' => true]);
    $this->actingAs($developer);
    
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    
    Invoice::factory()->create(['tenant_id' => $tenant1->id]);
    Invoice::factory()->create(['tenant_id' => $tenant2->id]);
    
    $invoices = Invoice::all();
    
    $this->assertCount(2, $invoices);
}
```

## Migration from Old System

If migrating from non-scoped models:

1. Add `BelongsToTenant` trait to model
2. Ensure `tenant_id` column exists
3. Test existing queries still work
4. Add tests for tenant isolation
5. Review any direct SQL queries

## Performance Considerations

- Global scopes add WHERE clauses to all queries
- Minimal performance impact (single WHERE condition)
- Indexes on `tenant_id` recommended for large tables
- Relationships automatically benefit from scoping

## Troubleshooting

### Issue: Model not returning data

**Solution**: Check if tenant context is set:
```php
$tenant = app('tenant');
if (!$tenant) {
    // Tenant not set - ensure middleware is applied
}
```

### Issue: Need to query across tenants

**Solution**: Use `withoutTenantScope()`:
```php
$allData = Model::withoutTenantScope()->get();
```

### Issue: Getting wrong tenant's data

**Solution**: Verify correct tenant is set in middleware:
```php
// In middleware
Log::info('Tenant set: ' . $tenant->id);
```

## Best Practices

1. **Always use the trait** for tenant-scoped models
2. **Never bypass scoping** unless absolutely necessary
3. **Test tenant isolation** for new features
4. **Log tenant context** for audit trails
5. **Use explicit scopes** when querying specific tenants

## API Endpoints

All tenant-based endpoints automatically scoped:

```
GET /invoices              - Returns current tenant's invoices
POST /invoices             - Creates invoice for current tenant
GET /packages              - Returns current tenant's packages
GET /customers             - Returns current tenant's customers
```
