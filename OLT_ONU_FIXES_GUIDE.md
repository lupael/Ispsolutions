# OLT/ONU System Fixes and Implementation Guide

## Overview

This document details the fixes and improvements made to the OLT (Optical Line Terminal) and ONU (Optical Network Unit) management system, SNMP trap handling, device monitoring, and backup functionality.

## What Was Fixed

### 1. SNMP Trap Receiver (✓ COMPLETE)

**Problem:** No endpoint existed to receive SNMP traps from OLT devices. The system had models and viewing capabilities for traps, but no way to actually receive them.

**Solution Implemented:**
- Created `SnmpTrapReceiverController` with three endpoints:
  - `/api/v1/snmp-trap/receive` - Main JSON trap receiver
  - `/api/v1/snmp-trap/receive-legacy` - Legacy snmptrapd format receiver
  - `/api/v1/snmp-trap/test` - Test endpoint (non-production only)

- Features:
  - Automatic severity detection based on trap type
  - Critical trap handling updates OLT health status
  - Support for unknown OLTs (records trap for troubleshooting)
  - Proper tenant scoping
  - Comprehensive logging

**Configuration:**

1. **Option A: Direct HTTP Trap Receiver**
   Configure your OLT devices to send SNMP traps as HTTP POST requests:
   ```
   POST https://your-domain.com/api/v1/snmp-trap/receive
   Content-Type: application/json
   
   {
     "source_ip": "192.168.1.100",
     "trap_type": "linkDown",
     "oid": ".1.3.6.1.6.3.1.1.5.3",
     "severity": "critical",
     "message": "PON port 1/1 link down",
     "trap_data": {
       "port": "1/1",
       "interface": "gpon-onu_1/1:1"
     }
   }
   ```

2. **Option B: Using snmptrapd Daemon**
   Configure snmptrapd to forward traps to the application:
   
   Edit `/etc/snmp/snmptrapd.conf`:
   ```conf
   # Authentication
   authCommunity log,execute,net public
   
   # Trap handler script
   traphandle default /usr/local/bin/forward-trap-to-app.sh
   ```
   
   Create `/usr/local/bin/forward-trap-to-app.sh`:
   ```bash
   #!/bin/bash
   
   # Read trap data from stdin
   TRAP_DATA=$(cat)
   
   # Forward to application
   curl -X POST https://your-domain.com/api/v1/snmp-trap/receive-legacy \
     -H "Content-Type: text/plain" \
     -d "$TRAP_DATA"
   ```
   
   Make it executable:
   ```bash
   chmod +x /usr/local/bin/forward-trap-to-app.sh
   ```

3. **Testing:**
   ```bash
   # Test the trap receiver
   curl -X POST https://your-domain.com/api/v1/snmp-trap/test \
     -H "Content-Type: application/json" \
     -d '{
       "source_ip": "192.168.1.100",
       "trap_type": "linkDown",
       "severity": "critical"
     }'
   ```

### 2. ONU Discovery and Synchronization (✓ COMPLETE)

**Problem:** ONU discovery was not working properly due to:
- Generic regex pattern that didn't match vendor-specific outputs
- Poor error handling
- No validation of discovered data
- Missing logging for troubleshooting

**Solution Implemented:**
- Vendor-specific ONU list parsing for:
  - VSOL (V-SOL)
  - Huawei
  - ZTE
  - Fiberhome
  - Generic fallback for unknown vendors

- Improved discovery process:
  - Try SNMP first if configured
  - Fallback to SSH if SNMP fails or returns no results
  - Comprehensive logging at each step
  - Better error handling

- Enhanced sync functionality:
  - Batch processing with error isolation
  - Serial number validation
  - Proper tenant_id scoping
  - Track created vs updated ONUs
  - Detailed sync statistics

**Usage:**

1. **Manual Sync via Command Line:**
   ```bash
   # Sync all active OLTs
   php artisan olt:sync-onus
   
   # Sync specific OLT
   php artisan olt:sync-onus --olt=1
   
   # Force sync inactive OLT
   php artisan olt:sync-onus --olt=1 --force
   ```

