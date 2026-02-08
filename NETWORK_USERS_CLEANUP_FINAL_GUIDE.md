# NetworkUser Shim Removal & Final Deprecation Guide

**STATUS:** Post-monitoring cleanup (do NOT execute until 1-2 weeks of production data migration monitoring)

## When to Execute

Only proceed with this cleanup guide AFTER:
✅ Data migration has run successfully (`php artisan migrate:network-users`)  
✅ All network_users are linked to users (0 orphaned rows)  
✅ Production has been monitored for 1-2 weeks with NO errors in:
  - Customer logins
  - RADIUS authentication
  - Billing/invoice generation
  - Network service provisioning
  - Customer reports

## Final Cleanup Steps

### Phase 1: Code Cleanup (Reversible)

#### Step 1.1: Replace NetworkUser Usage in Code

Search for all remaining `NetworkUser` references:

```bash
grep -r "NetworkUser" app/ --include="*.php" | grep -v "/app/Models/NetworkUser.php"
```

Common locations to update:
- `app/Jobs/` - Background jobs
- `app/Services/` - Service classes
- `app/Events/` - Event handlers
- `database/seeders/` - Seeders

**Replacement Pattern:**
```php
// Before
use App\Models\NetworkUser;
$networkUser = NetworkUser::find($id);

// After
use App\Models\Customer;
$customer = Customer::find($id);
// OR
use App\Models\User;
$user = User::where('operator_level', 100)->find($id);
```

#### Step 1.2: Remove NetworkUser Model

Once all references are updated:

```bash
rm app/Models/NetworkUser.php
```

Update any `use` statements that import this model.

#### Step 1.3: Update Tests

Find and update test files:

```bash
grep -r "NetworkUser" tests/ --include="*.php"
```

Replace with appropriate User/Customer references in tests.

#### Step 1.4: Update Documentation

Update any docs that mention NetworkUser:

```bash
grep -r "NetworkUser\|network_users" docs/ *.md --include="*.md"
```

Example files to check:
- `NETWORK_USER_MIGRATION.md`
- `DEPRECATING_NETWORK_USERS_MIGRATION_GUIDE.md`
- `API.md`
- README sections on models

### Phase 2: Database Changes (POINT OF NO RETURN)

#### Step 2.1: Create Drop Migration

Create a new migration to drop legacy structures:

```bash
php artisan make:migration drop_network_users_legacy_tables
```

Fill in migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key constraints first
        Schema::table('onus', function (Blueprint $table) {
            if (Schema::hasColumn('onus', 'network_user_id')) {
                // Drop FK if it exists: this depends on your actual schema
                // $table->dropForeign(['network_user_id']);
            }
        });

        // Drop related tables
        Schema::dropIfExists('network_user_sessions');
        
        // Drop the main table
        Schema::dropIfExists('network_users');
    }

    public function down(): void
    {
        // Intentionally not implemented
        // Rollback by restoring database backup
        throw new \Exception('Cannot rollback network_users table drop. Restore from backup.');
    }
};
```

#### Step 2.2: Remove legacy_network_user_id Column (Optional)

If you want to clean up the tracking column we added:

```php
// In same migration or separate one
Schema::table('users', function (Blueprint $table) {
    if (Schema::hasColumn('users', 'legacy_network_user_id')) {
        $table->dropIndex(['legacy_network_user_id']);
        $table->dropColumn('legacy_network_user_id');
    }
});
```

**Note:** Keeping this column is less harmful than keeping the entire `network_users` table. Consider keeping it for audit purposes.

#### Step 2.3: Run Database Migrations

```bash
# Final backup before destructive operation
mysqldump -u user -p database > final_backup_before_network_users_drop_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
php artisan migrate
```

**BACKUP BACKUP BACKUP!** This is the point of no return.

#### Step 2.4: Run Cleanup Migration (Already Exists)

If not already run, execute:

```bash
php artisan migrate
```

This includes `2026_02_08_001000_drop_legacy_columns.php` which removes:
- `legacy_status` column from users
- `old_role_id` column from users
- Legacy role slugs (`reseller`, `sub-reseller`, `legacy_role`)

### Phase 3: Verification (After Each Step)

#### After Code Cleanup

```bash
# Ensure no NetworkUser references remain
grep -r "NetworkUser" app/ tests/ --include="*.php" | wc -l
# Should output: 0

# Verify application boots
php artisan tinker
> quit()
```

#### After Database Changes

```bash
# Verify tables are dropped
mysql -u user -p database
> SHOW TABLES LIKE 'network_users'; -- Should be empty
> SHOW TABLES LIKE 'network_user_sessions'; -- Should be empty
> SELECT COUNT(*) FROM users WHERE legacy_network_user_id IS NOT NULL; -- May continue migrating data

