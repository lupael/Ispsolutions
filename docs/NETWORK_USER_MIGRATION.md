# NetworkUser Model Elimination - Migration Guide

## Overview

The `NetworkUser` model/table has been eliminated and its functionality merged into the `Customer` (User) model. This makes the Customer entity the **single source of truth** for both CRM and network provisioning (RADIUS/Router).

## What Changed

### Before
- Separate `network_users` table and `NetworkUser` model
- Customers (User) had a 1:1 relationship with NetworkUser
- Network credentials stored separately from customer data
- Manual sync required between Customer and NetworkUser

### After
- Single `users` table contains both CRM and network data
- Customers are identified by `operator_level = 100`
- Network credentials stored directly on User model
- Automatic RADIUS provisioning via model observers

## Architecture

```
Customer (User Model)
    ↓
Single Source of Truth
    ↓
UserObserver → Automatic RADIUS Sync
    ↓
RADIUS Database (radcheck, radreply)
    ↓
Network Devices (MikroTik, Cisco, etc.)
```

## New Fields on User Model

The following fields have been added to the `users` table:

| Field | Type | Purpose |
|-------|------|---------|
| `username` | string | Network authentication username (unique) |
| `radius_password` | string | Plain text password for RADIUS (hidden) |
| `service_type` | enum | pppoe, hotspot, static, dhcp, vpn, cable_tv |
| `connection_type` | enum | pppoe, hotspot, static, dhcp, vpn |
| `billing_type` | enum | prepaid, postpaid, unlimited |
| `device_type` | string | Device type identifier |
| `mac_address` | string | MAC address for binding |
| `ip_address` | string | Assigned IP address |
| `status` | enum | active, inactive, suspended |
| `expiry_date` | date | Service expiry date |
| `zone_id` | foreign key | Network zone assignment |

## RADIUS Integration

### Automatic Provisioning

The `UserObserver` automatically handles RADIUS provisioning:

1. **Customer Created** → RADIUS account created
2. **Customer Updated** → RADIUS attributes updated
3. **Customer Suspended** → RADIUS account removed
4. **Customer Deleted** → RADIUS account removed
5. **Customer Restored** → RADIUS account re-created

### Manual RADIUS Operations

You can still manually sync if needed:

```php
// Sync customer to RADIUS
$customer->syncToRadius(['password' => $password]);

// Update RADIUS attributes
$customer->updateRadius(['Framed-IP-Address' => '10.0.0.1']);

// Remove from RADIUS
$customer->removeFromRadius();

// Check if network customer
if ($customer->isNetworkCustomer()) {
    // Customer has network service
}
```

## Migration Steps

### 1. Run Migrations (IN ORDER)

```bash
# Step 1: Add network fields to users table
php artisan migrate --path=database/migrations/2026_01_27_041542_add_network_fields_to_users_table.php

# Step 2: Copy data from network_users to users
php artisan migrate --path=database/migrations/2026_01_27_041729_migrate_network_user_data_to_users_table.php

# Step 3: VERIFY DATA INTEGRITY
# Check that all network_users data exists in users table

# Step 4: Drop network_users table (future release)
# Note: The drop table migration is not included in this PR for staged deprecation
# The NetworkUser model and table remain active for backward compatibility
# Drop migration will be introduced in a future release after all usages are migrated
```

### 2. Verify Data Migration

```sql
-- Check record counts match
SELECT COUNT(*) FROM network_users;
SELECT COUNT(*) FROM users WHERE operator_level = 100 AND service_type IS NOT NULL;

-- Check specific customer data
SELECT 
    nu.username, 
    nu.service_type, 
    nu.status,
    u.username as user_username,
    u.service_type as user_service_type,
    u.status as user_status
FROM network_users nu
JOIN users u ON nu.user_id = u.id
WHERE nu.username != u.username OR nu.service_type != u.service_type;
```

### 3. Update Custom Code

If you have custom code referencing `NetworkUser`, update it:

```php
// OLD CODE
$networkUser = NetworkUser::where('username', $username)->first();
$networkUser->status = 'suspended';
$networkUser->save();

// NEW CODE  
$customer = User::where('operator_level', 100)
    ->where('username', $username)
    ->first();
$customer->status = 'suspended';
$customer->save();
// RADIUS sync happens automatically via observer
```

## Controller Changes

### Deprecated Routes

The following routes have been deprecated:

- `GET /panel/admin/network-users`
- `GET /panel/admin/network-users/create`
- `POST /panel/admin/network-users`
- `GET /panel/admin/network-users/{id}`
- `GET /panel/admin/network-users/{id}/edit`
- `PUT /panel/admin/network-users/{id}`
- `DELETE /panel/admin/network-users/{id}`
- `GET /panel/manager/network-users`
- `GET /panel/staff/network-users`

