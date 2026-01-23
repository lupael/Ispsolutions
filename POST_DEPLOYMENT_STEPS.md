# Quick Start - Post-Deployment Steps

After deploying the fixes from this PR, follow these steps to ensure everything is working correctly.

## Step 1: Clear All Caches (REQUIRED)

```bash
cd /path/to/ispsolution

# Clear all Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimizations
php artisan optimize
```

## Step 2: Verify Database Connection

```bash
# Check database connection
php artisan db:show

# Check migration status
php artisan migrate:status
```

## Step 3: Test Fixed Routes

Open your browser and test these previously broken endpoints:

### Analytics Dashboard
- URL: https://dev.ispbills.com/panel/admin/analytics/dashboard
- **Expected**: Dashboard loads with revenue charts
- **Fixed Issue**: payment_date column error

### Revenue Report
- URL: https://dev.ispbills.com/panel/admin/analytics/revenue-report
- **Expected**: Revenue report displays
- **Fixed Issue**: payment_date column error

### Customer Report
- URL: https://dev.ispbills.com/panel/admin/analytics/customer-report
- **Expected**: Customer statistics display
- **Fixed Issue**: is_active column error

### Service Report
- URL: https://dev.ispbills.com/panel/admin/analytics/service-report
- **Expected**: Service package distribution displays
- **Fixed Issue**: service_packages table error (code was already correct)

### Network Devices
- URL: https://dev.ispbills.com/panel/admin/network/devices
- **Expected**: List of routers, OLTs, and switches
- **Fixed Issue**: host column error

### Network Routers
- URL: https://dev.ispbills.com/panel/admin/network/routers
- **Expected**: List of MikroTik routers
- **Fixed Issue**: networkUsers relationship error

### OLT Templates
- URL: https://dev.ispbills.com/panel/admin/olt/templates
- **Expected**: Configuration templates page loads
- **Fixed Issue**: variable_name constant error

### Operator Special Permissions
- URL: https://dev.ispbills.com/panel/admin/operators/[ID]/special-permissions
- **Expected**: Form loads and can be submitted
- **Fixed Issue**: Form structure verified (may need route cache clear)

## Step 4: Test Report Exports

Try exporting reports from these pages:

1. Transactions: https://dev.ispbills.com/panel/admin/accounting/transactions
   - Click "Export Excel" button
   - Click "Export PDF" button

2. VAT Collections: https://dev.ispbills.com/panel/admin/accounting/vat-collections
   - Click export button

3. Expenses: https://dev.ispbills.com/panel/admin/accounting/expense-report
   - Click export button

4. Income/Expense: https://dev.ispbills.com/panel/admin/accounting/income-expense-report
   - Click export button

5. Receivable: https://dev.ispbills.com/panel/admin/accounting/receivable
   - Click export button

6. Payable: https://dev.ispbills.com/panel/admin/accounting/payable
   - Click export button

**If exports still don't work**: Run `php artisan route:cache` again

## Step 5: Test Customer Features

### PPPoE Import
- URL: https://dev.ispbills.com/panel/admin/customers/pppoe-import
- **Expected**: Import form displays
- **Verified**: View exists and route is defined

### Bulk Update
- URL: https://dev.ispbills.com/panel/admin/customers/bulk-update
- **Expected**: Bulk update form displays
- **Verified**: View exists and route is defined

### Import Requests
- URL: https://dev.ispbills.com/panel/admin/customers/import-requests
- **Expected**: List of import requests displays
- **Verified**: View exists and route is defined

## Step 6: Verify Feature Access

### SMS Gateway Management
- URL: https://dev.ispbills.com/panel/admin/sms/gateways
- **Expected**: SMS gateway configuration page
- **Verified**: Controller, routes, and views exist

### Package to PPP Profile Mapping
- Navigate to Packages section
- Click on a package
- Look for "Mappings" tab or link
- **Expected**: Package mapping interface
- **Verified**: Controller, routes, and views exist

## Step 7: Check for Remaining Issues

### If buttons still don't work:

1. **Check browser console** (F12 → Console tab)
   - Look for JavaScript errors
   - Look for "CSRF token mismatch" errors
   - Look for "404 Not Found" errors

2. **Rebuild frontend assets**:
   ```bash
   npm install
   npm run build
   # or for development
   npm run dev
   ```

3. **Check file permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### If RADIUS errors persist:

