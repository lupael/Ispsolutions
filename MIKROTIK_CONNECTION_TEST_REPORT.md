# Mikrotik Connection Testing Report

## Date: 2026-01-29

## Update: Dual API Support Implemented ✅

**NEW**: System now supports both RouterOS v6 (Binary API) and v7 (REST API) with automatic detection.

## Objective
Test connectivity to Mikrotik router at [REDACTED_IP]:8777 with credentials [REDACTED_CREDENTIAL]/[REDACTED_CREDENTIAL] and verify PPP Profile import functionality.

## Test Environment
- **Router IP**: [REDACTED_IP]
- **API Port**: 8777 (REST API) / 8728 (Binary API)
- **Username**: [REDACTED_USERNAME]
- **Password**: [REDACTED_PASSWORD]
- **Protocol**: HTTP REST API (v7+) or Binary API (v6/v7)

## Previous Test Results (REST API Only)

### Connection Test Results

**Test 1: Basic TCP Connectivity**
- ✓ TCP connection to [REDACTED_IP]:8777 **ESTABLISHED**
- ✗ HTTP response **NOT RECEIVED** (connection hangs)
- Timeout occurred after 60+ seconds

**Test 2: PPP Profile Endpoint Test**
- ✓ TCP connection established
- ✗ HTTP GET to `/api/ppp/profile` endpoint **TIMEOUT**
- No response received within 60 seconds

### Analysis

The connection behavior indicated:

1. **TCP Layer**: ✓ Working
   - The router is reachable on port 8777
   - TCP handshake completes successfully
   - No firewall blocking at TCP level

2. **HTTP Layer**: ✗ Not Responding
   - HTTP requests are sent but never receive responses
   - Connection remains open but idle
   - Suggests HTTP/REST API service is not running or misconfigured

## Solution Implemented

### Dual API Support ✅

The system now supports **BOTH** API types:

1. **Binary API** (Port 8728 - RouterOS v6 and v7)
   - Native RouterOS protocol
   - Works with ALL RouterOS versions
   - More reliable and battle-tested
   - **Recommended for v6 routers**

2. **REST API** (Port 8777/80/443 - RouterOS v7.1+ only)
   - HTTP-based API
   - Requires RouterOS v7.1beta4 or newer
   - Requires www service enabled

### Auto-Detection Feature

- System automatically tries Binary API first
- Falls back to REST API if Binary fails
- Configurable via `api_type` field in database
- No manual configuration needed

### New Configuration Options

**Router `api_type` Field**:
- `auto` - Automatic detection (default, recommended)
- `binary` - Force Binary API (for v6 routers)
- `rest` - Force REST API (for v7.1+ routers)

## Updated Recommendations

### For Router at [REDACTED_IP]

Since REST API (port 8777) is not responding, enable Binary API:

```routeros
/ip service set api disabled=no port=8728
```

Then update router configuration:

```php
$router = MikrotikRouter::find(1);
$router->api_port = 8728;
$router->api_type = 'binary';  // or 'auto' for auto-detection
$router->save();
```

### For All Routers

**RouterOS v6 Routers:**
```routeros
/ip service set api disabled=no port=8728
```
- Set `api_type = 'binary'` or `'auto'`
- Binary API is the only option

**RouterOS v7.1+ Routers:**
```routeros
# Option 1: Binary API (recommended)
/ip service set api disabled=no port=8728

# Option 2: REST API
/ip service set www disabled=no port=8777
```
- Set `api_type = 'auto'` for automatic selection
- Both APIs work, binary preferred

## Changes Made

### 1. Added Binary API Library
- Package: `bencroker/routeros-api-php`
- Provides native RouterOS protocol support

### 2. Created RouterOSBinaryApiService
- Complete Binary API implementation
- Response normalization
- Sensitive data sanitization
- Error handling and logging

### 3. Enhanced MikrotikApiService
- Now acts as API selector/dispatcher
- Auto-detection logic
- Transparent switching between APIs
- Maintains backward compatibility

### 4. Database Migration
- Added `api_type` enum field to mikrotik_routers
- Default value: `auto`
- Migration: `2026_01_29_100141_add_api_type_to_mikrotik_routers_table.php`