2. **Manual Sync via API:**
   ```bash
   POST /api/v1/olt/{id}/sync-onus
   ```

3. **Automated Sync:**
   - Configured to run hourly via Laravel scheduler
   - Check `routes/console.php` line 19

**OLT Configuration Requirements:**

For SNMP-based discovery:
- `snmp_community`: SNMP community string (e.g., "public")
- `snmp_version`: SNMP version ("v1", "v2c", or "v3")
- `snmp_port`: SNMP port (default: 161)
- `management_protocol`: Set to "snmp" or "both"

For SSH-based discovery:
- `ip_address`: OLT IP address
- `port`: SSH port (default: 22)
- `username`: SSH username
- `password`: SSH password
- `management_protocol`: Set to "ssh" or "both"

### 3. Device Monitoring (✓ WORKING)

**Status:** The monitoring system was already implemented but needed better integration with OLT/ONU services.

**Features:**
- Monitors routers, OLTs, and ONUs
- Collects CPU usage, memory usage, uptime
- Stores monitoring data in `device_monitors` table
- Bandwidth usage tracking with time-series aggregation

**Usage:**

1. **Manual Monitoring via Command Line:**
   ```bash
   # Monitor all devices
   php artisan monitoring:collect
   
   # Monitor specific device type
   php artisan monitoring:collect --type=olt
   php artisan monitoring:collect --type=onu
   
   # Monitor specific device
   php artisan monitoring:collect --type=olt --id=1
   ```

2. **Monitor via API:**
   ```bash
   # Get all device statuses
   GET /api/v1/monitoring/devices
   
   # Get specific device status
   GET /api/v1/monitoring/devices/{type}/{id}/status
   
   # Monitor ONUs on an OLT
   GET /api/v1/olt/{id}/monitor-onus
   ```

3. **Automated Monitoring:**
   - Runs every 5 minutes via Laravel scheduler
   - Data aggregation runs hourly/daily
   - Old data cleanup runs daily

### 4. OLT Backup System (✓ WORKING)

**Status:** The backup system was already implemented and working.

**Features:**
- Creates configuration backups via SSH
- Stores backups in `storage/app/backups/olts/{olt_id}/`
- Tracks backup metadata in `olt_backups` table
- Manual and automatic backup support

**Usage:**

1. **Manual Backup via Command Line:**
   ```bash
   # Backup all active OLTs
   php artisan olt:backup
   
   # Backup specific OLT
   php artisan olt:backup --olt=1
   
   # Force backup inactive OLT
   php artisan olt:backup --olt=1 --force
   ```

2. **Manual Backup via API:**
   ```bash
   POST /api/v1/olt/{id}/backup
   ```

3. **Retrieve Backups:**
   ```bash
   # Get backups for specific OLT
   GET /api/v1/olt/{id}/backups
   
   # Get all backups across all OLTs
   GET /api/v1/olt/backups/all
   ```

4. **Automated Backup:**
   - Runs daily at 2:00 AM via Laravel scheduler
   - Check `routes/console.php` line 20

## OLT Health Check

Monitor OLT connectivity and health status:

```bash
# Check all OLTs
php artisan olt:health-check

# Check specific OLT with details
php artisan olt:health-check --olt=1 --details
```

Automated health checks run every 15 minutes.

## Scheduled Tasks Summary

The following tasks are scheduled in `routes/console.php`:

| Task | Schedule | Description |
|------|----------|-------------|
| `olt:health-check` | Every 15 minutes | Check OLT connectivity and health |
| `olt:sync-onus` | Hourly | Sync ONUs from OLTs |
| `olt:backup` | Daily at 2:00 AM | Backup OLT configurations |
| `monitoring:collect` | Every 5 minutes | Collect device metrics |
| `monitoring:aggregate-hourly` | Hourly | Aggregate monitoring data |
| `monitoring:aggregate-daily` | Daily at 1:00 AM | Create daily summaries |
| `monitoring:cleanup` | Daily at 3:00 AM | Clean up old data (90 days) |

## API Endpoints Summary

