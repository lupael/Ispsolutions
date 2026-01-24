# Router-to-RADIUS Migration Tool

This tool migrates a MikroTik router from local PPP authentication to RADIUS authentication.

## Command Signature

```bash
php artisan mikrotik:migrate-to-radius {router_id} [OPTIONS]
```

## Arguments

- `router_id` - The ID of the router to migrate (required)

## Options

- `--force` - Skip confirmation prompts
- `--no-backup` - Skip backup creation (not recommended)
- `--test-user=USERNAME` - Username to test RADIUS authentication

## Migration Process

The tool performs a 7-step migration process:

1. **Verify RADIUS Server Connectivity** - Pings RADIUS server to ensure it's reachable
2. **Backup Current PPP Secrets** - Saves current configuration to storage (kept for 7 days)
3. **Configure RADIUS Authentication** - Adds RADIUS server and enables PPP AAA
4. **Test RADIUS Authentication** - Validates authentication with test user (optional)
5. **Disable Local PPP Secrets** - Disables all local PPP secrets
6. **Disconnect Active Sessions** - Forces reconnection with RADIUS auth
7. **Verify Migration** - Confirms RADIUS is enabled and working

## Usage Examples

### Basic migration with confirmation prompt
```bash
php artisan mikrotik:migrate-to-radius 1
```

### Force migration without prompts
```bash
php artisan mikrotik:migrate-to-radius 1 --force
```

### Skip backup creation (not recommended)
```bash
php artisan mikrotik:migrate-to-radius 1 --no-backup
```

### Include authentication test
```bash
php artisan mikrotik:migrate-to-radius 1 --test-user=testuser
```

### Combined options
```bash
php artisan mikrotik:migrate-to-radius 1 --force --test-user=testuser
```

## Safety Features

- **Interactive Confirmation** - Requires user confirmation before proceeding (unless --force)
- **Automatic Backup** - Creates JSON backup of all PPP secrets before migration
- **Step-by-Step Verification** - Validates each step before proceeding
- **Test Authentication** - Optional RADIUS authentication test before completing
- **Automatic Rollback** - Restores original configuration if migration fails
- **Detailed Logging** - All operations are logged for auditing
- **7-Day Retention** - Backup files kept for 7 days in cache for emergency rollback

## Rollback Process

If migration fails, the tool automatically:
1. Retrieves backup from cache
2. Re-enables all disabled PPP secrets
3. Disables RADIUS authentication
4. Restores original configuration

## Configuration Requirements

Before running migration, ensure these are configured in your `.env` or config files:

```env
RADIUS_SERVER=192.168.1.100
RADIUS_SECRET=your-secret-key
RADIUS_AUTH_PORT=1812
RADIUS_ACCT_PORT=1813
```

## Backup Location

Backups are stored in: `storage/app/backups/router-migrations/`

Format: `router_{router_id}_ppp_secrets_{timestamp}.json`

## Manual Rollback

If needed, you can manually rollback using Laravel Tinker:

```php
$router = App\Models\MikrotikRouter::find(1);
$service = app(App\Services\RouterMigrationService::class);
$service->rollback($router);
```

## Troubleshooting

### Migration fails at Step 1 (RADIUS connectivity)
- Check RADIUS server is running
- Verify router can reach RADIUS server
- Confirm RADIUS server address in configuration

### Migration fails at Step 3 (Configure RADIUS)
- Check router API is accessible
- Verify router credentials are correct
- Ensure router has sufficient resources

### Migration fails at Step 4 (Test authentication)
- Ensure test user exists in radcheck table
- Verify RADIUS server is accepting authentication requests
- Check RADIUS secret matches configuration

### Active sessions not reconnecting
- Users need to disconnect and reconnect manually
- Or use the disconnect feature to force reconnection

## Best Practices

1. **Test User** - Always use `--test-user` option to verify RADIUS authentication works
2. **Backup** - Never use `--no-backup` in production
3. **Maintenance Window** - Run during low-usage periods
4. **Monitoring** - Monitor RADIUS server during migration
5. **Documentation** - Document the migration for your team
6. **Testing** - Test on a development router first

## Security Considerations

- Backups contain sensitive PPP secrets - ensure proper file permissions
- RADIUS secret is transmitted to router - use secure network
- Cache contains backup references - ensure cache is secure
- Logs may contain sensitive information - review log retention policies
