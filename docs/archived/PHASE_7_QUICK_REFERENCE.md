# Phase 7 Quick Reference Guide

## Cache Usage

```php
use App\Services\CacheService;

$cacheService = app(CacheService::class);

// Cache tenant data
$packages = $cacheService->rememberTenantData('packages', 900, function() {
    return Package::active()->get();
});

// Cache statistics
$stats = $cacheService->rememberStats('dashboard', function() {
    return [
        'total_users' => NetworkUser::count(),
        'active_users' => NetworkUser::active()->count(),
    ];
});

// Invalidate cache
$cacheService->forgetTenantCache();
$cacheService->invalidateModelCache('Package', $tenantId);
```

## Queue Jobs

```php
use App\Jobs\{SendInvoiceEmailJob, ProcessPaymentJob, SyncMikrotikSessionJob};

// Dispatch jobs
SendInvoiceEmailJob::dispatch($invoice, 'new');
ProcessPaymentJob::dispatch($payment);
SyncMikrotikSessionJob::dispatch($routerId, $username);
GenerateBillingReportJob::dispatch($tenantId, 'monthly', ['year' => 2024]);
SendBulkSmsJob::dispatch($phoneNumbers, $message, $tenantId);

// Queue commands
php artisan queue:work
php artisan queue:work --queue=high,default
php artisan queue:monitor
```

## Query Optimization

```php
// Use scopes
$users = NetworkUser::active()
    ->byTenant($tenantId)
    ->byServiceType('pppoe')
    ->get();

// Eager loading with select
$invoices = Invoice::select(['id', 'invoice_number', 'total_amount', 'status'])
    ->withRelations()
    ->paginate(20);

// Optimized payment listing
$payments = Payment::byTenant($tenantId)
    ->completed()
    ->withRelations()
    ->recentPayments(30)
    ->paginate(20);
```

## Performance Monitoring

```php
use App\Services\PerformanceMonitoringService;

// Start monitoring
PerformanceMonitoringService::start();

// Your code here...

// Get metrics
$metrics = PerformanceMonitoringService::stop();
// Returns: ['duration_ms' => 125, 'query_count' => 5, 'slow_queries' => 0]

// Log performance
PerformanceMonitoringService::logMetrics('/api/users', $metrics);
```

## Artisan Commands

```bash
# Cache management
php artisan cache:warm --all           # Warm all tenants
php artisan cache:warm --tenant=1     # Warm specific tenant
php artisan cache:clear               # Clear all cache

# Queue management
php artisan queue:work                # Start worker
php artisan queue:work --daemon       # Run as daemon
php artisan queue:listen              # Listen for jobs
php artisan queue:failed              # List failed jobs
php artisan queue:retry all           # Retry failed jobs

# Database
php artisan migrate                   # Run migrations
php artisan db:show                   # Show database info
php artisan migrate:status            # Migration status
```

## Load Testing

```bash
# Basic load test
cd tests/Performance
./load-test.sh

# With configuration
BASE_URL=http://localhost \
CONCURRENCY=20 \
REQUESTS=1000 \
AUTH_TOKEN="your-token" \
./load-test.sh
```

## Database Indexes

All major tables now have optimized indexes:
- Single column: tenant_id, status, email, username
- Composite: (tenant_id, status), (tenant_id, user_id), etc.

Check `docs/DATABASE_OPTIMIZATION.md` for full details.

## Performance Targets

- Response time: < 500ms
- API calls: < 200ms
- Query time: < 100ms
- Queries/request: < 20

## Troubleshooting

### High query count
```php
// Enable query logging
PerformanceMonitoringService::enableQueryLogging();
// ... your code ...
$queries = PerformanceMonitoringService::getQueryLog();
dd($queries);
```

### Slow queries
Check logs for queries > 100ms, they're automatically logged.

### Cache issues
```php
// Test cache connection
Cache::put('test', 'value', 60);
$value = Cache::get('test'); // Should return 'value'
```

### Queue not processing
```bash
# Check queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Restart workers
supervisorctl restart ispsolution-queue-worker:*
```
