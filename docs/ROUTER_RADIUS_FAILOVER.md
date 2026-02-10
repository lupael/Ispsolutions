# Router RADIUS Failover Guide

## Overview

This guide explains how to configure and manage automatic failover between RADIUS and local router authentication in the ISP Solution system. Failover ensures that customer connections remain operational even when the RADIUS server becomes unavailable.

## Table of Contents

- [Introduction](#introduction)
- [Architecture](#architecture)
- [Configuration](#configuration)
- [Failover Modes](#failover-modes)
- [Netwatch Setup](#netwatch-setup)
- [Manual Mode Switching](#manual-mode-switching)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## Introduction

The RADIUS failover system provides three authentication modes:

1. **RADIUS Mode**: All authentication goes through RADIUS server
2. **Router Mode**: All authentication uses local PPP secrets
3. **Hybrid Mode**: Automatic failover between RADIUS and Router modes

### Benefits

- **High Availability**: Customers can connect even when RADIUS is down
- **Zero Downtime**: Automatic switching without manual intervention
- **Flexibility**: Manual control when needed
- **Monitoring**: Real-time status of authentication mode

## Architecture

```
┌─────────────┐
│   Customer  │
└──────┬──────┘
       │ PPPoE Connection
       ▼
┌─────────────────────────────────────────────┐
│          MikroTik Router                     │
│  ┌─────────────────────────────────────┐   │
│  │  PPP AAA Settings                    │   │
│  │  - use-radius: yes/no (dynamic)      │   │
│  │  - accounting: yes/no (dynamic)      │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │  Netwatch Monitor                    │   │
│  │  - Monitors: RADIUS Server IP        │   │
│  │  - Interval: 1m (configurable)       │   │
│  │  - Up Script: Switch to RADIUS       │   │
│  │  - Down Script: Switch to Router     │   │
│  └─────────────────────────────────────┘   │
└──────────────┬──────────────────────────────┘
               │
               ▼
        ┌──────────────┐
        │ RADIUS Server │
        └──────────────┘
```

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```bash
# RADIUS Server Configuration
RADIUS_SERVER_IP=127.0.0.1
RADIUS_AUTH_PORT=1812
RADIUS_ACCT_PORT=1813
RADIUS_PRIMARY_AUTH=hybrid

# Netwatch Configuration
RADIUS_NETWATCH_ENABLED=true
RADIUS_NETWATCH_INTERVAL=1m
RADIUS_NETWATCH_TIMEOUT=1s
```

### 2. Router Configuration

When creating or editing a router, configure the following:

#### Via Web UI

1. Navigate to **Network → Routers → Create/Edit**
2. Fill in the RADIUS Configuration section:
   - **RADIUS Shared Secret**: Must match the NAS device secret
   - **Public IP Address**: Router's public IP (for RADIUS NAS identification)
   - **RADIUS Server IP**: From config (readonly)
   - **Primary Authentication Mode**: Select `hybrid` for automatic failover

#### Via API

```php
$router = MikrotikRouter::create([
    'name' => 'Main Router',
    'host' => '192.168.1.1',
    'username' => 'admin',
    'password' => 'password',
    'radius_secret' => 'shared_secret_123',
    'public_ip' => '203.0.113.1',
    'primary_auth' => 'hybrid', // or 'radius' or 'router'
    'tenant_id' => $tenantId,
]);
```

### 3. Configure Failover

#### Via Web UI

1. Navigate to **Network → Routers**
2. Click on a router
3. Go to **Failover** tab
4. Click **Configure Failover**
5. System will automatically:
   - Configure PPP AAA settings for RADIUS
   - Create Netwatch monitor
   - Set up failover scripts

#### Via Console Command

```bash
php artisan router:failover {router_id}
```

#### Via API

```bash
POST /api/routers/failover/{router_id}/configure
```

## Failover Modes

### 1. RADIUS Mode

All authentication requests go to RADIUS server.

**When to use:**
- RADIUS server is healthy and available
- You want centralized authentication
- You need accounting data

**Configuration:**
```php
// Set router to RADIUS mode
$failoverService->switchToRadiusMode($router);
```

**Router Settings:**
- `use-radius=yes`
- `accounting=yes`

### 2. Router Mode

All authentication uses local PPP secrets on the router.

**When to use:**
- RADIUS server is down or unreachable
- Emergency/maintenance mode
- Testing local authentication

**Configuration:**
```php
// Set router to Router mode
$failoverService->switchToRouterMode($router);
```

**Router Settings:**
- `use-radius=no`
- `accounting=no`

### 3. Hybrid Mode (Automatic Failover)

System automatically switches between RADIUS and Router modes based on RADIUS health.

**When to use:**
- Production environments (recommended)
- Maximum availability required
- No manual intervention desired

**Configuration:**
```php
// Enable hybrid mode
$router->update(['primary_auth' => 'hybrid']);
$failoverService->configureFailover($router);
```

**How it works:**
1. Netwatch monitors RADIUS server every 1 minute
2. If RADIUS responds: Switch to RADIUS mode
3. If RADIUS fails: Switch to Router mode
4. Events are logged for monitoring

## Netwatch Setup

Netwatch is MikroTik's built-in monitoring tool that checks host availability.

### Automatic Setup

When you configure failover, the system automatically creates:

```routeros
/tool netwatch
add host=<RADIUS_SERVER_IP> \
    interval=1m \
    timeout=1s \
    up-script="/ppp aaa set use-radius=yes accounting=yes" \
    down-script="/ppp aaa set use-radius=no accounting=no" \
    comment="ISP Solution RADIUS Failover"
```

### Manual Setup

If you need to manually configure Netwatch:

1. Connect to router via Winbox or SSH
2. Go to **Tools → Netwatch**
3. Add new entry:
   - **Host**: Your RADIUS server IP
   - **Interval**: 1m (check every minute)
   - **Timeout**: 1s (wait 1 second for response)
   - **Up Script**: `/ppp aaa set use-radius=yes accounting=yes`
   - **Down Script**: `/ppp aaa set use-radius=no accounting=no`

### Verifying Netwatch

```bash
# Check Netwatch status
/tool netwatch print detail

# Check if RADIUS is up
/tool netwatch print where host=<RADIUS_IP>
```

## Manual Mode Switching

Sometimes you need to manually control the authentication mode.

### Via Web UI

1. Navigate to **Network → Routers**
2. Click on a router
3. Go to **Failover** tab
4. Click one of:
   - **Switch to RADIUS Mode**
   - **Switch to Router Mode**

### Via API

```bash
# Switch to RADIUS mode
POST /api/routers/failover/{router_id}/switch-to-radius

# Switch to Router mode
POST /api/routers/failover/{router_id}/switch-to-router
```

### Via Console Command

```bash
# Check current status
php artisan router:failover {router_id} --status

# Switch to RADIUS
php artisan router:failover {router_id} --mode=radius

# Switch to Router
php artisan router:failover {router_id} --mode=router
```

### Via RouterOS CLI

```routeros
# Check current AAA settings
/ppp aaa print

# Switch to RADIUS
/ppp aaa set use-radius=yes accounting=yes

# Switch to Router
/ppp aaa set use-radius=no accounting=no
```

## Monitoring

### Real-Time Status

View failover status in the router dashboard:

1. Navigate to **Network → Routers**
2. Click on a router
3. The dashboard shows:
   - **Current Mode**: RADIUS/Router/Hybrid
   - **RADIUS Health**: Up/Down with last check time
   - **Netwatch Status**: Configured/Not configured
   - **Last Failover Event**: Timestamp and reason

### API Endpoint

```bash
GET /api/routers/failover/{router_id}/status

Response:
{
  "success": true,
  "status": {
    "current_mode": "radius",
    "radius_health": {
      "status": "up",
      "last_check": "2026-01-26 21:30:00",
      "response_time": "15ms"
    },
    "netwatch_configured": true,
    "last_failover": {
      "timestamp": "2026-01-26 20:15:00",
      "from_mode": "router",
      "to_mode": "radius",
      "reason": "RADIUS server recovered"
    }
  }
}
```

### Events

The system fires events when failover occurs:

```php
// Listen to failover events
Event::listen(FailoverTriggered::class, function ($event) {
    Log::info('Failover triggered', [
        'router' => $event->router->name,
        'from_mode' => $event->fromMode,
        'to_mode' => $event->toMode,
        'reason' => $event->reason,
    ]);
});
```

### Logs

Check logs for failover activity:

```bash
# Application logs
tail -f storage/logs/laravel.log | grep -i failover

# Router provisioning logs
tail -f storage/logs/router-provisioning.log
```

## Troubleshooting

### Issue: Netwatch Not Switching Modes

**Symptoms:**
- RADIUS is down but router still tries to use RADIUS
- Netwatch shows as "down" but mode doesn't change

**Solutions:**

1. Check Netwatch configuration:
   ```routeros
   /tool netwatch print detail
   ```

2. Verify scripts are correct:
   ```routeros
   /tool netwatch export
   ```

3. Test scripts manually:
   ```routeros
   /ppp aaa set use-radius=no accounting=no
   /ppp aaa print
   ```

4. Reconfigure failover from ISP Solution:
   ```bash
   php artisan router:failover {router_id} --reconfigure
   ```

### Issue: Users Can't Connect After Failover

**Symptoms:**
- Failover occurs but users still can't authenticate
- "Authentication failed" errors

**Solutions:**

1. **Check PPP Secrets Exist:**
   ```routeros
   /ppp secret print
   ```
   
   If missing, mirror users from ISP Solution:
   ```bash
   php artisan router:mirror-users {router_id}
   ```

2. **Verify Profiles Exist:**
   ```routeros
   /ppp profile print
   ```
   
   If missing, provision users again:
   ```bash
   php artisan router:provision-all {router_id}
   ```

3. **Check User Passwords:**
   - Ensure local passwords match what's in ISP Solution
   - Update passwords if needed

### Issue: RADIUS Server Shows as Down (False Positive)

**Symptoms:**
- RADIUS is running but Netwatch marks it as down
- Timeout errors in logs

**Solutions:**

1. **Increase Timeout:**
   ```routeros
   /tool netwatch set [find host=<RADIUS_IP>] timeout=2s
   ```

2. **Check Network Path:**
   ```routeros
   /ping <RADIUS_IP> count=10
   ```

3. **Verify Firewall Rules:**
   - Ensure ICMP is allowed
   - Check if there's a firewall blocking ping

4. **Use Different Test:**
   Instead of ping, test RADIUS port:
   ```routeros
   /tool fetch url="http://<RADIUS_IP>:1812" mode=tcp check-certificate=no
   ```

### Issue: Constant Flapping Between Modes

**Symptoms:**
- Mode switches back and forth frequently
- "Netwatch unstable" warnings

**Solutions:**

1. **Increase Check Interval:**
   ```routeros
   /tool netwatch set [find host=<RADIUS_IP>] interval=5m
   ```

2. **Add Stability Check:**
   Require multiple failures before switching:
   - Edit Netwatch down-script to include counter
   - Only switch after 3+ consecutive failures

3. **Check Network Stability:**
   - Investigate packet loss between router and RADIUS
   - Check if RADIUS server is overloaded

### Issue: Accounting Data Lost During Failover

**Symptoms:**
- Missing accounting records when in Router mode
- Gaps in usage data

**Solutions:**

This is **expected behavior**:
- Router mode disables RADIUS accounting
- Only RADIUS mode sends accounting data

**Mitigation:**
1. Minimize time in Router mode
2. Use hybrid mode for automatic recovery
3. Run reports acknowledging potential gaps
4. Consider local logging on router:
   ```routeros
   /ip accounting set enabled=yes threshold=10000
   ```

### Issue: Manual Switch Doesn't Persist

**Symptoms:**
- Manually switch to Router mode
- Netwatch switches back to RADIUS immediately

**Solutions:**

1. **Disable Netwatch Temporarily:**
   ```routeros
   /tool netwatch set [find host=<RADIUS_IP>] disabled=yes
   ```

2. **Or Remove Netwatch:**
   ```bash
   # Via ISP Solution
   POST /api/routers/failover/{router_id}/disable-netwatch
   ```

3. **Set Router to "Router Mode" Permanently:**
   ```php
   $router->update(['primary_auth' => 'router']);
   ```

## Best Practices

### 1. Regular Testing

Test failover monthly:

```bash
# 1. Note current mode
# 2. Temporarily stop RADIUS service
sudo systemctl stop freeradius

# 3. Wait 2 minutes
# 4. Verify router switched to Router mode
# 5. Test customer connection

# 6. Start RADIUS
sudo systemctl start freeradius

# 7. Wait 2 minutes
# 8. Verify router switched back to RADIUS mode
```

### 2. Monitor Events

Set up alerts for failover events:

```php
// In EventServiceProvider
Event::listen(FailoverTriggered::class, SendFailoverAlert::class);
```

### 3. Keep Secrets in Sync

Ensure local PPP secrets are always up to date:

```bash
# Daily cron job
0 2 * * * php artisan router:mirror-users --all
```

### 4. Document Custom Settings

If you customize Netwatch intervals or timeout values, document them:

```
Router: Main-Router-01
Netwatch Interval: 5m (custom - high latency link)
Netwatch Timeout: 3s (custom - slow RADIUS response)
Reason: Long distance WAN link with 200ms latency
```

### 5. Use Hybrid Mode in Production

Always use `hybrid` mode for production routers:
- Automatic failover with zero manual intervention
- Maximum uptime for customers
- Automatic recovery when RADIUS comes back

## Related Documentation

- [ROUTER_PROVISIONING_GUIDE.md](ROUTER_PROVISIONING_GUIDE.md) - User provisioning
- [RADIUS_SETUP_GUIDE.md](RADIUS_SETUP_GUIDE.md) - RADIUS server setup
- [ROUTER_BACKUP_RESTORE.md](ROUTER_BACKUP_RESTORE.md) - Backup strategies

## Support

If you encounter issues not covered in this guide:

1. Check application logs: `storage/logs/laravel.log`
2. Check router logs: `/log print` in RouterOS
3. Review recent failover events in the database
4. Contact support with router ID and timestamp of issue
