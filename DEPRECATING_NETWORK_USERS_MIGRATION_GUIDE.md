# Deprecating network_users - Migration Guide

## Overview

This document describes the changes made to deprecate the `network_users` terminology and refactor the system to use `Customer` with the new `is_subscriber` flag.

## What Changed

### Before
- `network_users` table stored customer network credentials
- `network_user_sessions` table tracked customer sessions  
- Foreign keys used `network_user_id`
- Customers identified by `operator_level = 100`
- Customers treated as part of the administrative hierarchy

### After
- `customers` table stores customer network credentials (renamed from `network_users`)
- `customer_sessions` table tracks customer sessions (renamed from `network_user_sessions`)
- Foreign keys use `customer_id` (renamed from `network_user_id`)
- Customers identified by `is_subscriber = true` flag
- Customers are external subscribers, NOT part of the administrative hierarchy (Levels 0-80)

## Database Changes

### Tables Renamed
1. `network_users` → `customers`
2. `network_user_sessions` → `customer_sessions`

### Columns Renamed
- `onus.network_user_id` → `onus.customer_id`
- `hotspot_login_logs.network_user_id` → `hotspot_login_logs.customer_id`

### New Column Added
- `users.is_subscriber` (boolean, default: false, indexed)

### Data Migration
- All users with `operator_level = 100` have been updated:
  - `is_subscriber` set to `true`
  - `operator_level` set to `null`

## Models Updated

### NetworkUser Model
- Now references `customers` table instead of `network_users`
- All relationships and functionality remain the same

### NetworkUserSession Model
- Now references `customer_sessions` table instead of `network_user_sessions`

### Customer Model
- Uses `is_subscriber = true` scope instead of `operator_level = 100`
- Sets `operator_level` to `null` for new customers
- Automatically generates `customer_id` on creation

### User Model
- Added `is_subscriber` to fillable fields and casts
- Deprecated `OPERATOR_LEVEL_CUSTOMER` constant (will be removed in v2.0)
- Updated documentation to reflect new hierarchy

### Other Models
- `Onu`: Updated to use `customer_id` foreign key
- `HotspotLoginLog`: Updated to use `customer_id` foreign key

## Code Changes

### Controllers (22 files updated)
All references to `operator_level = 100` replaced with `is_subscriber = true`:
- API Controllers: DataController, NetworkUserController, RadiusController, etc.
- Panel Controllers: AdminController, ManagerController, SuperAdminController, etc.
- Services: OperatorStatsCacheService, CustomerCacheService, BillingProfileCacheService

### Validation Requests
- `UpdateNetworkUserRequest`: Now validates against `customers` table
- `StoreNetworkUserRequest`: Sets `is_subscriber = true` instead of `operator_level = 100`
- `BulkUserUpdateRequest`: Validates IDs against `customers` table

### Observers
- `UserObserver`: Updated to use `is_subscriber` instead of `operator_level = 100`

## Migration Steps

### CRITICAL: Maintenance Mode Required

These migrations rename tables and update data. Run with application in maintenance mode:

```bash
# Step 1: Put application in maintenance mode
php artisan down

# Step 2: Run migrations (they will execute in order)
php artisan migrate

# Step 3: Verify migration success
# Check that tables were renamed and data migrated correctly

# Step 4: Bring application back online
php artisan up
```

### Migrations Executed (in order)

1. `2026_01_30_200600_rename_network_users_to_customers.php`
   - Renames `network_users` to `customers`
   - Renames `network_user_sessions` to `customer_sessions`

2. `2026_01_30_200700_rename_network_user_id_to_customer_id.php`
   - Renames `network_user_id` to `customer_id` in related tables
   - Updates foreign key constraints

3. `2026_01_30_200800_add_is_subscriber_to_users_table.php`
   - Adds `is_subscriber` column to `users` table
   - Migrates data: `operator_level = 100` → `is_subscriber = true, operator_level = null`

## Verification

### After Migration, Verify:

1. **Tables renamed correctly:**
```sql
SHOW TABLES LIKE 'customers';
SHOW TABLES LIKE 'customer_sessions';
-- Should NOT exist anymore:
-- SHOW TABLES LIKE 'network_users';
```

