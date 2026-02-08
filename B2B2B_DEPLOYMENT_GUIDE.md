# B2B2B Multi-Tenancy & Network User Deprecation - Deployment Guide

## Overview

This guide documents the complete deployment workflow for the B2B2B multi-tenancy implementation, including tenant-scoped access, RBAC middleware, subscription enforcement, and safe migration from `network_users` to `users`.

---

## Phase 1: Database Migrations (Immediate - Week 1)

### Prerequisites
- Database backup
- Read-only replicas for testing (optional but recommended)
- Downtime window: 30-60 seconds per migration

### Step 1A: Add B2B2B subscription fields to users table

```bash
php artisan migrate --path=database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php
```

**What it does:**
- Adds `subscription_plan_id` (FK → `subscription_plans`) to `users`
- Adds `expires_at` timestamp for subscription expiry tracking
- Creates index on `subscription_plan_id` for performance

**Verification:**
```sql
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' AND COLUMN_NAME IN ('subscription_plan_id', 'expires_at');
-- Should return 2 rows
```

### Step 1B: Track legacy network_user IDs

```bash
php artisan migrate --path=database/migrations/2026_02_08_010000_add_legacy_network_user_id_to_users.php
```

**What it does:**
- Adds `legacy_network_user_id` column to map old records to new ones (safe rollback)
- Idempotent: can be run safely anytime

### Step 1C: Drop deprecated legacy columns (optional - do this only if you never used them)

```bash
php artisan migrate --path=database/migrations/2026_02_08_001000_drop_legacy_columns.php
```

**What it does:**
- Drops `legacy_status` and `old_role_id` from `users` (if they exist)
- Removes obsolete roles: `reseller`, `sub-reseller`, `legacy_role`

**Decision point:** Only run this if your system never actually used these columns. If unsure, skip for now.

---

## Phase 2: Migrate network_users → users (Week 2)

### Step 2A: Run the data migration command

```bash
php artisan migrate:network-users --chunk=1000
```

**What it does:**
1. Finds all `network_users` rows with `user_id = NULL` (orphaned records)
2. Creates a corresponding `User` record for each orphan
3. Maps fields:
   - `network_users.username` → `users.username`
   - `network_users.password` → `users.password` (or `radius_password` if column exists)
   - `network_users.service_type` → `users.service_type`
   - `network_users.connection_type` → `users.connection_type`
   - `network_users.status` → `users.status`
   - And: `billing_type`, `device_type`, `mac_address`, `ip_address`, `expiry_date`, `zone_id`
4. Sets `operator_level = 100` (Customer level by convention)
5. Stores original network_user ID in `legacy_network_user_id` for mapping
6. Links back: updates `network_users.user_id` → new User.id

**Chunk size:** Default 500 (configurable with `--chunk=N`)

**Example:**
```bash
# Small batches for high-volume migrations
php artisan migrate:network-users --chunk=100

# Larger batches for fewer records
php artisan migrate:network-users --chunk=2000
```

### Step 2B: Verify the migration

```sql
-- Check total network_users
SELECT COUNT(*) as total_network_users FROM network_users;

-- Check how many are now linked to users
SELECT COUNT(*) as linked_to_users FROM network_users WHERE user_id IS NOT NULL;

-- Check orphaned records (should be 0)
SELECT COUNT(*) as orphaned FROM network_users WHERE user_id IS NULL;

-- Verify customer users were created
SELECT COUNT(*) as customer_users FROM users WHERE operator_level = 100;

-- Sample check: verify a migrated user has correct data
SELECT n.username, u.username, u.legacy_network_user_id, u.operator_level
FROM network_users n
INNER JOIN users u ON n.user_id = u.id
LIMIT 5;
```

### Step 2C: Monitor for 1-2 weeks

After running the migration:
1. Monitor application logs for errors
2. Check RADIUS authentication (if applicable)
3. Verify customer logins and session creation
4. Monitor database performance (CPU, query times)
5. Check for duplicate usernames or unexpected errors in `legacy` logs

---

## Phase 3: Deploy Code Changes (Week 1-2, in parallel)

### Step 3A: Ensure all code uses Customer/User model

Search and replace `NetworkUser` usage in your application:

```bash
grep -r "NetworkUser" app/ --include="*.php" | grep -v "namespace\|Models/NetworkUser" | head -20
```

Update references to use `User::class` or `Customer::class` instead:
- [WidgetCacheService](WidgetCacheService.php) ✅ (already updated)
- [DeveloperController.php] - check view paths ✅ (already updated to panels.developer.*)
- [SuperAdminController.php] - check view paths ✅ (already updated to panels.super-admin.*)
- [AdminController.php] - check view paths ✅ (already updated to panels.admin.*)
- Any custom jobs/commands that reference network_users directly

### Step 3B: Verify subscription middleware is active

Routes using `subscription` middleware:

```bash
grep -r "subscription" routes/ --include="*.php"
```

Should see: `'subscription' => CheckSubscription::class` registered in `app/Http/Kernel.php`

Verify route groups include it:
```php
Route::middleware(['auth', 'resolve.tenant', 'subscription', 'role:super-admin'])
    ->group(function () {
        // super-admin routes
    });
```

### Step 3C: Ensure all panel views use new paths

Old paths (should NOT be in use):
- ❌ `resources/views/developer/*`
- ❌ `resources/views/super-admin/*`
- ❌ `resources/views/admin/*`

New canonical paths (should be used):
- ✅ `resources/views/panels/developer/*`
- ✅ `resources/views/panels/super-admin/*`
- ✅ `resources/views/panels/admin/*`

Verify:
```bash
[ -d resources/views/developer ] && echo "ERROR: Old developer views still exist" || echo "OK"
[ -d resources/views/super-admin ] && echo "ERROR: Old super-admin views still exist" || echo "OK"
```

