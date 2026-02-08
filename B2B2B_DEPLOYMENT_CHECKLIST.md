# B2B2B Deployment Checklist

Use this checklist during the actual deployment to track progress and ensure nothing is missed.

---

## Pre-Deployment (Before Any Changes)

### Environment Preparation
- [ ] Production database backup created and verified restorable
- [ ] Read-only replica available for schema testing (optional)
- [ ] Downtime window communicated to team
- [ ] Rollback runbook reviewed by ops team
- [ ] All tests passing: `php artisan test` ✅

### Code Review
- [ ] All migrations reviewed and approved
- [ ] Changed routes reviewed for security
- [ ] Middleware logic reviewed
- [ ] Test coverage verified
- [ ] Documentation reviewed

### Communication
- [ ] Deployment plan shared with team
- [ ] Customer support alerted (if needed)
- [ ] On-call engineer assigned
- [ ] Slack/alerts configured

---

## Phase 1: Add B2B2B Fields (Duration: 2-5 minutes)

### Step 1-1: Add subscription fields to users

```bash
php artisan migrate --path=database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php
```

- [ ] Migration completed without errors
- [ ] Checked `artisan migrate` output
- [ ] Verified in database:
  ```bash
  mysql -e "DESCRIBE users;" | grep -E "subscription_plan_id|expires_at"
  ```

### Step 1-2: Add legacy tracking column

```bash
php artisan migrate --path=database/migrations/2026_02_08_010000_add_legacy_network_user_id_to_users.php
```

- [ ] Migration completed without errors
- [ ] Verified column exists:
  ```bash
  mysql -e "DESCRIBE users;" | grep legacy_network_user_id
  ```

### Step 1-3: (Optional) Drop legacy columns

**Only run if never used:**

```bash
php artisan migrate --path=database/migrations/2026_02_08_001000_drop_legacy_columns.php
```

- [ ] Confirmed legacy columns not in use elsewhere
- [ ] Migration completed without errors
- [ ] Application still functional

### Post-Phase 1 Checks
- [ ] Application loads without errors
- [ ] No errors in `storage/logs/laravel.log`
- [ ] Database queries perform normally (check slow query log)
- [ ] All role-based routes still accessible

---

## Phase 2: Migrate Network Users Data (Duration: 5-30 minutes, depends on data volume)

### Step 2-1: Run data migration command

```bash
time php artisan migrate:network-users --chunk=1000
```

- [ ] Command started successfully
- [ ] Monitor output for progress (should show "Processed X records")
- [ ] Completed without errors

**Example output:**
```
Found 5000 network_users rows with no linked user. Processing in chunks of 1000...
[Output will show progress...]
Completed. Processed 5000 records.
```

### Step 2-2: Verify migration success

```bash
# Check for remaining orphans (should be 0)
mysql -e "SELECT COUNT(*) as orphaned FROM network_users WHERE user_id IS NULL;"

# Check customer users created
mysql -e "SELECT COUNT(*) as customer_count FROM users WHERE operator_level = 100;"

# Sample data integrity check
mysql -e "SELECT n.username, u.username, u.legacy_network_user_id FROM network_users n INNER JOIN users u ON n.user_id = u.id LIMIT 5;"
```

- [ ] Orphaned count = 0
- [ ] Customer count reasonable (should match or be slightly less than network_users count)
- [ ] Sample rows show correct data mapping

### Step 2-3: Verify no duplicate usernames

```bash
mysql -e "SELECT username, COUNT(*) as cnt FROM users GROUP BY username HAVING cnt > 1 LIMIT 10;"
```

- [ ] No duplicate usernames (should return empty result)

### Step 2-4: Monitor production

After successful migration:
- [ ] Watch application logs for 10 minutes
- [ ] Check RADIUS authentication (if applicable): `tail -f /var/log/freeradius/debug`
- [ ] Verify customer logins work
- [ ] Check database performance (CPU, I/O)
- [ ] Monitor error rate dashboard

---

## Phase 3: Deploy Code Changes (Duration: variable, depends on CI/CD)

### Step 3-1: Deploy code

Using your standard deployment process:

```bash
git push origin main
# OR
./deploy.sh production
# OR
kubectl apply -f deployment.yaml
# ... (your standard process)
```

- [ ] Code deployment completed
- [ ] All pods/processes restarted
- [ ] Health checks passing

