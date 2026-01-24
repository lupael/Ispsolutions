# Quick Deployment Guide - New Features

**Target**: Production deployment of all new features from CONTROLLER_FEATURE_ANALYSIS implementation  
**Estimated Time**: 10-15 minutes  
**Downtime Required**: Minimal (only during migration)

## Pre-Deployment Checklist

- [ ] Backup current database
- [ ] Backup current codebase
- [ ] Verify .env configuration
- [ ] Ensure all services are running
- [ ] Notify users of maintenance window

## Deployment Steps

### 1. Pull Latest Code

```bash
cd /path/to/ispsolution
git pull origin main  # or your branch name
```

### 2. Install Dependencies (if needed)

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 3. Run Migrations

```bash
# Backup database first!
php artisan backup:database  # if backup command exists

# Run migrations
php artisan migrate --force

# Verify migrations
php artisan migrate:status
```

### 4. Seed Initial Data

```bash
# Seed VAT profiles (4 default profiles)
php artisan db:seed --class=VatProfileSeeder --force

# Seed SMS events (9 event templates)
php artisan db:seed --class=SmsEventSeeder --force

# Seed expense categories (6 categories with subcategories)
php artisan db:seed --class=ExpenseCategorySeeder --force
```

### 5. Clear and Rebuild Cache

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize for production
php artisan optimize
```

### 6. Verify Deployment

```bash
# Check routes are loaded
php artisan route:list | grep "panel/customers"

# Verify database tables
php artisan tinker
>>> Schema::hasTable('customer_mac_addresses')
>>> Schema::hasTable('customer_volume_limits')
>>> Schema::hasTable('vat_profiles')
>>> exit

# Check seeders worked
php artisan tinker
>>> \App\Models\VatProfile::count()  # Should be 4
>>> \App\Models\SmsEvent::count()    # Should be 9
>>> \App\Models\ExpenseCategory::count()  # Should be 6
>>> exit
```

### 7. Set Permissions (if using file-based permissions)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Restart Services

```bash
# Restart PHP-FPM (if using)
sudo systemctl restart php8.2-fpm

# Restart queue workers (if using)
php artisan queue:restart

# Restart web server
sudo systemctl restart nginx  # or apache2
```

## Post-Deployment Verification

### Test New Features (via API or browser)

1. **Test MAC Binding**
   ```bash
   curl -X GET http://your-domain.com/panel/customers/1/mac-binding \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

2. **Test VAT Profiles**
   ```bash
   curl -X GET http://your-domain.com/panel/vat \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

3. **Test SMS Events**
   ```bash
   curl -X GET http://your-domain.com/panel/sms/events \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

4. **Test Expense Categories**
   ```bash
   curl -X GET http://your-domain.com/panel/expenses/categories \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

### Check Logs for Errors

```bash
tail -f storage/logs/laravel.log
```

## Rollback Plan (if needed)

If something goes wrong:

```bash
# 1. Restore database backup
mysql -u username -p database_name < backup.sql

# 2. Revert code
git reset --hard PREVIOUS_COMMIT_HASH

# 3. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## New Features Available

After deployment, these features are immediately available:

1. **Customer MAC Binding** - `/panel/customers/{id}/mac-binding`
2. **Volume Limits** - `/panel/customers/{id}/volume-limit`
3. **Time Limits** - `/panel/customers/{id}/time-limit`
4. **Advance Payments** - `/panel/customers/{id}/advance-payments`
5. **Custom Prices** - `/panel/customers/{id}/custom-prices`
6. **VAT Management** - `/panel/vat`
7. **SMS Broadcast** - `/panel/sms/broadcast`
8. **SMS Events** - `/panel/sms/events`
9. **SMS History** - `/panel/sms/history`
10. **Expense Management** - `/panel/expenses`

## Training & Documentation

Share with your team:

- **NEW_FEATURES_GUIDE.md** - Comprehensive usage guide
- **IMPLEMENTATION_SUMMARY.md** - Overview and business impact
- **CONTROLLER_FEATURE_ANALYSIS.md** - Feature details

## Monitoring

Keep an eye on:

- Database performance (9 new tables)
- API response times
- Error logs for any issues
- User feedback

## Support

If you encounter issues:

1. Check `storage/logs/laravel.log` for errors
2. Verify migrations completed: `php artisan migrate:status`
3. Ensure seeders ran successfully
4. Check file permissions
5. Verify .env configuration

## Success Criteria

✅ All migrations completed successfully  
✅ Seeders populated initial data  
✅ Routes are accessible  
✅ No errors in logs  
✅ API endpoints respond correctly  
✅ Database tables created with proper indexes

---

**Deployment Time**: 10-15 minutes  
**Risk Level**: Low (all changes are additive, no modifications to existing features)  
**Rollback Time**: < 5 minutes if needed  
**Production Ready**: Yes ✅