**Use customer management routes instead.**

### Updated Controllers

All controllers now work with the User model:

- `AdminController` - Dashboard stats use User queries
- `CustomerWizardController` - Creates customers with network fields
- `BulkOperationsController` - Bulk operations on User model
- `API Controllers` - All API endpoints updated

### API Backward Compatibility

The `NetworkUserController` API endpoint remains for backward compatibility but internally uses the User model:

```
GET  /api/v1/network-users
POST /api/v1/network-users
GET  /api/v1/network-users/{id}
PUT  /api/v1/network-users/{id}
DELETE /api/v1/network-users/{id}
```

## Role-Based Permissions

### Admin (operator_level ≤ 20)
- ✅ Full access to all customer actions
- ✅ Create, edit, delete customers
- ✅ Suspend, activate customers
- ✅ Change packages, MAC binding
- ✅ Manage all network settings

### Operator (operator_level = 30)
- ✅ Edit customers (with permission)
- ✅ Create tickets
- ✅ Internet history
- ✅ Check usage
- ✅ View tickets & logs
- ✅ Activate (if paid/within validity)
- ✅ Suspend (with permission)
- ✅ Advance payment
- ✅ Change package (with balance deduction)
- ✅ MAC binding (with permission)
- ✅ Send SMS (if SMS balance available)
- ✅ Payment link (if SMS balance available)

### Sub-Operator (operator_level = 40)
- ✅ Same as Operator but only for own customers
- ❌ Cannot create operators
- ❌ Cannot manage packages

## Dashboard Queries

### Before (NetworkUser)
```php
'total_network_users' => NetworkUser::count(),
'pppoe_customers' => NetworkUser::where('service_type', 'pppoe')->count(),
'suspended_customers' => NetworkUser::where('status', 'suspended')->count(),
```

### After (User)
```php
'total_network_users' => User::where('operator_level', 100)
    ->whereNotNull('service_type')->count(),
'pppoe_customers' => User::where('operator_level', 100)
    ->where('service_type', 'pppoe')->count(),
'suspended_customers' => User::where('operator_level', 100)
    ->where('status', 'suspended')->count(),
```

## Customer Creation Example

### Before
```php
// Create customer
$customer = User::create([...]);

// Create network user
$networkUser = NetworkUser::create([
    'user_id' => $customer->id,
    'username' => $username,
    'password' => $password,
    'service_type' => 'pppoe',
]);

// Manually sync to RADIUS
$radiusService->syncUser($networkUser);
```

### After
```php
// Create customer with network fields
$customer = User::create([
    // Customer fields
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'operator_level' => 100,
    
    // Network fields
    'username' => $username,
    'radius_password' => $password,
    'service_type' => 'pppoe',
    'status' => 'active',
]);

// RADIUS sync happens automatically via UserObserver
```

## Benefits

1. **Single Source of Truth**: Customer is the only entity to manage
2. **Automatic Provisioning**: RADIUS sync happens automatically
3. **Cleaner Architecture**: No duplicate concepts
4. **Better Tenant Isolation**: All queries scoped to `tenant_id`
5. **Simpler UI/UX**: Only manage "Customers", not "Customers" + "Network Users"
6. **Consistent Data**: No sync issues between Customer and NetworkUser
7. **Better Performance**: One less JOIN in most queries

## Rollback Plan

If you need to rollback:

1. Restore database from backup taken before migration
2. Revert code to previous commit
3. Run `composer install` to restore dependencies

**Note**: The migrations are designed to be reversible, but the `down()` method for data migration cannot restore network_users table data. Always take a backup before migrating.

## Troubleshooting

### RADIUS Not Syncing

Check the logs:
```bash
tail -f storage/logs/laravel.log | grep RADIUS
```

Manually sync:
```php
$customer = User::find($customerId);
$customer->syncToRadius(['password' => $customer->radius_password]);
```

### Data Missing After Migration

Restore from backup and re-run migration:
```bash
# Restore database
mysql -u user -p database < backup.sql

# Re-run migrations
php artisan migrate:rollback --step=2
php artisan migrate
```

### Customer Not Authenticating

Check RADIUS database:
```sql
SELECT * FROM radcheck WHERE username = 'customer_username';
SELECT * FROM radreply WHERE username = 'customer_username';
```

Verify customer status:
```php
$customer = User::where('username', 'customer_username')->first();
echo $customer->status; // Should be 'active'
echo $customer->is_active; // Should be true
```

## Support

For issues or questions, refer to:
- [ROLES_AND_PERMISSIONS.md](docs/ROLES_AND_PERMISSIONS.md)
- [RADIUS_SETUP_GUIDE.md](RADIUS_SETUP_GUIDE.md)
- GitHub Issues

---

**Last Updated**: 2026-01-27
**Migration Version**: 1.0
