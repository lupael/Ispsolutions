# Network Users Data Migration - Deployment Guide

## Overview
This guide walks through migrating orphaned `network_users` records into the unified `users` table and completing the deprecation of the `network_users` legacy structure.

## Pre-Migration Checklist

1. **Backup Database** (CRITICAL)
   ```bash
   mysqldump -u <user> -p <database> > network_users_migration_backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Verify network_users table exists and count records**
   ```bash
   SELECT COUNT(*) FROM network_users;
   SELECT COUNT(*) FROM network_users WHERE user_id IS NULL;
   ```

3. **Review existing network_users schema**
   ```bash
   DESCRIBE network_users;
   ```

## Migration Steps (In Order)

### Step 1: Run New Migrations

Execute the two new migrations that add infrastructure for the migration:

```bash
php artisan migrate
```

This will:
- ✅ Add `legacy_network_user_id` column to `users` table (from migration `2026_02_08_010000_add_legacy_network_user_id_to_users.php`)
- ✅ Register subscription fields `subscription_plan_id`, `expires_at` if not already present
- ✅ Register other hierarchy/RBAC changes

**Verification:**
```bash
php artisan tinker
> Schema::getColumnListing('users');  // verify legacy_network_user_id is present
```

### Step 2: Run Network Users Migration Command

Execute the custom Artisan command to migrate orphaned network_users:

```bash
php artisan migrate:network-users --chunk=500
```

This command:
- Finds all `network_users` rows with `user_id IS NULL` (orphaned records)
- Creates corresponding `users` records, mapping network credential fields
- Links `network_users.user_id` back to the newly created user
- Sets `users.legacy_network_user_id` to track the original network_users ID
- Operates in defined chunks (default 500) to avoid memory issues
- Is **idempotent** — safe to run multiple times

**Output Example:**
```
Found 1234 network_users rows with no linked user. Processing in chunks of 500...
Completed. Processed 1234 records.
```

### Step 3: Verify Data Integrity

Run verification checks:

```bash
# Check all network_users are now linked
SELECT COUNT(*) FROM network_users WHERE user_id IS NULL;  -- should be 0

# Check legacy IDs are recorded
SELECT COUNT(DISTINCT legacy_network_user_id) FROM users WHERE legacy_network_user_id IS NOT NULL;

# Spot-check a few records
SELECT nu.id, nu.username, u.id, u.legacy_network_user_id 
FROM network_users nu 
LEFT JOIN users u ON nu.user_id = u.id 
LIMIT 10;
```

### Step 4: Update References in Other Tables (If Any)

Check if other tables reference `network_users` directly:

```bash
# Find foreign keys to network_users
SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME = 'network_users';
```

Common references to update:
- `radius_sessions` (if exists) → use `users` instead
- `network_user_sessions` → update to `users`
- Any job/queue records → update references

### Step 5: Run Application Tests

Verify the application still works with migrated data:

```bash
php artisan test --group=migration
php artisan test tests/Integration/NetworkUserIntegrationTest.php
```

### Step 6: Deploy & Clean Up (Later Release)

**For this release:** Stop here. The `NetworkUser` shim model remains in place for backward compatibility.

**For future release (after monitoring):**

Once you're confident the data migration is stable (monitor for 1-2 weeks):

1. **Remove NetworkUser shim:**
   ```bash
   rm app/Models/NetworkUser.php
   ```
   Update all code to use `Customer` or `User` instead.

2. **Drop legacy columns from users:**
   ```bash
   php artisan migrate
   ```
   (This runs the `2026_02_08_001000_drop_legacy_columns.php` migration)

3. **Drop network_users table** (point-of-no-return):
   Create a new migration:
   ```bash
   php artisan make:migration drop_network_users_table
   ```
   Add to migration:
   ```php
   Schema::dropIfExists('network_users');
   Schema::dropIfExists('network_user_sessions');  // if exists
   ```

## Rollback Plan (If Issues Occur)

If something goes wrong **before step 6**:

1. **Restore database from backup:**
   ```bash
   mysql -u <user> -p <database> < network_users_migration_backup_YYYYMMDD_HHMMSS.sql
   ```

2. **Re-run migrations from scratch:**
   ```bash
   php artisan migrate:refresh  # or migrate:rollback then migrate
   ```

3. **Investigate and re-run migration command:**
   ```bash
   php artisan migrate:network-users --chunk=100  // smaller chunks for debugging
   ```

## Post-Migration Monitoring

Monitor these metrics in production:

- **User login success rate** - should remain > 99.5%
- **RADIUS authentication** - should remain 100%
- **Bandwidth collection jobs** - should not error
- **Customer report generation** - should not error
- **Customer billing/invoicing** - verify accuracy

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i network
```

## Environment Issues (Dev Container)

**Note:** The development container currently has an OpenSSL PHP issue. To run these commands:

Option A: Run on production-like environment (staging/production)
Option B: Fix PHP setup in container:
```bash
apt-get update && apt-get install -y libssl1.1
```

## Files Created/Modified

**New Files:**
- `database/migrations/2026_02_08_010000_add_legacy_network_user_id_to_users.php` - Adds tracking column
- `app/Console/Commands/MigrateNetworkUsers.php` - Migration command
- `database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php` - B2B2B hierarchy fields
- `database/migrations/2026_02_08_001000_drop_legacy_columns.php` - Cleanup (run after monitoring)

**Modified Files:**
- `app/Models/User.php` - Added subscription fields and relations
- `app/Models/NetworkUser.php` - Shim model for backward compatibility
- `app/Services/WidgetCacheService.php` - Updated to use Customer instead of NetworkUser
- `routes/web.php` - Added tenant/subscription middleware to panel routes
- `app/Http/Middleware/CheckSubscription.php` - Subscription enforcement (new)
- `app/Http/Controllers/DeveloperController.php` - Updated view paths
- `app/Http/Controllers/SuperAdminController.php` - Updated view paths
- `app/Http/Controllers/AdminController.php` - Updated view paths

## Verification Command

After migration, run this command to get a summary:

```php
// In tinker or test
$totalUsers = \App\Models\User::count();
$usersWithLegacyId = \App\Models\User::whereNotNull('legacy_network_user_id')->count();
$allNetworkUsersLinked = \DB::table('network_users')->whereNull('user_id')->count() === 0;

echo "Total users: $totalUsers\n";
echo "Users with legacy network_user_id: $usersWithLegacyId\n";
echo "All network_users linked: " . ($allNetworkUsersLinked ? 'YES' : 'NO') . "\n";
```

## Rollback Checklist (If Deciding to Abort)

If you decide not to proceed with full deprecation:

- [ ] Keep `network_users` table (don't drop)
- [ ] Keep `NetworkUser` model
- [ ] Keep migrations up to step 5
- [ ] Document decision in CHANGELOG.md
- [ ] Revert shim-related code changes manually if needed
