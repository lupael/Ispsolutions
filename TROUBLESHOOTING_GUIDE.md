# Troubleshooting Guide - Remaining Issues

This guide helps debug the remaining issues that require investigation or configuration fixes.

## Quick Diagnostics Script

Run this to check your system status:

```bash
#!/bin/bash
echo "=== ISP Solution Diagnostics ==="
echo ""

echo "1. Checking PHP version..."
php --version | head -1

echo ""
echo "2. Checking Laravel installation..."
php artisan --version

echo ""
echo "3. Checking database connection..."
php artisan db:show 2>&1 | head -5

echo ""
echo "4. Checking migrations status..."
php artisan migrate:status 2>&1 | tail -10

echo ""
echo "5. Checking routes..."
php artisan route:list --columns=Method,URI,Name | grep -E "special-permissions|export|customers" | head -10

echo ""
echo "6. Checking if Radius DB is accessible..."
mysql -h 127.0.0.1 -P 3307 -e "SELECT 1;" 2>&1 | head -3

echo ""
echo "=== End Diagnostics ==="
```

## Issue-Specific Troubleshooting

### 1. Buttons Not Working (Add/Edit Forms)

**Symptoms**: Clicking buttons does nothing, forms don't submit

**Debug Steps**:

1. Open browser developer console (F12)
2. Look for JavaScript errors
3. Check for these common issues:

```javascript
// Common errors to look for:
// - "CSRF token mismatch"
// - "Uncaught TypeError"
// - "Failed to fetch"
// - "Network request failed"
```

**Quick Fixes**:

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Regenerate app key if needed
php artisan key:generate

# Rebuild frontend assets
npm install
npm run build
```

**Check CSRF Token**:

In your blade files, ensure:
```blade
<form method="POST" action="...">
    @csrf
    <!-- form fields -->
</form>
```

### 2. Special Permissions PUT Error

**Error**: "The PUT method is not supported for route panel/admin/operators/5/special-permissions"

**Debug**:

```bash
# Check routes
php artisan route:list | grep special-permissions

# Expected output:
# GET    /panel/admin/operators/{id}/special-permissions
# PUT    /panel/admin/operators/{id}/special-permissions
```

**If routes look correct**:

1. Clear route cache:
```bash
php artisan route:clear
php artisan route:cache
```

2. Verify form has @method directive:
```blade
<form method="POST" action="{{ route('panel.admin.operators.special-permissions.update', $id) }}">
    @csrf
    @method('PUT')
    <!-- form fields -->
</form>
```

3. Check if JavaScript is hijacking the form submission

### 3. Export Routes Not Found

**Error**: "Route [panel.admin.reports.transactions.export] not defined"

**Debug**:

```bash
# List all export routes
php artisan route:list | grep export

# Check if they have panel.admin prefix
php artisan route:list | grep "panel.admin.reports"
```

**Fix**:

```bash
# Clear and recache routes
php artisan route:clear
php artisan route:cache

# If still not working, check routes/web.php structure
# Ensure export routes are INSIDE the admin group
```

### 4. RADIUS Database Connection Refused

**Error**: "SQLSTATE[HY000] [2002] Connection refused (Connection: radius, Host: 127.0.0.1, Port: 3307)"

This is NOT a code issue. Fix infrastructure:

```bash
# Check if MySQL/MariaDB is running
systemctl status mysql
# or
systemctl status mariadb

# Check if port 3307 is listening
netstat -tlnp | grep 3307

# Try connecting manually
mysql -h 127.0.0.1 -P 3307 -u radius_user -p

# Check .env configuration
grep -E "RADIUS_.*" .env
```

**If RADIUS DB is not needed**:

Comment out or disable radius-related features in config/database.php

### 5. Demo Customer in Wrong Location

**Debug**:

```bash
# Check where demo customer was created
php artisan tinker
>>> $customer = User::where('name', 'Demo Customer')->first();
>>> $customer->toArray();
>>> exit