### SNMP Trap Endpoints
- `POST /api/v1/snmp-trap/receive` - Receive JSON trap
- `POST /api/v1/snmp-trap/receive-legacy` - Receive snmptrapd format
- `POST /api/v1/snmp-trap/test` - Test endpoint (dev only)

### OLT Management Endpoints
- `GET /api/v1/olt` - List OLTs
- `GET /api/v1/olt/{id}` - Get OLT details
- `POST /api/v1/olt/{id}/test-connection` - Test connection
- `POST /api/v1/olt/{id}/sync-onus` - Sync ONUs
- `GET /api/v1/olt/{id}/statistics` - Get statistics
- `POST /api/v1/olt/{id}/backup` - Create backup
- `GET /api/v1/olt/{id}/backups` - List backups
- `GET /api/v1/olt/{id}/monitor-onus` - Monitor ONUs

### SNMP Trap Management Endpoints
- `GET /api/v1/olt/snmp-traps` - List traps
- `POST /api/v1/olt/snmp-traps/{id}/acknowledge` - Acknowledge trap
- `POST /api/v1/olt/snmp-traps/acknowledge-all` - Acknowledge all

### ONU Operations Endpoints
- `GET /api/v1/olt/onu/{id}` - Get ONU details
- `POST /api/v1/olt/onu/{id}/refresh` - Refresh status
- `POST /api/v1/olt/onu/{id}/authorize` - Authorize ONU
- `POST /api/v1/olt/onu/{id}/unauthorize` - Unauthorize ONU
- `POST /api/v1/olt/onu/{id}/reboot` - Reboot ONU

### Monitoring Endpoints
- `GET /api/v1/monitoring/devices` - All device statuses
- `GET /api/v1/monitoring/devices/{type}/{id}/status` - Device status

## Troubleshooting

### ONU Discovery Not Working

1. **Check OLT Configuration:**
   ```bash
   php artisan tinker
   >>> $olt = App\Models\Olt::find(1);
   >>> $olt->canConnect();  // Should return true
   >>> $olt->snmp_community;  // Check if set
   ```

2. **Test Connection:**
   ```bash
   php artisan olt:health-check --olt=1 --details
   ```

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "onu\|olt"
   ```

4. **Manual Sync with Debug:**
   ```bash
   php artisan olt:sync-onus --olt=1 -vvv
   ```

### SNMP Traps Not Being Received

1. **Test the Endpoint:**
   ```bash
   curl -X POST http://localhost/api/v1/snmp-trap/test \
     -H "Content-Type: application/json" \
     -d '{"source_ip":"192.168.1.100","trap_type":"linkDown"}'
   ```

2. **Check snmptrapd is Running:**
   ```bash
   systemctl status snmptrapd
   ```

3. **Check Trap Handler Script:**
   ```bash
   ls -la /usr/local/bin/forward-trap-to-app.sh
   ```

4. **Check Application Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "trap"
   ```

### Monitoring Not Collecting Data

1. **Check if Scheduler is Running:**
   ```bash
   php artisan schedule:list
   ```

2. **Ensure cron is configured:**
   ```bash
   crontab -l | grep artisan
   # Should see: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Manual Collection:**
   ```bash
   php artisan monitoring:collect --type=olt --id=1
   ```

## Testing

Run the test suite:

```bash
# Run all OLT/ONU tests
php artisan test --filter="Olt|Onu|Monitoring"

# Run specific test
php artisan test --filter=OltServiceTest
```

## Next Steps

1. Configure SNMP trap receiver on your OLT devices
2. Verify OLT connection settings (SNMP/SSH credentials)
3. Run initial ONU sync: `php artisan olt:sync-onus`
4. Monitor logs for any issues
5. Set up cron for automated tasks
6. Configure notifications for critical traps (future enhancement)

## Security Notes

- SNMP trap receiver endpoints are rate-limited but don't require authentication
- Consider IP whitelisting for trap receiver endpoints
- Ensure HTTPS is used in production
- OLT credentials are encrypted in database
- All API endpoints (except trap receiver) require authentication

## Support

For issues or questions:
1. Check logs in `storage/logs/laravel.log`
2. Review this documentation
3. Check existing issues on GitHub
4. Create a new issue with detailed error logs