---

## Phase 4: Final Cleanup (Week 3+, after monitoring period)

### Step 4A: Remove NetworkUser shim model

The `NetworkUser` model was created as a backward-compatibility shim during migration. After deployment is stable and no errors occur:

```bash
rm app/Models/NetworkUser.php
```

Then update any remaining imports from `App\Models\NetworkUser` to `App\Models\User` or `App\Models\Customer`.

### Step 4B: Drop network_users table (ONE-WAY)

**⚠️ WARNING:** This is irreversible without a database backup.

```bash
# First, ensure all data is migrated and monitoring shows no issues
php artisan migrate --path=database/migrations/2026_02_08_020000_drop_network_users_table.php
```

**What it does:**
1. Validates that no orphaned `network_users` records exist
2. Logs record counts for audit trail
3. Drops `network_users` table
4. Drops `network_user_sessions` table (if it exists)

**Before running:**
- ✅ Run `migrate:network-users` successfully
- ✅ Verify zero orphaned records (see Step 2B)
- ✅ Monitor production for 1-2 weeks without errors
- ✅ Backup database
- ✅ Clear any cached queries/views that reference network_users

**Rollback:** Restore from database backup (only option)

---

## Testing & Validation

### Run test suites

```bash
# Test the migration command
php artisan test tests/Unit/Console/Commands/MigrateNetworkUsersCommandTest.php

# Test subscription middleware
php artisan test tests/Feature/Middleware/CheckSubscriptionMiddlewareTest.php

# Test B2B2B fields migration
php artisan test tests/Feature/Migrations/AddB2b2bFieldsToUsersTableTest.php

# Run all tests
php artisan test
```

### Manual QA checklist

- [ ] Developer can access `/developer` panel
- [ ] Super-admin can access `/super-admin` panel (with valid subscription)
- [ ] Expired super-admin sees 403 error
- [ ] Admin under super-admin can access `/admin`
- [ ] Customer can access `/customer` panel
- [ ] RADIUS authentication works for migrated customers
- [ ] Session tracking works
- [ ] Billing jobs complete without errors
- [ ] No "network_users" references in error logs

---

## Rollback Plan

If issues occur during any phase:

### Phase 1 Rollback (B2B2B fields)
```bash
php artisan migrate:rollback --path=database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php
```

Clear `subscription_plan_id` and `expires_at` from cache/code.

### Phase 2 Rollback (Data migration)
```bash
# Only option: restore database from backup taken before Step 2A
# The migrate:network-users command cannot be safely reversed
```

### Phase 4 Rollback (Table drop)
```bash
# Only option: restore database from backup
# There is no automatic recovery after dropping network_users
```

---

## Troubleshooting

### Issue: `migrate:network-users` exits with code 1

**Check:**
```bash
# Ensure tables exist
php artisan db:table users
php artisan db:table network_users

# Check for any orphaned records before running
mysql -e "SELECT COUNT(*) FROM network_users WHERE user_id IS NULL;"
```

**Solution:** Run migrations first if tables don't exist.

---

### Issue: "Orphaned network_users records detected"

**Cause:** Some `network_users` have NULL `user_id` and couldn't be auto-linked.

**Solution:**
```sql
-- Identify orphaned records
SELECT id, username FROM network_users WHERE user_id IS NULL;

-- Manually link or delete
UPDATE network_users SET user_id = <correct_user_id> WHERE id = <network_user_id>;
-- Then re-run: php artisan migrate:network-users
```

---

### Issue: Subscription middleware blocks legitimate access

**Check:**
```sql
-- Verify super-admin has subscription
SELECT id, name, operator_level, subscription_plan_id, expires_at 
FROM users WHERE operator_level = 2;

-- Check if subscription_plan_id exists
SELECT COUNT(*) FROM subscription_plans;
```

**Solution:** Assign subscription to super-admin:
```sql
UPDATE users 
SET subscription_plan_id = 1, expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR) 
WHERE id = <super_admin_id>;
```

---

### Issue: Old view paths still rendering

**Check:**
```bash
grep -r "resources/views/developer" app/ --include="*.php"
```

**Solution:** Update controller view() calls to use new `panels.developer.*` path.

---

## Files & References

### Migrations created:
- `2026_02_08_000000_add_b2b2b_fields_to_users_table.php`
- `2026_02_08_001000_drop_legacy_columns.php`
- `2026_02_08_010000_add_legacy_network_user_id_to_users.php`
- `2026_02_08_020000_drop_network_users_table.php`

### New classes:
- `App\Console\Commands\MigrateNetworkUsers`
- `App\Http\Middleware\CheckSubscription`

### Updated models:
- `App\Models\User` (added `subscriptionPlan()` relation, casts for expires_at)
- `App\Models\Customer` (inherits from User)
- `App\Models\NetworkUser` (compat shim, to be removed after Phase 4)

### Tests:
- `tests/Unit/Console/Commands/MigrateNetworkUsersCommandTest.php`
- `tests/Feature/Middleware/CheckSubscriptionMiddlewareTest.php`
- `tests/Feature/Migrations/AddB2b2bFieldsToUsersTableTest.php`

---

## Support & Questions

For questions during deployment:
1. Check troubleshooting section above
2. Review generated logs in `storage/logs/`
3. Run test suites to identify issues
4. Contact development team with logs and error messages

---

**Deployment Schedule Example:**
- **Day 1-2:** Phase 1 (migrations)
- **Day 3-10:** Phase 2 (data migration + monitoring)
- **Day 11-28:** Monitor for issues, deploy code changes
- **Day 29+:** Phase 4 (cleanup), after 1-2 week monitoring period