### 5. Increased Timeout
- Updated from 30 to 60 seconds in `config/services.php`
- Updated all timeout references in MikrotikApiService
- Updated .env.example with new settings

### 6. Documentation
- Created `ROUTEROS_DUAL_API_SUPPORT.md`
- Created installation script `install-routeros-api.sh`
- Updated this connection test report

## Installation Steps

1. **Install Dependencies**
   ```bash
   composer require bencroker/routeros-api-php:^1.0
   # or run: ./install-routeros-api.sh
   ```

2. **Run Migration**
   ```bash
   php artisan migrate
   ```

3. **Clear Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Enable Binary API on Router**
   ```routeros
   /ip service set api disabled=no port=8728
   ```

5. **Update Router Configuration** (Optional)
   ```php
   $router = MikrotikRouter::find(1);
   $router->api_port = 8728;
   $router->api_type = 'auto';  // Let system auto-detect
   $router->save();
   ```

6. **Test Connection**
   ```bash
   php artisan tinker
   >>> $router = App\Models\MikrotikRouter::find(1);
   >>> $service = app(App\Services\MikrotikApiService::class);
   >>> $profiles = $service->getMktRows($router, '/ppp/profile');
   ```

## Testing Instructions

### Test Binary API Connection
```bash
# Test from command line
telnet [REDACTED_IP] 8728

# Test from application
php artisan tinker
>>> $router = App\Models\MikrotikRouter::find(1);
>>> $service = app(App\Services\RouterOSBinaryApiService::class);
>>> $service->testConnection($router);
```

### Test Auto-Detection
```bash
php artisan tinker
>>> $router = App\Models\MikrotikRouter::find(1);
>>> $router->api_type = 'auto';
>>> $router->api_port = 8728;  # Binary API port
>>> $router->save();
>>> $service = app(App\Services\MikrotikApiService::class);
>>> $profiles = $service->getMktRows($router, '/ppp/profile');
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
- `Auto-detected binary API for router`
- `Successfully fetched rows from MikroTik via binary API`
- `Binary API failed, using REST API for router`

## API Compatibility Matrix

| RouterOS Version | Binary API | REST API | Recommended Setting |
|------------------|------------|----------|---------------------|
| v6.0 - v6.48     | ✅ Yes     | ❌ No    | `api_type = 'binary'` or `'auto'` |
| v7.0 - v7.1beta3 | ✅ Yes     | ❌ No    | `api_type = 'binary'` or `'auto'` |
| v7.1beta4+       | ✅ Yes     | ✅ Yes   | `api_type = 'auto'` (prefers binary) |

## Conclusion

**Status**: ✅ Solution Implemented

**Reason**: Added dual API support to work with both v6 and v7 routers

**Action Required**: 
1. Run installation script or manually install dependency
2. Run migration to add api_type field
3. Enable Binary API service on router (port 8728)
4. System will automatically use the correct API

**Next Steps**: 
1. Install composer dependency
2. Run migration
3. Enable Binary API on router: `/ip service set api disabled=no port=8728`
4. Test connection with updated configuration

## Files Modified
- `composer.json` - Added bencroker/routeros-api-php dependency
- `app/Models/MikrotikRouter.php` - Added api_type field
- `app/Services/MikrotikApiService.php` - Added dual API support
- `app/Services/RouterOSBinaryApiService.php` - New Binary API adapter (created)
- `database/migrations/2026_01_29_100141_add_api_type_to_mikrotik_routers_table.php` - New migration (created)
- `ROUTEROS_DUAL_API_SUPPORT.md` - New documentation (created)
- `install-routeros-api.sh` - Installation script (created)
- `MIKROTIK_CONNECTION_TEST_REPORT.md` - Updated with solution

## Support
For detailed information, see:
- `ROUTEROS_DUAL_API_SUPPORT.md` - Complete dual API documentation
- MikroTik Wiki: https://wiki.mikrotik.com/wiki/Manual:API
- RouterOS Documentation: https://help.mikrotik.com/docs/