### Step 3-2: Verify middleware is registered

```bash
php artisan tinker
# In tinker:
>>> app('router')->getRoutes()->filter(function($r) { return strpos($r->middleware(), 'subscription') !== false; })->count()
# Or look at config:
>>> config('app.middleware')
```

- [ ] `subscription` middleware visible in route groups

### Step 3-3: Test subscription enforcement

```bash
# Create test super-admin with expired subscription
php artisan tinker
>>> $plan = \App\Models\SubscriptionPlan::first();
>>> $user = \App\Models\User::create([
    'name' => 'Test Expired Admin',
    'email' => 'test_' . time() . '@example.com',
    'password' => bcrypt('test'),
    'operator_level' => 2,
    'subscription_plan_id' => $plan->id,
    'expires_at' => now()->subDay(),
]);
>>> exit;

# Test access - should get 403
curl -H "Accept: application/json" -b "XSRF-TOKEN=xyz" http://localhost/super-admin
# Should return: HTTP 403 Forbidden

# Clean up test user
php artisan tinker
>>> \App\Models\User::find(<id>)->delete();
>>> exit;
```

- [ ] Expired super-admin cannot access panel (403)
- [ ] API returns JSON 403
- [ ] Web request handles gracefully

### Step 3-4: Test developer bypass

```bash
# Create test developer
php artisan tinker
>>> $dev = \App\Models\User::create([
    'name' => 'Test Developer',
    'email' => 'dev_' . time() . '@example.com',
    'password' => bcrypt('test'),
    'operator_level' => 1,
    // No subscription required
]);
>>> exit;

# Should be able to access without subscription
curl -b "XSRF-TOKEN=xyz" http://localhost/developer
# Should not return 403

# Clean up
php artisan tinker
>>> \App\Models\User::find(<id>)->delete();
>>> exit;
```

- [ ] Developer can access without subscription

### Step 3-5: Verify view paths

Test each role's panel:
- [ ] `/developer` loads correctly
- [ ] `/super-admin` loads correctly (with valid subscription)
- [ ] `/admin` loads correctly
- [ ] No 404 errors for views

### Step 3-6: Check application logs

```bash
tail -100 storage/logs/laravel.log | grep -i error
```

- [ ] No new errors
- [ ] No deprecation warnings related to views or models

---

## Post-Deployment Monitoring (Next 1-2 weeks)

### Daily Checks

Every day, during business hours:

```bash
# Check error logs
tail -1000 storage/logs/laravel.log | grep -c ERROR

# Check for network_users access errors
tail -1000 storage/logs/laravel.log | grep -i "network_users"

# Verify subscription blocks still working
# (Test with expired account periodically)

# Check database performance
mysqldumpslow /var/log/mysql/slow.log | head -10

# Monitor RADIUS auth if applicable
grep -i "reject\|failed" /var/log/freeradius/debug
```

- [ ] Error count in acceptable range
- [ ] No unexpected network_users errors
- [ ] Database performance stable
- [ ] Auth working normally

### Weekly Summary

After 1 week, compile:
- [ ] Error rate (target: same as before migration)
- [ ] Performance metrics (query latency, CPU, disk I/O)
- [ ] Auth success rates
- [ ] Customer support tickets related to access

---

## Phase 4: Cleanup (Week 3+, if monitoring shows no issues)

### Step 4-1: Remove NetworkUser shim

```bash
rm app/Models/NetworkUser.php

# Verify no remaining imports
grep -r "NetworkUser" app/ --include="*.php" | grep -v "^#"
```

- [ ] File removed
- [ ] No remaining references

### Step 4-2: Drop network_users table (IRREVERSIBLE)

**STOP:** Before running this, confirm:
- [ ] 1-2 weeks of monitoring completed
- [ ] No errors related to network migration
- [ ] Database backup from before Phase 2 confirmed backed up offsite
- [ ] Team agrees it's safe to proceed

```bash
# Optional: Final backup before drop
mysqldump database_name network_users > /backup/network_users_final_$(date +%Y%m%d).sql

# Run the final migration
php artisan migrate --path=database/migrations/2026_02_08_020000_drop_network_users_table.php
```

- [ ] Migration completed successfully
- [ ] Logs show table was dropped

### Step 4-3: Verify cleanup

