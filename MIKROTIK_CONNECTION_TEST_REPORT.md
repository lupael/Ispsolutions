# Mikrotik Connection Testing Report

## Date: 2026-01-29

## Objective
Test connectivity to Mikrotik router at 103.138.147.185:8777 with credentials ispsolution1213/ispsolution1213 and verify PPP Profile import functionality.

## Test Environment
- **Router IP**: 103.138.147.185
- **API Port**: 8777
- **Username**: ispsolution1213
- **Password**: ispsolution1213
- **Protocol**: HTTP (RouterOS REST API)

## Test Results

### Connection Test Results

**Test 1: Basic TCP Connectivity**
- ✓ TCP connection to 103.138.147.185:8777 **ESTABLISHED**
- ✗ HTTP response **NOT RECEIVED** (connection hangs)
- Timeout occurred after 60+ seconds

**Test 2: PPP Profile Endpoint Test**
- ✓ TCP connection established
- ✗ HTTP GET to `/api/ppp/profile` endpoint **TIMEOUT**
- No response received within 60 seconds

### Analysis

The connection behavior indicates:

1. **TCP Layer**: ✓ Working
   - The router is reachable on port 8777
   - TCP handshake completes successfully
   - No firewall blocking at TCP level

2. **HTTP Layer**: ✗ Not Responding
   - HTTP requests are sent but never receive responses
   - Connection remains open but idle
   - Suggests HTTP/REST API service is not running or misconfigured

### Possible Root Causes

1. **RouterOS REST API Not Enabled**
   - The REST API service may not be running on the router
   - Check: `/ip service print` on RouterOS to verify API service is enabled
   - The api-ssl or www-ssl service should be enabled on port 8777

2. **Wrong Port Configuration**
   - Port 8777 might be configured for a different service
   - Standard MikroTik ports:
     - API: 8728 (binary protocol)
     - API-SSL: 8729 (secure binary protocol)
     - HTTP: 80 (web interface)
     - HTTPS: 443 (secure web interface)
     - REST API: Requires www or www-ssl service with proper configuration

3. **Authentication Issues**
   - Credentials may be correct for RouterOS but REST API might require additional permissions
   - User might not have API access rights

4. **Network/Firewall Issues**
   - Deep packet inspection (DPI) might be blocking HTTP traffic
   - Proxy or NAT issues between client and router
   - Router CPU overload causing service delays

5. **RouterOS Version Issues**
   - REST API was introduced in RouterOS v7.1beta4
   - If router is running older version, REST API is not available

## Changes Made

### 1. Increased Timeout to 60 Seconds

Updated timeout configuration from 30 to 60 seconds as requested:

**File: config/services.php**
```php
'mikrotik' => [
    'timeout' => env('MIKROTIK_API_TIMEOUT', 60),  // Increased from 30
    'default_port' => env('MIKROTIK_DEFAULT_PORT', 8728),
    'max_retries' => env('MIKROTIK_MAX_RETRIES', 3),
    'retry_delay' => env('MIKROTIK_RETRY_DELAY', 1000),
],
```

**File: app/Services/MikrotikApiService.php**
- Updated all `->timeout(config('services.mikrotik.timeout', 60))` calls (5 locations)
- Changed fallback value from 30 to 60 seconds for consistency

**File: .env.example**
```env
MIKROTIK_API_TIMEOUT=60  # Increased from 30
MIKROTIK_MAX_RETRIES=3
MIKROTIK_RETRY_DELAY=1000
```

### 2. Configuration Consolidation

Added `max_retries` and `retry_delay` to the services config for centralized configuration.

## Recommendations for Router Owner

### Immediate Actions Required

1. **Verify REST API Service Status**
   ```
   /ip service print
   ```
   Look for `api` or `www` service on port 8777

2. **Enable REST API (if not enabled)**
   ```
   /ip service set api-ssl disabled=no port=8777
   # Or for standard HTTP:
   /ip service set www disabled=no port=8777
   ```

3. **Check User Permissions**
   ```
   /user print detail
   ```
   Ensure user `ispsolution1213` has full API access

4. **Test from Router Console**
   ```
   /tool fetch url="http://127.0.0.1:8777/api" mode=http
   ```

5. **Check RouterOS Version**
   ```
   /system package print
   ```
   Ensure version is 7.1beta4 or newer for REST API support

### Alternative Connection Methods

If REST API continues to fail, consider:

1. **MikroTik Binary API (Port 8728)**
   - More reliable and widely supported
   - Use PHP library like `routeros-api-php`
   - Change `api_port` to 8728 in router configuration

2. **SSH with Command Parsing**
   - Always available on MikroTik routers
   - Use SSH libraries to execute commands
   - Parse text output

3. **Winbox API**
   - Native binary protocol
   - Most efficient method
   - Requires binary protocol implementation

## Testing Instructions for End Users

Once the router API service is properly configured:

1. **Update Environment**
   ```bash
   # In .env file
   MIKROTIK_API_TIMEOUT=60
   ```

2. **Clear Configuration Cache**
   ```bash
   php artisan config:clear
   ```

3. **Test via Application**
   - Navigate to Router Management
   - Add router with IP 103.138.147.185, Port 8777
   - Try to import PPP Profiles
   - Check `storage/logs/laravel.log` for detailed error messages

4. **Expected Log Entries**
   
   **Success:**
   ```
   [INFO] Successfully fetched rows from MikroTik
   {router_id: 1, menu: "/ppp/profile", count: X}
   ```
   
   **Timeout:**
   ```
   [WARNING] Failed to fetch rows from MikroTik
   {router_id: 1, menu: "/ppp/profile", status: timeout}
   ```

## Technical Details

### HTTP Request Format
```http
GET http://103.138.147.185:8777/api/ppp/profile HTTP/1.1
Host: 103.138.147.185:8777
Authorization: Basic aXNwc29sdXRpb24xMjEzOmlzcHNvbHV0aW9uMTIxMw==
Accept: */*
```

### Timeout Behavior
- **Connection Timeout**: 10 seconds (system default)
- **Read Timeout**: 60 seconds (configured)
- **Total Timeout**: 60 seconds maximum per request
- **Retries**: Up to 3 attempts with 1 second delay between attempts

### Expected Response Format
```json
[
  {
    ".id": "*1",
    "name": "default",
    "local-address": "10.0.0.1",
    "remote-address": "10.0.0.2-10.0.0.254",
    "rate-limit": "10M/10M"
  }
]
```

## Conclusion

**Status**: ❌ Unable to connect to Mikrotik REST API

**Reason**: Router is reachable but HTTP/REST API service is not responding

**Action Required**: Router owner must verify and enable REST API service on RouterOS

**Code Changes**: ✅ Timeout increased to 60 seconds as requested

**Next Steps**: 
1. Router owner to check RouterOS REST API configuration
2. Verify RouterOS version supports REST API
3. Consider alternative API methods if REST API unavailable
4. Re-test connection after router configuration changes

## Files Modified
- `config/services.php` - Increased timeout to 60s, added retry config
- `app/Services/MikrotikApiService.php` - Updated all timeout references to 60s
- `.env.example` - Updated default timeout value and added retry settings
- `MIKROTIK_CONNECTION_TEST_REPORT.md` - This documentation

## Support
For issues with Mikrotik router configuration, refer to:
- MikroTik Wiki: https://wiki.mikrotik.com/wiki/Manual:REST_API
- RouterOS Documentation: https://help.mikrotik.com/docs/
