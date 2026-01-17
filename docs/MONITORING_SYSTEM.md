# Network Device Monitoring & Bandwidth Tracking - Implementation Summary

## Overview
This implementation adds comprehensive network device monitoring and bandwidth usage tracking capabilities to the ISP solution. The system monitors MikroTik routers, OLTs, and ONUs, collecting real-time metrics and tracking bandwidth usage over time.

## Database Schema

### `device_monitors` Table
Stores real-time monitoring data for all network devices:
- Polymorphic relationship (supports routers, OLTs, ONUs)
- Fields: status, cpu_usage, memory_usage, uptime, last_check_at
- Multi-tenant support via tenant_id
- Optimized indexes for efficient querying

### `bandwidth_usages` Table
Time-series bandwidth data with automatic aggregation:
- Polymorphic relationship to devices
- Fields: upload_bytes, download_bytes, total_bytes, timestamp
- Period types: raw, hourly, daily, weekly, monthly
- Optimized indexes for time-series queries

## API Endpoints

### Device Status
```
GET /api/v1/monitoring/devices
GET /api/v1/monitoring/devices/{type}/{id}/status
POST /api/v1/monitoring/devices/{type}/{id}/monitor
```

### Bandwidth Tracking
```
POST /api/v1/monitoring/devices/{type}/{id}/bandwidth
GET /api/v1/monitoring/devices/{type}/{id}/bandwidth?period=hourly&start_date=...&end_date=...
GET /api/v1/monitoring/devices/{type}/{id}/bandwidth/graph?period=daily
```

## Artisan Commands

### Data Collection
```bash
php artisan monitoring:collect              # Collect all devices
php artisan monitoring:collect --type=router  # Specific device type
php artisan monitoring:collect --type=olt --id=1  # Specific device
```

### Data Aggregation
```bash
php artisan monitoring:aggregate-hourly     # Raw → Hourly
php artisan monitoring:aggregate-daily      # Hourly → Daily
```

### Maintenance
```bash
php artisan monitoring:cleanup --days=90    # Clean old data
```

## Scheduled Tasks

The following tasks are automatically scheduled:
- **Every 5 minutes**: Collect device metrics
- **Hourly**: Aggregate raw bandwidth to hourly summaries
- **Daily at 01:00**: Aggregate hourly to daily summaries
- **Daily at 03:00**: Clean old monitoring data (90 days retention)

## Usage Examples

### Monitor a Device
```php
$monitoringService->monitorDevice('router', 1);
// Returns: ['status' => 'online', 'cpu_usage' => 45.2, 'memory_usage' => 62.3, 'uptime' => 864000]
```

### Record Bandwidth Usage
```php
$monitoringService->recordBandwidthUsage('router', 1, 1048576, 2097152);
// Records 1MB upload, 2MB download
```

### Get Bandwidth Graph Data
```php
$graphData = $monitoringService->getBandwidthGraph('router', 1, 'daily');
// Returns chart-ready data with labels and datasets
```

### Get All Device Statuses
```php
$statuses = $monitoringService->getAllDeviceStatuses();
// Returns grouped statuses with summary statistics
```

## Model Usage

### DeviceMonitor Model
```php
// Query scopes
DeviceMonitor::online()->get();
DeviceMonitor::offline()->get();
DeviceMonitor::degraded()->get();
DeviceMonitor::deviceType('App\Models\MikrotikRouter')->get();

// Helper methods
$monitor->isOnline();
$monitor->isOffline();
$monitor->getUptimeHuman(); // "1d 2h 3m"
```

### BandwidthUsage Model
```php
// Query scopes
BandwidthUsage::device('App\Models\MikrotikRouter', 1)->get();
BandwidthUsage::periodType('hourly')->get();
BandwidthUsage::hourly()->get();
BandwidthUsage::daily()->get();
BandwidthUsage::dateRange($start, $end)->get();

// Helper methods
$usage->getUploadHuman();   // "1.50 MB"
$usage->getDownloadHuman(); // "3.00 MB"
$usage->getTotalHuman();    // "4.50 MB"
```

## Data Aggregation Flow

1. **Raw Data**: Collected every 5 minutes → stored as 'raw' period_type
2. **Hourly Aggregation**: After 2 hours → sum raw data, store as 'hourly', delete raw
3. **Daily Aggregation**: After 2 days → sum hourly data, store as 'daily', delete hourly
4. **Weekly Aggregation**: After 2 weeks → sum daily data, store as 'weekly', delete daily
5. **Monthly Aggregation**: After 2 months → sum weekly data, store as 'monthly', delete weekly
6. **Cleanup**: Monthly data kept for 2 years, others follow 90-day retention

## Features

✅ Polymorphic relationships for multiple device types
✅ Real-time monitoring with CPU, memory, uptime tracking
✅ Bandwidth usage recording and tracking
✅ Automatic time-series data aggregation
✅ Chart-ready graph data generation
✅ Multi-tenancy support
✅ Optimized database queries with proper indexes
✅ RESTful API endpoints with validation
✅ Comprehensive unit tests
✅ Factory definitions for testing
✅ Scheduled task automation
✅ Human-readable formatting for metrics

## Testing

Run the monitoring service tests:
```bash
php artisan test --filter=MonitoringServiceTest
php artisan test --filter=DeviceMonitorTest
php artisan test --filter=BandwidthUsageTest
```

## Integration

The monitoring service is registered in `NetworkServiceProvider` and can be injected into controllers, commands, or other services:

```php
use App\Contracts\MonitoringServiceInterface;

public function __construct(
    private MonitoringServiceInterface $monitoringService
) {}
```

## Performance Considerations

- Indexes are added to frequently queried columns (monitorable_type, monitorable_id, timestamp, period_type)
- Automatic aggregation reduces database size over time
- Old data is automatically cleaned up to maintain performance
- Query scopes enable efficient filtering without loading unnecessary data

## Future Enhancements

Possible improvements for future iterations:
- Real-time alerts for device offline/degraded status
- Threshold-based alerting (CPU > 90%, Memory > 95%)
- Historical trend analysis and predictions
- Bandwidth quota tracking and enforcement
- Integration with notification system
- Dashboard widgets for monitoring overview
- Export functionality for reports