2. **Data migrated correctly:**
```sql
-- Check that customers have is_subscriber = true and operator_level IS NULL
SELECT id, name, email, is_subscriber, operator_level 
FROM users 
WHERE is_subscriber = true 
LIMIT 10;

-- Count should match old customer count
SELECT COUNT(*) FROM users WHERE is_subscriber = true;
```

3. **Foreign keys updated:**
```sql
-- Check onus table
SHOW CREATE TABLE onus;  -- Should reference customers.id via customer_id

-- Check hotspot_login_logs table
SHOW CREATE TABLE hotspot_login_logs;  -- Should reference customers.id via customer_id
```

## Backward Compatibility

### NetworkUser Model
- Still exists and can be used in code
- Now points to `customers` table
- Provides transparent migration path

### API Endpoints
- API routes remain at `/api/v1/network-users/*` for backward compatibility
- Internally use NetworkUser model which now points to `customers` table

### Deprecated Methods
The following relationship methods are deprecated but still work:
- `Onu::networkUser()` (use `customer()` instead)
- `HotspotLoginLog::networkUser()` (use `customer()` instead)

## Breaking Changes

⚠️ **The following code patterns will break:**

1. **Direct table references:**
```php
// BEFORE (BROKEN)
DB::table('network_users')->where(...)->get();

// AFTER (FIXED)
DB::table('customers')->where(...)->get();
```

2. **Foreign key references:**
```php
// BEFORE (BROKEN)
$table->foreignId('network_user_id')...

// AFTER (FIXED)
$table->foreignId('customer_id')...
```

3. **Operator level checks:**
```php
// BEFORE (BROKEN)
User::where('operator_level', 100)->get();

// AFTER (FIXED)
User::where('is_subscriber', true)->get();
```

4. **Customer identification:**
```php
// BEFORE (BROKEN)
if ($user->operator_level === 100) {
    // Customer logic
}

// AFTER (FIXED)
if ($user->is_subscriber === true) {
    // Customer logic
}
```

## Role Hierarchy Changes

### Old Hierarchy (Deprecated)
- Level 0: Developer
- Level 10: Super Admin
- Level 20: Admin
- Level 30: Operator
- Level 40: Sub-Operator
- Level 50: Manager
- Level 70: Accountant
- Level 80: Staff
- **Level 100: Customer** ❌ (DEPRECATED)

### New Hierarchy
- Level 0: Developer
- Level 10: Super Admin
- Level 20: Admin
- Level 30: Operator (manages customers)
- Level 40: Sub-Operator (manages customers)
- Level 50: Manager
- Level 70: Accountant
- Level 80: Staff
- **Customers: `is_subscriber = true`, `operator_level = null`** ✅ (NEW)

**Key Change:** Customers are now external subscribers and are NOT part of the administrative hierarchy. They are managed as assets/subscribers by Operators and Sub-Operators.

## Testing Recommendations

1. **Test Customer Creation:**
   - Verify new customers have `is_subscriber = true` and `operator_level = null`

2. **Test Customer Queries:**
   - Dashboard statistics
   - Customer lists
   - Filtering and search

3. **Test RADIUS Integration:**
   - Customer provisioning
   - Updates sync to RADIUS
   - Suspensions remove from RADIUS

4. **Test Relationships:**
   - Customer → Package
   - Customer → Sessions
   - Customer → ONUs
   - Customer → Invoices

5. **Test API Endpoints:**
   - `/api/v1/network-users` (backward compatible)
   - Customer CRUD operations
   - Bulk operations

## Rollback Plan

If you need to rollback:

1. **Restore from backup** (RECOMMENDED)
   ```bash
   mysql -u user -p database < backup_before_migration.sql
   ```

2. **Run migration rollback:**
   ```bash
   php artisan migrate:rollback --step=3
   ```
   
   This will:
   - Revert `is_subscriber` changes (set back to `operator_level = 100`)
   - Rename `customer_id` columns back to `network_user_id`
   - Rename `customers` and `customer_sessions` tables back to original names

## Support

For issues or questions:
- Check the logs: `storage/logs/laravel.log`
- Review migration output for errors
- Verify database state with SQL queries above
- Consult the development team

---

**Migration Date:** 2026-01-30
**Version:** 1.0
**Breaking Changes:** Yes (requires maintenance mode)
