# Task 7: RRD Graph System for Performance Monitoring

## Overview

This implementation provides a comprehensive bandwidth usage monitoring system using RRDtool (Round-Robin Database tool) to generate performance graphs for customers. The system collects bandwidth data from RADIUS accounting records and generates visual graphs showing upload/download patterns over time.

## Features

### 1. RRD Database Management
- **Automatic database creation** for each customer
- **5-minute data collection interval** (300 seconds)
- **Multiple time resolutions**:
  - Last hour (12 data points)
  - Last 24 hours (288 data points)
  - Last 7 days (2016 data points)
  - Last 30 days (8640 data points)
- **Data sources**: Upload and Download (COUNTER type)
- **Aggregation types**: AVERAGE and MAX

### 2. Bandwidth Data Collection
- **Automated collection** via scheduled job every 5 minutes
- **Queries RADIUS accounting** (radacct table) for real-time usage
- **Handles active and inactive sessions** gracefully
- **Error handling and logging** for failed collections

### 3. Graph Generation
- **Four timeframe options**:
  - Hourly: Last 60 minutes
  - Daily: Last 24 hours
  - Weekly: Last 7 days
  - Monthly: Last 30 days
- **Visual elements**:
  - Upload bandwidth (green area)
  - Download bandwidth (blue area, stacked)
  - Current, average, and maximum values
- **Base64-encoded PNG** for easy embedding
- **5-minute caching** to reduce server load

### 4. Graceful Degradation
- **Fallback mechanism** when RRD extension is not available
- **Placeholder graphs** with installation instructions
- **Non-blocking operation** - system continues working without RRD

## Files Created

### Backend Services
- **`app/Services/RrdGraphService.php`** - Core RRD service with database and graph management

### Jobs
- **`app/Jobs/CollectBandwidthDataJob.php`** - Scheduled job for bandwidth data collection

### Controllers
- **`app/Http/Controllers/Api/V1/GraphController.php`** - API endpoints for graph retrieval

### Views
- **`resources/views/panels/shared/components/bandwidth-graph.blade.php`** - Reusable Blade component

### Configuration
- **Updated `routes/api.php`** - Added graph API endpoints
- **Updated `routes/console.php`** - Scheduled bandwidth collection job
- **Updated `Dockerfile`** - Added RRDtool and PHP RRD extension

## Installation

### Docker Environment (Automated)

The Dockerfile has been updated to automatically install RRDtool:

```bash
# Rebuild the Docker container
docker-compose build

# Restart services
docker-compose up -d
```

### Manual Installation (Non-Docker)

#### Ubuntu/Debian:
```bash
# Install RRDtool and development libraries
sudo apt-get update
sudo apt-get install -y rrdtool librrd-dev

# Install PHP RRD extension
sudo pecl install rrd
sudo echo "extension=rrd.so" > /etc/php/8.2/mods-available/rrd.ini
sudo phpenmod rrd

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### Alpine Linux:
```bash
# Install RRDtool
apk add rrdtool rrdtool-dev

# Install PHP RRD extension
pecl install rrd
echo "extension=rrd.so" > /etc/php82/conf.d/50_rrd.ini
```

#### CentOS/RHEL:
```bash
# Install RRDtool
sudo yum install -y rrdtool rrdtool-devel

# Install PHP RRD extension
sudo pecl install rrd
echo "extension=rrd.so" > /etc/php.d/50-rrd.ini

# Restart PHP-FPM
sudo systemctl restart php-fpm
```

### Verify Installation

```bash
# Check if RRD extension is loaded
php -m | grep rrd

# Check RRDtool version
rrdtool --version
```

## API Endpoints

All endpoints require authentication and tenant isolation is enforced.

### Get Hourly Graph
```
GET /api/v1/customers/{id}/graphs/hourly
```

### Get Daily Graph
```
GET /api/v1/customers/{id}/graphs/daily
```

### Get Weekly Graph
```
GET /api/v1/customers/{id}/graphs/weekly
```

### Get Monthly Graph
```
GET /api/v1/customers/{id}/graphs/monthly
```

### Response Format
```json
{
  "success": true,
  "data": {
    "customer_id": 123,
    "timeframe": "hourly",
    "graph": "iVBORw0KGgoAAAANSUhEUg...",
    "format": "base64_png"
  }
}
```

## Usage in Blade Templates

### Basic Usage
```blade
<x-bandwidth-graph :customerId="$customer->id" />
```

### With Auto-Refresh
```blade
<x-bandwidth-graph 
    :customerId="$customer->id" 
    :autoRefresh="true"
    :refreshInterval="300000" />
```

### Example: Customer Detail Page
```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Customer: {{ $customer->username }}</h1>
    
    <div class="grid grid-cols-1 gap-6">
        <!-- Customer Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Account Information</h2>
            <!-- Customer details here -->
        </div>
        
        <!-- Bandwidth Graph -->
        <div class="bg-white p-6 rounded-lg shadow">
            <x-bandwidth-graph 
                :customerId="$customer->id" 
                :autoRefresh="true" />
        </div>
    </div>
</div>
@endsection
```

## Scheduled Job

The bandwidth collection job runs automatically every 5 minutes:

```php
// routes/console.php
Schedule::job(new \App\Jobs\CollectBandwidthDataJob)->everyFiveMinutes();
```

### Manual Execution
```bash
# Run the job manually
php artisan queue:work --once

# Or dispatch it directly
php artisan tinker
>>> \App\Jobs\CollectBandwidthDataJob::dispatch();
```

### Monitor Job Execution
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "Bandwidth data collection"

# Monitor queue
php artisan queue:monitor
```

## Storage Structure