# Verify no dangling foreign keys
> SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'network_users';
# Should return: 0 rows
```

#### After Full Deployment

Run integration tests:

```bash
php artisan test tests/Integration/
php artisan test --group=migration
```

Monitor application logs for any errors:

```bash
tail -f storage/logs/laravel.log | grep -i "network"
```

Record should show NO matches (the legacy term is gone).

## Rollback Plan (Critical!)

If issues occur at ANY point in Phase 2:

**Immediate:**
```bash
# Stop the application
# Restore database from backup
mysql -u user -p database < final_backup_before_network_users_drop_YYYYMMDD_HHMMSS.sql

# Revert code changes if needed
git revert <commit-hash>  # Or manually fix files
```

**Why Phase 2 is risky:**
- Once `network_users` table is dropped, any code still referencing it will error
- Foreign key constraints might break if children still reference network_users
- Backup restoration is your only recovery option

## Post-Cleanup Monitoring

Monitor these for 2 weeks:

**Daily Checks:**
- [ ] Check logs for "network_user" or "NetworkUser" errors
- [ ] Verify customer login success rate (should be 100%)
- [ ] Confirm billing jobs run without error
- [ ] Validate any customer-facing reports

**Weekly Checks:**
- [ ] Run `php artisan test` suite
- [ ] Verify no slow queries on users table
- [ ] Check for any deprecation notices in logs

**Metrics to Monitor:**
```sql
-- Customer activity (should be normal)
SELECT DATE(created_at), COUNT(*) FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 WEEK) GROUP BY DATE(created_at);

-- No orphaned data
SELECT COUNT(*) FROM users WHERE operator_level = 100 AND (username IS NULL OR radius_password IS NULL);

-- RADIUS still working
SELECT COUNT(*) FROM radius_sessions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

## Files to Clean Up/Update

**Delete:**
- [ ] `app/Models/NetworkUser.php`

**Update (remove NetworkUser references):**
- [ ] `app/Services/WidgetCacheService.php` (already updated, verify)
- [ ] `app/Jobs/CollectNetworkStatsJob.php` (if exists)
- [ ] Any `routes/` files referencing NetworkUser
- [ ] Test files in `tests/`
- [ ] Documentation files

**Create:**
- [ ] `database/migrations/202X_XX_XX_XXXXXX_drop_network_users_legacy_tables.php`

## Success Criteria

✅ Cleanup is complete when:

1. **Code:** No references to NetworkUser class exist (grep returns 0)
2. **Database:** `network_users` table does not exist
3. **Tests:** All tests pass including integration tests
4. **Logs:** No "NetworkUser" or "network_users" errors for 2 weeks
5. **Functionality:** All features work normally:
   - Customer sign-ups
   - Billing generation
   - RADIUS authentication
   - Customer dashboards
   - Admin reports

## Rollback Decision Tree

```
Issue Found?
├─ Phase 1 (Code changes)
│  ├─ Yes → git revert, redeploy code only
│  └─ No → proceed to Phase 2
├─ Phase 2 (Migrations)
│  ├─ Yes → restore DB backup, redeploy to previous release
│  └─ No → cleanup complete
└─ Monitoring Period
   ├─ Errors found → investigate root cause, apply patch
   └─ No errors for 2 weeks → deprecation complete ✓
```

## Final Confirmation Checklist

Before marking deprecation as complete:

- [ ] All code references to NetworkUser removed
- [ ] `network_users` table dropped
- [ ] `network_user_sessions` table dropped (if separate)
- [ ] `legacy_network_user_id` column removed or marked as deprecated
- [ ] Tests pass (unit + integration + feature)
- [ ] No errors in logs for 1-2 weeks post-deployment
- [ ] Customer-facing functionality verified (logins, billing, account management)
- [ ] Documentation updated to remove NetworkUser references
- [ ] CHANGELOG.md updated with removal notice
- [ ] Version bump applied (e.g., v2.1.0)

## Historical Note

**What we're removing:**
- `network_users` table: Originally separate from `users`, stored customer network credentials
- `NetworkUser` model: Legacy Eloquent model for network_users table
- Legacy column references: `legacy_status`, `old_role_id`
- Legacy roles: `reseller`, `sub-reseller`

**Why:**
- Unified `users` table now handles all user types (developers, super-admins, admins, operators, customers)
- B2B2B multi-tenancy hierarchy requires single user model
- Simplifies permissions and authentication
- Reduces database complexity and query joins

**What remains:**
- `users` table with all network credential fields
- `Customer` model for customer-specific features
- `User` model with `operator_level` levels: 10 (developer), 30 (super-admin), 50 (admin), 75 (operator), 100 (customer)
- Subscription enforcement via `CheckSubscription` middleware
- Multi-tenancy via `BelongsToTenant` trait and `ResolveTenant` middleware