```bash
mysql -e "SHOW TABLES LIKE 'network_users%';"
# Should return: (empty result set)

mysql -e "SHOW TABLES LIKE 'network_user_sessions';"
# Should return: (empty result set)
```

- [ ] Both tables no longer exist
- [ ] Database schema audit clean

### Step 4-4: Final validation

```bash
# Run full test suite one last time
php artisan test

# Check that no code references dropped tables
grep -r "network_users" app/ --include="*.php"
# Should return: (empty or only in comments/docs)
```

- [ ] All tests passing
- [ ] No code references to dropped tables (except documentation)

---

## Rollback Procedures

Use these ONLY if serious issues occur.

### Rollback from Phase 1

```bash
php artisan migrate:rollback --path=database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php
php artisan migrate:rollback --path=database/migrations/2026_02_08_010000_add_legacy_network_user_id_to_users.php
```

- [ ] Migrations rolled back
- [ ] Application functioning normally
- [ ] No errors in logs

### Rollback from Phase 2-4

**No automated rollback available.** Use database backup:

```bash
# Stop the application
systemctl stop laravel
# Or similar for your deployment

# Restore backup
mysql < /backup/database_before_phase2.sql

# Restart application
systemctl start laravel

# Verify
curl http://localhost/
# Should load normally
```

- [ ] Database restored
- [ ] Application running
- [ ] All data restored
- [ ] Users able to log in

---

## Troubleshooting During Deployment

### Issue: Migration fails with foreign key error

**Cause:** `subscription_plan_id` references non-existent subscription plan

**Solution:**
```bash
# Create a default subscription plan
php artisan tinker
>>> \App\Models\SubscriptionPlan::create([
    'name' => 'Default Plan',
    'slug' => 'default',
    'price' => 0,
    'billing_cycle' => 'annual',
]);
>>> exit

# Re-run migration
php artisan migrate --path=database/migrations/2026_02_08_000000_add_b2b2b_fields_to_users_table.php
```

- [ ] Migration succeeds

### Issue: migrate:network-users reports orphaned records

**Cause:** Some `network_users.user_id` columns are still NULL after command

**Solution:**
```bash
# Find orphaned records
mysql -e "SELECT id, username FROM network_users WHERE user_id IS NULL LIMIT 10;"

# Manually link or delete
mysql -e "UPDATE network_users SET user_id = 1 WHERE id = <network_user_id>;"

# Re-run migrate command
php artisan migrate:network-users --chunk=100
```

- [ ] All network_users linked

### Issue: Subscription middleware blocks all super-admins

**Cause:** Routes not properly configured with subscription middleware

**Check:**
```bash
grep -A 5 "super-admin" routes/web.php | grep -i subscription
```

**Solution:**
- Verify `app/Http/Kernel.php` registers the middleware
- Verify route groups include `'subscription'` in middleware array
- Reload routes: `php artisan route:clear && php artisan route:cache`

- [ ] Routes updated
- [ ] Middleware cache cleared
- [ ] Super-admins can access (with valid subscription)

---

## Sign-Off

After all phases complete:

**Ops Lead:**
- [ ] All checks passed
- [ ] Monitoring stable
- [ ] Rollback plan understood
- **Date:** ______ **Time:** ______ **Signature:** ______________

**Dev Lead:**
- [ ] Code changes verified
- [ ] No regressions detected
- [ ] Documentation complete
- **Date:** ______ **Time:** ______ **Signature:** ______________

**Product/Support:**
- [ ] No customer-facing issues
- [ ] Feature working as expected
- [ ] Team trained on new subscription flow
- **Date:** ______ **Time:** ______ **Signature:** ______________

---

## Post-Deployment Report

After deployment completes, fill in:

**Date Range:** ________ to ________

**Issues Encountered:** 
- [ ] None
- [ ] Minor (describe): _________________________________
- [ ] Major (describe): _________________________________

**Performance Impact:**
- Before: _____ requests/sec, _____ ms avg response
- After:  _____ requests/sec, _____ ms avg response

**Data Integrity:**
- Network users migrated: _________
- Orphaned records: _________
- Duplicate usernames: _________

**Monitoring Metrics:**
- Error rate before: _____ %
- Error rate after: _____ %
- Auth success rate: _____ %

**Next Actions:**
- [ ] Document any issues experienced
- [ ] Update runbooks
- [ ] Schedule team retrospective
- [ ] Archive this checklist