```
storage/
├── app/
│   ├── rrd/
│   │   ├── 1.rrd          # Customer ID 1's RRD database
│   │   ├── 2.rrd          # Customer ID 2's RRD database
│   │   └── ...
│   └── graphs/
│       └── (temporary PNG files, auto-deleted)
```

## Troubleshooting

### RRD Extension Not Available

If the RRD extension is not installed, the system will:
1. Log a warning message
2. Generate fallback placeholder graphs
3. Continue normal operation
4. Display installation instructions in graphs

### No Data Displayed

**Possible causes:**
1. **No active sessions** - Customer hasn't connected yet
2. **RADIUS not configured** - Check radacct table exists
3. **Collection job not running** - Verify scheduler is active
4. **Database not created** - RRD databases are created on first data collection

**Solutions:**
```bash
# Check if RRD databases exist
ls -lh storage/app/rrd/

# Manually run collection
php artisan tinker
>>> $service = app(\App\Services\RrdGraphService::class);
>>> $customer = \App\Models\NetworkUser::find(1);
>>> $service->collectCustomerBandwidth($customer);

# Check RADIUS connection
php artisan tinker
>>> \App\Models\RadAcct::count();
```

### Graph Generation Fails

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep -i "rrd\|graph"
```

**Verify RRD files:**
```bash
# Check RRD file info
rrdtool info storage/app/rrd/1.rrd

# Test graph generation manually
rrdtool graph test.png --start -3600 --end now \
    DEF:upload=storage/app/rrd/1.rrd:upload:AVERAGE \
    LINE1:upload#00FF00:"Upload"
```

### Permission Issues

```bash
# Fix permissions
sudo chown -R www-data:www-data storage/app/rrd
sudo chmod -R 755 storage/app/rrd
```

## Performance Considerations

### Caching Strategy
- Graphs are cached for 5 minutes
- Cache keys: `bandwidth_graph_{customer_id}_{timeframe}`
- Automatic cache invalidation on regeneration

### Optimization Tips
1. **Increase cache TTL** for less active customers
2. **Pre-generate graphs** for important customers
3. **Use queue workers** for collection job
4. **Monitor disk usage** of RRD files

### Disk Space Management

```bash
# Check RRD directory size
du -sh storage/app/rrd/

# Clean up old RRD files (90+ days)
php artisan tinker
>>> $service = app(\App\Services\RrdGraphService::class);
>>> $count = $service->cleanup(90);
>>> echo "Deleted {$count} old RRD files";
```

## Security

### Tenant Isolation
- All API endpoints verify `tenant_id` matches authenticated user
- Customers can only access their own graphs
- RRD files are stored outside web root

### Rate Limiting
- API endpoints use `rate_limit:api` middleware
- Default: 60 requests per minute
- Adjust in `config/rate-limit.php` if needed

## Maintenance

### Regular Tasks

```bash
# Weekly: Check RRD file integrity
cd storage/app/rrd/
for file in *.rrd; do
    rrdtool info "$file" > /dev/null || echo "Corrupt: $file"
done

# Monthly: Clean up old RRD files
php artisan tinker
>>> app(\App\Services\RrdGraphService::class)->cleanup(90);

# Monitor cache size
php artisan cache:clear
```

### Backup RRD Databases

```bash
# Backup all RRD files
tar -czf rrd-backup-$(date +%Y%m%d).tar.gz storage/app/rrd/

# Restore from backup
tar -xzf rrd-backup-20240124.tar.gz
```

## Alternative: Database-Based Graphs (Without RRDtool)

If RRDtool cannot be installed, consider using pure PHP charting libraries:

### Option 1: Store in MySQL
```php
// Store bandwidth samples in database
BandwidthUsage::create([
    'customer_id' => $customerId,
    'upload_bytes' => $upload,
    'download_bytes' => $download,
    'recorded_at' => now(),
]);

// Generate graph using PHP GD or Chart.js
```

### Option 2: Use Chart.js
```blade
<canvas id="bandwidth-chart"></canvas>
<script>
// Fetch data via API and render with Chart.js
fetch('/api/v1/customers/{{ $customerId }}/bandwidth-data')
    .then(res => res.json())
    .then(data => {
        new Chart(ctx, {
            type: 'line',
            data: data,
            // ... chart config
        });
    });
</script>
```

## Testing

### Unit Tests
```bash
# Test RRD service
php artisan test --filter=RrdGraphServiceTest

# Test graph controller
php artisan test --filter=GraphControllerTest

# Test bandwidth collection job
php artisan test --filter=CollectBandwidthDataJobTest
```

### Manual Testing
```bash
# Create test RRD database
php artisan tinker
>>> $service = app(\App\Services\RrdGraphService::class);
>>> $service->createRrdDatabase(999);

# Add test data
>>> $service->updateBandwidthData(999, 1000000, 2000000);

# Generate test graph
>>> $graph = $service->generateGraph(999, 'hourly');
>>> echo strlen($graph); // Should show base64 string length
```

## Known Limitations

1. **RRD extension dependency** - Requires PECL installation
2. **Fixed time resolutions** - Cannot be changed without recreating databases
3. **No historical data migration** - New feature, starts collecting from installation
4. **Memory usage** - Large numbers of customers may need queue workers

## Future Enhancements

1. **Real-time graphs** using WebSockets
2. **Alerting system** for bandwidth spikes
3. **Export graphs** as PDF reports
4. **Comparative analysis** between customers
5. **Custom time ranges** for graphs
6. **Data retention policies** configuration
7. **GraphQL API** for more flexible queries

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Verify RRD installation: `php -m | grep rrd`
3. Review this documentation
4. Check GitHub issues

## License

This feature is part of the ISP Solution project and follows the same license terms.
