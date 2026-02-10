# OLT Dashboard Authentication Fix

## Problem Summary

The OLT Dashboard and related OLT management pages were experiencing a 401 (Unauthorized) error when attempting to load data from the API endpoint `/api/v1/olt/`. This affected multiple pages:

- OLT Dashboard (`/panel/admin/olt/dashboard`)
- OLT Monitor (`/panel/admin/olt/{id}/monitor`)
- OLT Performance (`/panel/admin/olt/{id}/performance`)
- OLT Backups (`/panel/admin/olt/backups`)
- OLT Firmware (`/panel/admin/olt/firmware`)

## Error Details

```
GET https://radius.ispbills.com/api/v1/olt/ 401 (Unauthorized)
Failed to load OLT data: Error: HTTP error! status: 401 - Unauthenticated.
```

## Root Cause

The application uses Laravel Sanctum for API authentication with the 'web' guard configured for SPA (Single Page Application) authentication. However, the critical Sanctum middleware `EnsureFrontendRequestsAreStateful` was not configured in the API middleware group in `bootstrap/app.php`.

Without this middleware:
- Same-origin API requests from the web interface could not be authenticated using the session cookie
- All API calls to `/api/v1/olt/*` endpoints failed with 401 Unauthorized
- The user's web session authentication was not being passed to the API layer

## Solution

Added the Sanctum middleware to the API middleware group in `bootstrap/app.php`:

```php
// Add Sanctum middleware for SPA authentication
$middleware->api(prepend: [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
]);
```

### Why This Works

The `EnsureFrontendRequestsAreStateful` middleware:
1. Checks if the request is from a stateful domain (configured in `config/sanctum.php`)
2. Allows Sanctum to authenticate the request using the web session cookie
3. Enables the `auth:sanctum` middleware to recognize authenticated users from the web interface
4. Maintains CSRF protection for API requests from the same origin

## Files Modified

1. **bootstrap/app.php** - Added Sanctum middleware configuration

## Benefits of This Fix

1. **All OLT API endpoints now work properly** from the web interface
2. **Session-based authentication** works seamlessly across web and API routes
3. **No breaking changes** to existing functionality
4. **Maintains security** - CSRF protection still enforced
5. **Enables proper SPA authentication** pattern for Laravel Sanctum

## API Endpoints Fixed

All endpoints under `/api/v1/olt/` now work correctly:

- `GET /api/v1/olt/` - List all OLTs
- `GET /api/v1/olt/{id}` - Get OLT details
- `POST /api/v1/olt/{id}/test-connection` - Test OLT connection
- `POST /api/v1/olt/{id}/sync-onus` - Sync ONUs from OLT
- `GET /api/v1/olt/{id}/statistics` - Get OLT statistics
- `POST /api/v1/olt/{id}/backup` - Create OLT backup
- `GET /api/v1/olt/{id}/backups` - List OLT backups
- `GET /api/v1/olt/{id}/port-utilization` - Get port utilization
- `GET /api/v1/olt/{id}/bandwidth-usage` - Get bandwidth usage
- `GET /api/v1/olt/{id}/monitor-onus` - Monitor ONUs
- `GET /api/v1/olt/onu/{onuId}` - Get ONU details
- `POST /api/v1/olt/onu/{onuId}/refresh` - Refresh ONU status
- `POST /api/v1/olt/onu/{onuId}/authorize` - Authorize ONU
- `POST /api/v1/olt/onu/{onuId}/unauthorize` - Unauthorize ONU
- `POST /api/v1/olt/onu/{onuId}/reboot` - Reboot ONU
- `POST /api/v1/olt/onu/bulk-operations` - Bulk ONU operations

## Testing

To verify the fix:

1. Log in to the admin panel
2. Navigate to OLT Dashboard at `/panel/admin/olt/dashboard`
3. The page should load successfully and display:
   - Active OLTs count
   - Total ONUs count
   - Online ONUs count
   - Offline ONUs count
   - List of OLT devices with their details

4. Test other OLT pages:
   - Monitor page should display real-time ONU status
   - Performance page should show statistics and port utilization
   - Backup page should list backups and allow creating new ones

## Additional Notes

### Sanctum Configuration

The Sanctum configuration in `config/sanctum.php` uses the 'web' guard:

```php
'guard' => ['web'],
```

This means Sanctum will use the default web authentication guard, which uses sessions.

### Stateful Domains

Sanctum is configured to treat requests from the application's own domain as stateful, allowing session-based authentication for same-origin API calls.

### Impact on Other API Routes

This fix benefits ALL API routes that use `auth:sanctum` middleware, not just OLT routes. Other affected routes include:

- Widget APIs (`/api/v1/widgets/*`)
- MikroTik APIs (`/api/v1/mikrotik/*`)
- Customer graph APIs (`/api/v1/customers/{id}/graphs/*`)
- IP Pool Migration APIs (`/api/v1/migrations/*`)
- SMS Payment APIs (`/api/sms-payments/*`)
- Auto-Debit APIs (`/api/auto-debit/*`)
- Subscription Payment APIs (`/api/subscription-payments/*`)
- Bkash Agreement APIs (`/api/bkash-agreements/*`)

All these APIs will now work correctly from the web interface without requiring API tokens.

## References

- [Laravel Sanctum Documentation - SPA Authentication](https://laravel.com/docs/11.x/sanctum#spa-authentication)
- [Laravel 11 Middleware Configuration](https://laravel.com/docs/11.x/middleware)
