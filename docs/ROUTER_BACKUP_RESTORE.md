# Router Backup and Restore Guide

## Overview

This guide explains how to create, manage, and restore router configuration backups in the ISP Solution system. Backups protect your router configurations and allow quick recovery from mistakes or failures.

## Table of Contents

- [Introduction](#introduction)
- [Backup Types](#backup-types)
- [Creating Backups](#creating-backups)
- [Managing Backups](#managing-backups)
- [Restoring Configurations](#restoring-configurations)
- [Backup Strategies](#backup-strategies)
- [Scheduled Backups](#scheduled-backups)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Introduction

The backup system provides comprehensive protection for your router configurations by:

- **Automatic Backups**: Created before any configuration change
- **Manual Backups**: On-demand backups whenever needed
- **Version History**: Multiple backup versions with timestamps
- **Quick Restore**: One-click restoration of previous configurations
- **Tenant Isolation**: Backups are securely isolated per tenant

### What Gets Backed Up

Each backup includes:
- PPP profiles
- PPP secrets (customer credentials)
- IP pools
- RADIUS configuration
- Firewall rules
- PPP AAA settings
- Netwatch configuration
- System identity and settings

### What Does NOT Get Backed Up

- RouterOS system files
- Installed packages
- Log files
- Interface configurations (LAN/WAN)
- IP addresses and routing

## Backup Types

### 1. Pre-Change Backup

**Trigger**: Automatically created before any configuration change

**Use Case**: 
- Rollback if configuration fails
- Audit trail of changes

**Naming Convention**: `pre-change-{timestamp}`

**Retention**: Controlled by `MIKROTIK_BACKUP_RETENTION_DAYS`

### 2. Manual Backup

**Trigger**: Created by admin on demand

**Use Case**:
- Before major changes
- Milestone configurations
- Testing new features

**Naming Convention**: User-provided name

**Retention**: Not automatically deleted

### 3. Import Backup

**Trigger**: Automatically created before importing data from router

**Use Case**:
- Rollback if import fails
- Compare before/after import

**Naming Convention**: `pre-import-{type}-{timestamp}`

**Retention**: Controlled by retention policy

### 4. Scheduled Backup

**Trigger**: Created by scheduled task (cron job)

**Use Case**:
- Regular snapshots
- Disaster recovery
- Compliance

**Naming Convention**: `scheduled-{timestamp}`

**Retention**: Keep last 30 days by default

## Creating Backups

### Via Web UI

#### Manual Backup

1. Navigate to **Network → Routers**
2. Click on a router
3. Go to **Backups** tab
4. Click **Create Backup** button
5. Fill in the form:
   - **Backup Name**: Descriptive name (e.g., "Before RADIUS Config")
   - **Reason**: Why you're creating this backup (optional)
6. Click **Create**

The system will:
- Connect to the router
- Export all configurations
- Store backup in database
- Show confirmation message

#### Automatic Backup (Pre-Change)

Automatic backups are created before:
- Configuring RADIUS
- Configuring PPP
- Configuring Firewall
- Importing data from router
- Bulk user provisioning

**Configuration:**
```bash
# Enable auto-backup in .env
MIKROTIK_AUTO_BACKUP=true
```

### Via API

#### Create Manual Backup

```bash
POST /api/routers/backup/{router_id}/create
Content-Type: application/json

{
  "name": "Before RADIUS Config",
  "reason": "Testing new RADIUS settings"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Backup created successfully.",
  "backup": {
    "id": 42,
    "name": "Before RADIUS Config",
    "created_at": "2026-01-26 21:00:00"
  }
}
```

### Via Console Command

```bash
# Create backup for specific router
php artisan router:backup {router_id} --name="Manual backup" --reason="Weekly backup"

# Create backup for all routers
php artisan router:backup --all

# Create backup with custom retention
php artisan router:backup {router_id} --retention=90
```

### Programmatically

```php
use App\Services\RouterBackupService;
use App\Models\MikrotikRouter;

$backupService = app(RouterBackupService::class);
$router = MikrotikRouter::find($routerId);

// Create manual backup
$backup = $backupService->createManualBackup(
    $router,
    'Before Major Change',
    'Upgrading to new package structure',
    auth()->id()
);

// Create pre-change backup (automatic)
$backup = $backupService->createPreChangeBackup(
    $router,
    'configure_radius'
);
```

## Managing Backups

### Viewing Backups

#### Via Web UI

1. Navigate to **Network → Routers**
2. Click on a router
3. Go to **Backups** tab

You'll see a table with:
- **Backup Name**
- **Type** (badge: manual, pre-change, import, scheduled)
- **Reason**
- **Created At**
- **Created By**
- **Actions** (Restore, Download, Delete)

#### Filtering

Use the dropdown to filter by backup type:
- All Backups
- Manual Only
- Pre-Change Only
- Import Only
- Scheduled Only

#### Via API

```bash
# List all backups for router
GET /api/routers/backup/{router_id}/list

# List backups by type
GET /api/routers/backup/{router_id}/list?type=manual
```

**Response:**
```json
{
  "success": true,
  "backups": [
    {
      "id": 42,
      "type": "manual",
      "notes": "Before RADIUS Config",
      "created_at": "2026-01-26 21:00:00",
      "created_by": 1
    },
    {
      "id": 41,
      "type": "pre-change",
      "notes": "Pre-change: configure_radius",
      "created_at": "2026-01-26 20:30:00",
      "created_by": 1
    }
  ]
}
```

### Deleting Backups

#### Via Web UI

1. Navigate to **Network → Routers → Backups**
2. Click **Delete** button for the backup
3. Confirm deletion

**Warning**: Deletion is permanent and cannot be undone.

#### Via API

```bash
DELETE /api/routers/backup/{router_id}/backups/{backup_id}
```

#### Via Console Command

```bash
# Delete specific backup
php artisan router:backup {router_id} --delete={backup_id}

# Delete all backups older than 90 days
php artisan router:backup {router_id} --cleanup --retention=90
```

### Cleaning Up Old Backups

#### Via Web UI

1. Navigate to **Network → Routers → Backups**
2. Click **Cleanup Old Backups**
3. Choose retention period (default: 30 days)
4. Click **Cleanup**

#### Via API

```bash
POST /api/routers/backup/{router_id}/cleanup
Content-Type: application/json

{
  "retention_days": 30
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deleted 5 old backup(s).",
  "deleted_count": 5
}
```

#### Automatic Cleanup

Configure automatic cleanup in `.env`:

```bash
MIKROTIK_BACKUP_RETENTION_DAYS=30
```

Set up a cron job:

```bash
# Clean up old backups daily at 3 AM
0 3 * * * php artisan router:backup --cleanup-all --retention=30
```

## Restoring Configurations

### When to Restore

Restore a backup when:
- Configuration change caused issues
- Accidentally deleted critical settings
- Testing didn't go as planned
- Need to revert to known-good state
- Disaster recovery

### Pre-Restore Checklist

Before restoring:

1. ✅ **Create a backup** of current configuration (just in case)
2. ✅ **Notify users** if restoration will cause disconnections
3. ✅ **Verify backup** is from the correct router
4. ✅ **Check backup age** - ensure it's the right version
5. ✅ **Review changes** that will be reverted

### Via Web UI

1. Navigate to **Network → Routers → Backups**
2. Find the backup you want to restore
3. Click **Restore** button
4. Review the confirmation dialog:
   - Backup name and date
   - Warning about current configuration
5. Click **Confirm Restore**

The system will:
- Create a pre-restore backup (current config)
- Connect to the router
- Apply the backed-up configuration
- Verify restoration
- Show success/failure message

### Via API

```bash
POST /api/routers/backup/{router_id}/restore
Content-Type: application/json

{
  "backup_id": 42
}
```

**Response:**
```json
{
  "success": true,
  "message": "Configuration restored successfully."
}
```

### Via Console Command

```bash
# Restore specific backup
php artisan router:backup {router_id} --restore={backup_id}

# Restore latest backup
php artisan router:backup {router_id} --restore=latest

# Restore with confirmation prompt
php artisan router:backup {router_id} --restore={backup_id} --interactive
```

### Programmatically

```php
use App\Services\RouterBackupService;
use App\Models\RouterConfigurationBackup;

$backupService = app(RouterBackupService::class);
$backup = RouterConfigurationBackup::find($backupId);

// Restore from backup
$success = $backupService->restoreFromBackup(
    $router,
    $backup
);

if ($success) {
    Log::info('Backup restored successfully', [
        'router_id' => $router->id,
        'backup_id' => $backup->id,
    ]);
}
```

### Post-Restore Steps

After restoration:

1. ✅ **Verify router connectivity**
   ```bash
   php artisan router:health {router_id}
   ```

2. ✅ **Test customer connections**
   - Have a test customer authenticate
   - Verify they can connect and access internet

3. ✅ **Check RADIUS status**
   ```bash
   # On router
   /ppp aaa print
   ```

4. ✅ **Review configuration**
   - Ensure all expected settings are restored
   - Check PPP profiles and secrets

5. ✅ **Monitor for issues**
   - Watch logs for errors
   - Monitor customer complaints

## Backup Strategies

### Strategy 1: Minimal (Development)

**Use Case**: Development/testing environments

**Configuration**:
- Auto-backup: Enabled
- Manual backups: As needed
- Scheduled backups: None
- Retention: 7 days

**Cron Jobs**: None

### Strategy 2: Standard (Small ISP)

**Use Case**: Small ISPs with 1-10 routers

**Configuration**:
- Auto-backup: Enabled
- Manual backups: Before major changes
- Scheduled backups: Daily
- Retention: 30 days

**Cron Jobs**:
```bash
# Daily backup at 2 AM
0 2 * * * php artisan router:backup --all --name="Daily Backup"

# Weekly cleanup at 3 AM Sunday
0 3 * * 0 php artisan router:backup --cleanup-all --retention=30
```

### Strategy 3: Enterprise (Large ISP)

**Use Case**: Large ISPs with 10+ routers

**Configuration**:
- Auto-backup: Enabled
- Manual backups: Before major changes
- Scheduled backups: Daily + Weekly
- Retention: 90 days (daily), 365 days (weekly)
- Off-site backup: Enabled

**Cron Jobs**:
```bash
# Daily backup at 2 AM
0 2 * * * php artisan router:backup --all --name="Daily Backup"

# Weekly backup at 2 AM Sunday (long retention)
0 2 * * 0 php artisan router:backup --all --name="Weekly Backup" --retention=365

# Daily cleanup at 3 AM (keep 90 days of daily backups)
0 3 * * * php artisan router:backup --cleanup-all --retention=90 --exclude-type=weekly

# Export backups to external storage at 4 AM
0 4 * * * php artisan router:backup --export-all --destination=/mnt/backup
```

### Strategy 4: Compliance (Highly Regulated)

**Use Case**: ISPs with strict compliance requirements

**Configuration**:
- Auto-backup: Enabled
- Manual backups: All changes (mandatory)
- Scheduled backups: Daily, Weekly, Monthly
- Retention: 7 years
- Audit logs: Enabled
- Immutable backups: Enabled
- Off-site backup: Required

**Cron Jobs**:
```bash
# Daily backup
0 2 * * * php artisan router:backup --all --name="Daily-$(date +%Y%m%d)"

# Weekly backup (every Sunday)
0 2 * * 0 php artisan router:backup --all --name="Weekly-$(date +%Y-W%V)"

# Monthly backup (1st of month)
0 2 1 * * php artisan router:backup --all --name="Monthly-$(date +%Y%m)"

# Export to compliance storage daily
0 4 * * * php artisan router:backup --export-all --destination=/mnt/compliance --encrypt

# Cleanup (but keep for 7 years)
0 3 1 * * php artisan router:backup --cleanup-all --retention=2555
```

## Scheduled Backups

### Setting Up Cron Jobs

#### Daily Backups

Add to `/etc/crontab` or use `crontab -e`:

```bash
# Daily backup at 2 AM for all routers
0 2 * * * cd /path/to/ispsolution && php artisan router:backup --all
```

#### Weekly Backups

```bash
# Weekly backup every Sunday at 2 AM
0 2 * * 0 cd /path/to/ispsolution && php artisan router:backup --all --name="Weekly Backup"
```

#### Hourly Backups (Critical Routers)

```bash
# Backup critical router every hour
0 * * * * cd /path/to/ispsolution && php artisan router:backup 1 --name="Hourly"
```

### Using Laravel Task Scheduler

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup for all routers at 2 AM
    $schedule->command('router:backup --all')
        ->dailyAt('02:00')
        ->onOneServer()
        ->emailOutputOnFailure('admin@example.com');
    
    // Weekly cleanup of old backups
    $schedule->command('router:backup --cleanup-all --retention=30')
        ->weekly()
        ->sundays()
        ->at('03:00');
    
    // Critical router backup every 4 hours
    $schedule->command('router:backup 1')
        ->cron('0 */4 * * *');
}
```

Then add to crontab:

```bash
* * * * * cd /path/to/ispsolution && php artisan schedule:run >> /dev/null 2>&1
```

### Monitoring Scheduled Backups

#### Check Last Backup

```bash
# Via console
php artisan router:backup {router_id} --status

# Via API
GET /api/routers/backup/{router_id}/list?limit=1
```

#### Set Up Alerts

```php
// In EventServiceProvider
Event::listen(BackupCreated::class, function ($event) {
    if ($event->backupType === 'scheduled') {
        // Send notification
        Notification::send(
            User::admins(),
            new BackupCompletedNotification($event->backup)
        );
    }
});
```

#### Failed Backup Alerts

```bash
# Check for failed backups
php artisan router:backup --check-failures --alert

# Email output on failure (in cron job)
0 2 * * * php artisan router:backup --all || mail -s "Backup Failed" admin@example.com
```

## Best Practices

### 1. Always Backup Before Changes

**Rule**: Never make configuration changes without a backup.

**Implementation**:
```php
// Automatically enforced in configuration service
$backupService->createPreChangeBackup($router, 'configure_radius');
$configService->configureRadius($router);
```

### 2. Test Restores Regularly

**Frequency**: Monthly

**Process**:
1. Select a non-production router (or production during maintenance)
2. Create a fresh backup
3. Make a small test change
4. Restore the backup
5. Verify restoration worked
6. Document any issues

### 3. Keep Multiple Versions

**Recommendation**: Keep at least 30 days of backups

**Rationale**:
- Issues may not be immediately apparent
- Need to compare configurations over time
- Compliance requirements

### 4. Document Major Changes

When creating manual backups, use descriptive names and reasons:

✅ **Good**:
- Name: "Pre-VLAN-Migration"
- Reason: "Before migrating all customers to VLAN 100"

❌ **Bad**:
- Name: "backup1"
- Reason: ""

### 5. Off-Site Backups

For critical routers, store backups off-site:

```bash
# Export backups to external storage
php artisan router:backup --export-all --destination=/mnt/nfs-backup

# Sync to cloud storage
php artisan router:backup --export-all --destination=s3://my-bucket/backups
```

### 6. Monitor Backup Size

Large backups may indicate issues:

```bash
# Check backup sizes
php artisan router:backup --stats

# Alert on large backups
if [ $(du -sm /backups/router-1 | cut -f1) -gt 100 ]; then
    echo "Backup size exceeds 100MB" | mail -s "Alert" admin@example.com
fi
```

### 7. Encryption for Sensitive Data

Backups contain customer credentials. Ensure they're encrypted:

```bash
# Encrypt sensitive fields in database
# (Already done in Nas model - 'secret' field is encrypted)

# Encrypt backup exports
php artisan router:backup --export-all --encrypt
```

## Troubleshooting

### Issue: Backup Creation Fails

**Error**: "Failed to connect to router"

**Solutions**:

1. Verify router connectivity:
   ```bash
   php artisan router:test {router_id}
   ```

2. Check credentials:
   ```routeros
   # On router, verify API is enabled
   /ip service print
   ```

3. Check firewall:
   ```bash
   telnet <router_ip> 8728
   ```

4. Review logs:
   ```bash
   tail -f storage/logs/laravel.log | grep -i backup
   ```

### Issue: Restore Fails Midway

**Error**: "Restore failed: connection lost"

**Solutions**:

1. **Don't panic** - router configuration is likely inconsistent

2. Try restoring again:
   ```bash
   php artisan router:backup {router_id} --restore={backup_id} --force
   ```

3. If that fails, manually restore via RouterOS:
   ```routeros
   /system backup load name=backup-file.backup
   ```

4. Last resort - factory reset and reconfigure

### Issue: Backup Takes Too Long

**Symptoms**:
- Backup creation takes > 5 minutes
- Timeout errors

**Solutions**:

1. Increase timeout in config:
   ```php
   // config/mikrotik.php
   'timeout' => env('MIKROTIK_TIMEOUT', 30),
   ```

2. Check router load:
   ```routeros
   /system resource print
   ```

3. Limit concurrent backups:
   ```bash
   # In cron job, backup routers one at a time instead of --all
   ```

### Issue: Restore Doesn't Seem to Work

**Symptoms**:
- Restore completes successfully
- But configuration hasn't changed

**Solutions**:

1. Check what was actually restored:
   ```php
   $backup = RouterConfigurationBackup::find($backupId);
   dd(json_decode($backup->configuration_data));
   ```

2. Verify router connection during restore:
   ```bash
   php artisan router:test {router_id}
   ```

3. Check if backup is empty or corrupted:
   ```bash
   php artisan tinker
   >>> $backup = RouterConfigurationBackup::find(42);
   >>> strlen($backup->configuration_data);
   ```

### Issue: Old Backups Not Being Deleted

**Symptoms**:
- Backup cleanup runs but old backups remain
- Disk space growing

**Solutions**:

1. Check retention setting:
   ```bash
   php artisan tinker
   >>> config('mikrotik.backup.retention_days');
   ```

2. Run cleanup manually with logs:
   ```bash
   php artisan router:backup --cleanup-all --retention=30 --verbose
   ```

3. Check for manual backups (not auto-deleted):
   ```bash
   php artisan router:backup --list --type=manual
   ```

4. Force cleanup if needed:
   ```bash
   php artisan router:backup --force-cleanup --before="2025-01-01"
   ```

## Related Documentation

- [ROUTER_RADIUS_FAILOVER.md](ROUTER_RADIUS_FAILOVER.md) - Failover configuration
- [ROUTER_PROVISIONING_GUIDE.md](ROUTER_PROVISIONING_GUIDE.md) - User provisioning
- [MIKROTIK_QUICKSTART.md](MIKROTIK_QUICKSTART.md) - MikroTik setup

## Support

If you encounter issues not covered in this guide:

1. Check logs: `storage/logs/laravel.log`
2. Verify router connection: `php artisan router:test {router_id}`
3. Review backup history in database
4. Contact support with router ID and backup ID
