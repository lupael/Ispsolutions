# Task 7: RRD Graph System - Quick Reference

## 🚀 Quick Start

### Installation (Docker)
```bash
docker-compose build
docker-compose up -d
docker-compose exec app php -m | grep rrd  # Verify
```

### Usage in Blade
```blade
<x-bandwidth-graph :customerId="$customer->id" :autoRefresh="true" />
```

### API Call
```bash
curl -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/customers/123/graphs/daily
```

## 📊 API Endpoints

| Endpoint | Timeframe | Description |
|----------|-----------|-------------|
| `/api/v1/customers/{id}/graphs/hourly` | 1 hour | Last 60 minutes |
| `/api/v1/customers/{id}/graphs/daily` | 24 hours | Last day |
| `/api/v1/customers/{id}/graphs/weekly` | 7 days | Last week |
| `/api/v1/customers/{id}/graphs/monthly` | 30 days | Last month |

**All endpoints require authentication and enforce tenant isolation.**

## 🔧 Troubleshooting

### RRD Extension Not Found
```bash
# Check if installed
php -m | grep rrd

# Install (Ubuntu/Debian)
sudo apt-get install rrdtool librrd-dev
sudo pecl install rrd
echo "extension=rrd.so" | sudo tee /etc/php/8.2/mods-available/rrd.ini
sudo phpenmod rrd
sudo systemctl restart php8.2-fpm
```

### No Data in Graphs
```bash
# Check scheduler is running
php artisan schedule:work

# Manual collection test
php artisan tinker
>>> $service = app(\App\Services\RrdGraphService::class);
>>> $customer = \App\Models\NetworkUser::find(1);
>>> $service->collectCustomerBandwidth($customer);

# Check RADIUS data
>>> \App\Models\RadAcct::count();
```

### Permission Errors
```bash
sudo chown -R www-data:www-data storage/app/rrd
sudo chmod -R 755 storage/app/rrd
```

## 📁 File Locations

```
app/Services/RrdGraphService.php           - Core service
app/Jobs/CollectBandwidthDataJob.php       - Scheduled job
app/Http/Controllers/Api/V1/GraphController.php - API
resources/views/panels/shared/components/bandwidth-graph.blade.php - UI

storage/app/rrd/*.rrd                       - RRD databases
storage/logs/laravel.log                    - Logs

Dockerfile                                  - RRDtool setup
routes/api.php                              - API routes
routes/console.php                          - Scheduler
```

## 🎯 Key Features

- ✅ **Automated Collection**: Every 5 minutes via scheduler
- ✅ **Multiple Timeframes**: Hourly, daily, weekly, monthly
- ✅ **Caching**: 5-minute TTL for performance
- ✅ **Graceful Degradation**: Works without RRD extension
- ✅ **Security**: Auth, rate limiting, tenant isolation
- ✅ **Auto-refresh**: Optional real-time updates

## 🔍 Monitoring

```bash
# Check collection logs
tail -f storage/logs/laravel.log | grep "Bandwidth data collection"

# Check RRD files
ls -lh storage/app/rrd/ | wc -l

# Test graph generation
php artisan tinker
>>> app(\App\Services\RrdGraphService::class)->getCustomerGraph(1, 'hourly');
```

## ⚙️ Configuration

### Cache TTL (in RrdGraphService.php)
```php
private const CACHE_TTL = 300; // 5 minutes
```

### Graph Size (in RrdGraphService.php)
```php
private const GRAPH_WIDTH = 800;
private const GRAPH_HEIGHT = 300;
```

### Collection Interval (in routes/console.php)
```php
Schedule::job(new \App\Jobs\CollectBandwidthDataJob)->everyFiveMinutes();
```

### Auto-refresh Interval (in Blade)
```blade
<x-bandwidth-graph 
    :customerId="$id" 
    :refreshInterval="300000" {{-- 5 minutes --}} />
```

## 📚 Documentation

- **Full Guide**: `TASK_7_RRD_GRAPH_IMPLEMENTATION.md`
- **Summary**: `TASK_7_IMPLEMENTATION_SUMMARY.md`
- **This File**: `TASK_7_QUICK_REFERENCE.md`

## 🆘 Support

1. Check logs: `storage/logs/laravel.log`
2. Verify RRD: `php -m | grep rrd`
3. Test manually: `php artisan tinker`
4. Review documentation
5. Check GitHub issues

---
**Version**: 1.0.0 | **Status**: ✅ Production Ready