# Check network_users table
php artisan tinker
>>> \App\Models\NetworkUser::where('username', 'LIKE', '%demo%')->get();
>>> exit
```

**Fix**:

If customer is in wrong place, migrate data or recreate:

```php
php artisan tinker
>>> // Find misplaced customer
>>> $user = User::where('name', 'Demo Customer')->first();
>>> 
>>> // Create proper network user if needed
>>> $networkUser = NetworkUser::create([
>>>     'username' => $user->email,
>>>     'password' => bcrypt('password'),
>>>     'user_id' => $user->id,
>>>     'service_type' => 'pppoe',
>>>     'status' => 'active'
>>> ]);
>>> exit
```

### 6. Tenant Isolation - Seeing Other Tenants' Data

**Debug**:

```bash
php artisan tinker
>>> $user = auth()->user();
>>> $user->tenant_id; // Should show a tenant ID

>>> // Check if TenancyService is working
>>> $service = app(\App\Services\TenancyService::class);
>>> $service->getCurrentTenant();

>>> // Check if models use BelongsToTenant trait
>>> $reflection = new \ReflectionClass(\App\Models\User::class);
>>> $traits = $reflection->getTraitNames();
>>> in_array('App\Traits\BelongsToTenant', $traits);
>>> exit
```

**Fix**:

Ensure all models that should be tenant-scoped use the trait:

```php
use App\Traits\BelongsToTenant;

class YourModel extends Model
{
    use BelongsToTenant;
    
    // ... rest of model
}
```

### 7. Repeated Menu Items

**Debug**:

```bash
# Check sidebar file
cat resources/views/panels/partials/sidebar.blade.php | grep -A 5 -B 5 "Network\|OLT"

# Look for duplicate entries
```

**Common Causes**:

1. Copy-paste errors in sidebar.blade.php
2. Multiple menu builders loading same items
3. View caching issues

**Fix**:

```bash
# Clear view cache
php artisan view:clear

# Check for duplicate route definitions
php artisan route:list --columns=Name | sort | uniq -d
```

## Performance Checks

### Check Query Performance

```bash
# Enable query log in .env
APP_DEBUG=true
DB_LOG_QUERIES=true

# Watch logs
tail -f storage/logs/laravel.log | grep "SELECT\|UPDATE\|INSERT"
```

### Check for N+1 Queries

Install and run debugbar:

```bash
composer require barryvdh/laravel-debugbar --dev
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

## Testing Specific Features

### Test Analytics Dashboard

```bash
curl -H "Cookie: laravel_session=YOUR_SESSION" \
     http://localhost/panel/admin/analytics/dashboard
```

### Test SMS Gateway

```bash
# Via artisan tinker
php artisan tinker
>>> $gateway = \App\Models\SmsGateway::first();
>>> $gateway->send('1234567890', 'Test message');
>>> exit
```

### Test Package Mapping

```bash
php artisan tinker
>>> $package = \App\Models\Package::first();
>>> $router = \App\Models\MikrotikRouter::first();
>>> $mapping = \App\Models\PackageProfileMapping::create([
>>>     'package_id' => $package->id,
>>>     'router_id' => $router->id,
>>>     'ppp_profile' => 'profile-1',
>>>     'ip_pool' => 'pool-1'
>>> ]);
>>> exit
```

## Common Laravel Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild all caches
php artisan optimize

# Check configuration
php artisan config:show

# Check environment
php artisan env

# List all routes
php artisan route:list

# Check database structure
php artisan db:table users --columns

# Run seeders
php artisan db:seed --class=DemoSeeder

# Reset and seed database (WARNING: Deletes all data!)
php artisan migrate:fresh --seed
```

## Getting Help

If issues persist:

1. Enable debug mode in .env:
   ```
   APP_DEBUG=true
   APP_LOG_LEVEL=debug
   ```

2. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Enable SQL query logging in .env:
   ```
   DB_LOG_QUERIES=true
   ```

4. Test with a clean browser session (incognito mode)

5. Verify permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. Check queue workers if using queues:
   ```bash
   php artisan queue:work --verbose
   ```

## Support Resources

- Laravel Documentation: https://laravel.com/docs
- Stack Overflow: https://stackoverflow.com/questions/tagged/laravel
- Laravel Discord: https://discord.gg/laravel
- Check FIXES_SUMMARY.md for details on applied fixes
