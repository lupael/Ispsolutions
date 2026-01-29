# RouterOS v6 and v7 Dual API Support

## Overview

The ISP Solution now supports both RouterOS v6 (Binary API) and v7+ (REST API) routers, providing seamless compatibility across all MikroTik router versions.

## Supported API Types

### 1. Binary API (Recommended)
- **Protocol**: TCP socket-based binary protocol
- **Default Port**: 8728
- **RouterOS Versions**: v6.x, v7.x
- **Advantages**:
  - Works with all RouterOS versions (v6 and v7)
  - More reliable and battle-tested
  - Lower latency
  - Native RouterOS protocol
- **Library**: bencroker/routeros-api-php

### 2. REST API
- **Protocol**: HTTP/HTTPS REST API
- **Default Port**: 80/443 (www service), 8777 (custom)
- **RouterOS Versions**: v7.1+ only
- **Advantages**:
  - Standard HTTP protocol
  - Easy to debug with curl/browser
  - Firewall-friendly
- **Limitation**: Only available in RouterOS v7.1beta4+

## Configuration

### Database Field: `api_type`

The `mikrotik_routers` table includes an `api_type` field with three options:

1. **`auto`** (Default - Recommended)
   - Automatically detects the best API
   - Tries Binary API first (more compatible)
   - Falls back to REST API if binary fails
   - Ideal for mixed environments

2. **`binary`**
   - Forces Binary API usage
   - Use for RouterOS v6 routers
   - Use when Binary API is known to work

3. **`rest`**
   - Forces REST API usage
   - Use for RouterOS v7.1+ routers
   - Requires `/ip service set www disabled=no`

### Environment Configuration

```env
# MikroTik API Configuration
MIKROTIK_API_TIMEOUT=60
MIKROTIK_DEFAULT_PORT=8728  # Binary API port
MIKROTIK_MAX_RETRIES=3
MIKROTIK_RETRY_DELAY=1000
```

## Usage

### Adding a Router

When adding a router, you can specify the API type:

```php
MikrotikRouter::create([
    'name' => 'Main Router',
    'ip_address' => '192.168.1.1',
    'api_port' => 8728,           // Binary API port
    'api_type' => 'auto',          // Auto-detect (recommended)
    'username' => 'admin',
    'password' => 'password',
]);
```

### API Detection Logic

The system automatically selects the appropriate API:

1. Check `api_type` field in database
2. If `auto`:
   - Test Binary API connection
   - If successful, use Binary API
   - If fails, fall back to REST API
3. If `binary`: Use Binary API only
4. If `rest`: Use REST API only

### Code Example

```php
use App\Services\MikrotikApiService;
use App\Models\MikrotikRouter;

$router = MikrotikRouter::find(1);
$apiService = app(MikrotikApiService::class);

// Automatically uses the appropriate API
$profiles = $apiService->getMktRows($router, '/ppp/profile');
```

## Migration Guide

### For Existing Installations

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Run Migration**
   ```bash
   php artisan migrate
   ```
   
   This adds the `api_type` column with default value `auto`.

3. **Existing Routers**
   - All existing routers will use `api_type = 'auto'`
   - System will automatically detect and use Binary API for v6 routers
   - No manual configuration needed

### Updating Router Configuration

If you need to force a specific API type:

```php
$router = MikrotikRouter::find(1);
$router->api_type = 'binary';  // or 'rest' or 'auto'
$router->save();
```

## RouterOS v6 Setup

For RouterOS v6 routers, ensure the API service is enabled:

```routeros
/ip service print
/ip service set api disabled=no port=8728
```

## RouterOS v7 Setup

### Option 1: Binary API (Recommended)
```routeros
/ip service set api disabled=no port=8728
```

### Option 2: REST API
```routeros
/ip service set www disabled=no port=8777
# Or use standard port 80
/ip service set www disabled=no port=80
```

**Note**: REST API requires RouterOS v7.1beta4 or newer.

## Troubleshooting

### Router Not Connecting

1. **Check API service status**
   ```routeros
   /ip service print
   ```

2. **Test Binary API**
   ```bash
   telnet <router-ip> 8728
   ```

3. **Test REST API**
   ```bash
   curl -u admin:password http://<router-ip>:8777/rest/system/identity
   ```

4. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Force Specific API

If auto-detection isn't working:

```php
// Force binary API
$router->update(['api_type' => 'binary', 'api_port' => 8728]);

// Force REST API
$router->update(['api_type' => 'rest', 'api_port' => 8777]);
```

## API Compatibility Matrix

| RouterOS Version | Binary API | REST API |
|------------------|------------|----------|
| v6.0 - v6.48     | ✅ Yes     | ❌ No    |
| v7.0 - v7.1beta3 | ✅ Yes     | ❌ No    |
| v7.1beta4+       | ✅ Yes     | ✅ Yes   |

## Performance Considerations

### Binary API
- **Latency**: Lower (native protocol)
- **Throughput**: Higher (binary format)
- **CPU Usage**: Lower (efficient parsing)
- **Recommended for**: Production, high-frequency operations

### REST API
- **Latency**: Higher (HTTP overhead)
- **Throughput**: Lower (JSON encoding)
- **CPU Usage**: Higher (HTTP parsing)
- **Recommended for**: Development, debugging, firewall-restricted environments

## Security Notes

1. **Binary API**
   - Encrypted connections available (SSL/TLS)
   - Use port 8729 for API-SSL
   - Configure: `/ip service set api-ssl disabled=no`

2. **REST API**
   - Use HTTPS for encrypted connections
   - Configure: `/ip service set www-ssl disabled=no`
   - Update `MIKROTIK_SCHEME=https` in `.env`

3. **Firewall Rules**
   - Allow port 8728 for Binary API
   - Allow port 8777/80/443 for REST API
   - Restrict access to management IPs

## Testing

### Test Binary API Connection
```bash
php artisan tinker
>>> $router = App\Models\MikrotikRouter::find(1);
>>> $service = app(App\Services\RouterOSBinaryApiService::class);
>>> $service->testConnection($router);
```

### Test REST API Connection
```bash
curl -u admin:password http://router-ip:8777/rest/system/identity
```

### Test Auto-Detection
```bash
php artisan tinker
>>> $router = App\Models\MikrotikRouter::find(1);
>>> $router->api_type = 'auto';
>>> $router->save();
>>> $service = app(App\Services\MikrotikApiService::class);
>>> $profiles = $service->getMktRows($router, '/ppp/profile');
```

## References

- [MikroTik Binary API Documentation](https://wiki.mikrotik.com/wiki/Manual:API)
- [MikroTik REST API Documentation](https://help.mikrotik.com/docs/display/ROS/REST+API)
- [bencroker/routeros-api-php Library](https://github.com/bencroker/routeros-api-php)