The RADIUS database connection errors are **infrastructure issues**, not code issues:

```bash
# Check if RADIUS DB service is running
systemctl status mysql
systemctl status mariadb

# Check if port 3307 is accessible
netstat -tlnp | grep 3307

# Try manual connection
mysql -h 127.0.0.1 -P 3307 -u radius_user -p
```

**Temporary workaround** if RADIUS is not needed:
- Avoid accessing /panel/admin/logs/hotspot
- Avoid accessing /panel/admin/logs/ppp

### If tenant isolation issues occur:

Check your .env file has correct tenant configuration:
```env
TENANCY_ENABLED=true
```

## Step 8: Run Database Migrations (RECOMMENDED)

These migrations add extra columns that are used by various services in the application:

```bash
# RECOMMENDED: Run migrations to add missing columns used by other services
php artisan migrate

# This will run:
# - add payment_date to payments table (required by CableTvBillingService, FinancialReportService, GeneralLedgerService, BulkOperationsService)
# - add is_active to network_users table (optional, this PR uses status='active' instead)
# - add host to mikrotik_routers table (optional, this PR uses ip_address instead)
# - add collected_by to payments table (required for YearlyReportController operator income reports)
```

**Important**: While this PR fixes the main analytics queries to work without these migrations, several other services still rely on the `payment_date` column. Running migrations is recommended unless you plan to refactor all services to use `paid_at` consistently.

## Verification Checklist

Use this checklist to verify all fixes:

- [ ] Analytics dashboard loads without errors
- [ ] Revenue report displays data
- [ ] Customer report shows statistics
- [ ] Service report shows package distribution
- [ ] Network devices page lists all devices
- [ ] Network routers page lists routers
- [ ] OLT templates page loads without constant error
- [ ] Operator special permissions form can be viewed
- [ ] Transaction report export works (Excel)
- [ ] Transaction report export works (PDF)
- [ ] All customer feature URLs are accessible
- [ ] SMS gateway management is accessible
- [ ] Package mappings are accessible
- [ ] No JavaScript errors in browser console
- [ ] All caches have been cleared

## Support

If you encounter issues after following these steps:

1. **Check the logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Review documentation**:
   - Read `FIXES_SUMMARY.md` for detailed fix explanations
   - Read `TROUBLESHOOTING_GUIDE.md` for debugging steps

3. **Common solutions**:
   - Clear browser cache (Ctrl+Shift+Delete)
   - Try incognito/private browsing mode
   - Check browser console for errors
   - Verify .env configuration is correct
   - Ensure database migrations are up to date

## What Was Fixed

This deployment fixes these specific SQL query errors in analytics and reporting:

1. ✅ Analytics payment queries (AdvancedAnalyticsService, YearlyReportController) - Changed from `payment_date` to `paid_at`
2. ✅ Analytics network user queries (AdvancedAnalyticsService, ZoneController) - Changed from `is_active` to `status = 'active'`
3. ✅ Device union query (AdminController) - Changed from `host` to `ip_address`
4. ✅ Template view Blade syntax error
5. ✅ MikrotikRouter relationship alias added with clarifying comments

**Scope Note**: This PR specifically fixes analytics and core reporting queries. Other services (CableTvBillingService, FinancialReportService, GeneralLedgerService, BulkOperationsService) still use `payment_date` and will require running migrations or separate refactoring.

## What Needs Additional Configuration

These items require configuration, migrations, or infrastructure fixes:

1. ⚠️ RADIUS database service (external service, not running)
2. ⚠️ Some buttons not working (may be JavaScript/CSRF issues)
3. ⚠️ Tenant isolation (verify TenancyService configuration)
4. ⚠️ Demo data placement (verify seeder configuration)
5. ⚠️ Other services using `payment_date` - Run `php artisan migrate` to add this column, or refactor those services to use `paid_at`
6. ⚠️ Operator income reports - Requires migration to add `collected_by` column to payments table

Refer to `TROUBLESHOOTING_GUIDE.md` for detailed steps to address these items.

## Success Indicators

After completing all steps, you should see:

- ✅ No "Column not found" errors in Laravel logs
- ✅ Analytics pages load and display data
- ✅ Network pages show devices and routers
- ✅ Export buttons generate files
- ✅ All customer management URLs are accessible
- ✅ No PHP errors in browser network tab
- ✅ No JavaScript errors in browser console

If you see all success indicators, the deployment is complete! 🎉
