# Router + RADIUS Configuration User Guide

**Based on:** IspBills ISP Billing System Patterns  
**Date:** 2026-01-30  
**Version:** 1.0

---

## Overview

This guide explains how to configure and use Router + RADIUS features in ISP Solution, following best practices from the IspBills system. These features enable centralized authentication, automated provisioning, and comprehensive customer management across MikroTik routers.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Adding a Router (NAS)](#adding-a-router-nas)
3. [Configuring RADIUS](#configuring-radius)
4. [Importing from Router](#importing-from-router)
5. [User Provisioning](#user-provisioning)
6. [Customer Comments](#customer-comments)
7. [Backup & Recovery](#backup--recovery)
8. [Failover Configuration](#failover-configuration)
9. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before configuring Router + RADIUS integration:

1. **MikroTik Router Requirements:**
   - RouterOS 6.x or 7.x
   - API service enabled (`/ip service set api enabled=yes`)
   - API credentials created with admin privileges
   - Network connectivity to billing server

2. **RADIUS Server Requirements:**
   - FreeRADIUS installed and running
   - Database connection configured
   - Shared secret for router authentication

3. **ISP Solution Requirements:**
   - Admin or Super Admin role
   - Network configuration permissions
   - RADIUS configuration values in `.env`

---

## Adding a Router (NAS)

### Step 1: Navigate to Router Management

1. Go to **Admin Panel** → **Network** → **Routers**
2. Click **Add New Router** button

### Step 2: Configure Router Details

Fill in the router information:

**Basic Information:**
- **Name:** Friendly name (e.g., "Main Gateway")
- **IP Address:** Router's IP address
- **Public IP:** External IP for RADIUS callback (if different)
- **API Port:** Default 8728 (or 8729 for SSL)
- **API Type:** binary or http

**Credentials:**
- **Username:** API username (must have admin privileges)
- **Password:** API password (encrypted in database)

**RADIUS Configuration:**
- **NAS Name:** Unique identifier for RADIUS
- **RADIUS Secret:** Shared secret for RADIUS authentication
- **Primary Auth:** Choose "Router" or "RADIUS"

### Step 3: Test Connection

Click **Test Connection** to verify:
- API connectivity
- Credentials validity
- Router response time

If successful, click **Save** to add the router.

---

## Configuring RADIUS

### Option 1: One-Click Configuration (Recommended)

For new routers, use the automated setup:

1. Navigate to **Network** → **Routers** → **Configure**
2. Select your router
3. Click **Configure RADIUS** button
4. System will automatically:
   - Add RADIUS client configuration
   - Enable PPP AAA with RADIUS
   - Configure RADIUS incoming
   - Setup netwatch for failover

### Option 2: Manual Configuration

#### Configure RADIUS Client

This adds your RADIUS server to the router:

```
Settings Applied:
- Service: ppp, hotspot
- Address: [Your RADIUS server IP]
- Auth Port: 1812
- Acct Port: 1813
- Secret: [Shared secret from NAS config]
- Timeout: 3s
```

#### Enable PPP AAA

Configures PPP to use RADIUS:

```
Settings Applied:
- use-radius: yes
- accounting: yes
- interim-update: 5m
```

#### Enable RADIUS Incoming

Allows dynamic client addition:

```
Settings Applied:
- accept: yes
```

### Verify Configuration

After configuration, check status:

1. Go to **Network** → **Routers** → **RADIUS Status**
2. Verify:
   - ✅ RADIUS Server: Connected
   - ✅ AAA Enabled: Yes
   - ✅ Accounting: Enabled
   - ✅ Interim Updates: 5m

---

## Importing from Router

Import existing configurations from your MikroTik router into ISP Solution.

### Import IP Pools

1. Navigate to **Network** → **Routers** → **Import**
2. Select **IP Pools** tab
3. Select target router
4. Click **Import Pools**
5. Review imported pools in **IP Management**

**What Gets Imported:**
- Pool names
- IP ranges
- Available/assigned status

### Import PPP Profiles

1. Go to **Import** → **PPP Profiles**
2. Select router
3. Click **Import Profiles**
4. Review in **Profiles** section

**What Gets Imported:**
- Profile names
- Local address
- Remote address
- Rate limits
- Pool assignments

### Import PPP Secrets (Customers)

⚠️ **Caution:** This imports users from router to billing system

1. Go to **Import** → **PPP Secrets**
2. Select router
3. **Options:**
   - ☑️ Filter Disabled Users (recommended)
   - ☐ Generate Bills (optional)
4. Click **Import Secrets**

**What Gets Imported:**
- Usernames
- Passwords (encrypted)
- Profile assignments
- Static IPs (if configured)
- Comments (customer metadata)

**Automatic Backup:** System creates a router-side export before import:
- File: `ppp-secret-backup-by-billing-[timestamp]`
- Location: Router's file system
- Available for manual restoration if needed

---

## User Provisioning

### Automatic Provisioning

When you create a customer in ISP Solution:

1. **RADIUS Mode:**
   - User created in `radcheck` table
   - Password stored in RADIUS database
   - Router authenticates via RADIUS
   - No local `/ppp/secret` created

2. **Router Mode:**
   - User created in `/ppp/secret` on router
   - Password synced to router
   - Local authentication
   - Backup maintained in RADIUS

3. **Hybrid Mode:**
   - User in both RADIUS and router
   - RADIUS primary, router fallback
   - Automatic failover via netwatch

### Manual Provisioning

To provision existing customer:

1. Go to **Customers** → **[Customer Name]** → **Actions**
2. Click **Provision to Router**
3. Select target router
4. System creates/updates PPP secret with customer data

### Static IP Assignment

For customers with static IPs:

1. Assign static IP in customer profile
2. On provisioning, system sets `remote-address` in PPP secret
3. Customer always gets assigned IP

---

## Customer Comments

ISP Solution embeds customer metadata into router objects for easy troubleshooting.

### Comment Format

Comments use IspBills pattern: `key--value,key--value,...`

**Example:**
```
uid--123,name--John Doe,mobile--01712345678,zone--5,pkg--10,exp--2026-12-31,status--active
```

**Fields Included:**
- `uid`: User/Customer ID
- `name`: Customer name
- `mobile`: Contact number
- `zone`: Zone ID
- `pkg`: Package ID
- `exp`: Expiry date
- `status`: Account status

### Viewing Comments

**From Router (WinBox/WebFig):**
1. Go to `/PPP` → `Secrets`
2. View `Comment` column
3. All customer info visible at a glance

**From ISP Solution:**
1. Customer details page shows all metadata
2. Hover over router status for comment preview

### Benefits

✅ **Quick Troubleshooting:** Identify customer without database lookup  
✅ **Router-Side Visibility:** See customer details in router interface  
✅ **Audit Trail:** Comments preserved in backups  
✅ **Offline Reference:** Available even if billing system is down

---

## Backup & Recovery

### Automatic Backups

System automatically backs up:

**Before Import Operations:**
- Router-side export via `/ppp/secret/export`
- Database snapshot of existing data
- Timestamp for version control

**Before Configuration Changes:**
- Current router configuration
- Stored in `router_configuration_backups` table
- Includes all RADIUS and PPP settings

**Scheduled Backups:**
- Daily automatic backups (configurable)
- Retention policy: 30 days default
- Storage: Database + file system

### Manual Backup

To create manual backup:

1. Navigate to **Network** → **Routers** → **Backups**
2. Select router
3. Click **Create Backup**
4. Add description (optional)
5. Backup saved with timestamp

### Restore from Backup

To restore configuration:

1. Go to **Routers** → **Backups**
2. Select backup to restore
3. Review backup details
4. Click **Restore**
5. Confirm restoration

⚠️ **Warning:** Restoration overwrites current configuration

### Export Backups

Download backups for external storage:

1. Select backup
2. Click **Download**
3. Format: JSON with full configuration
4. Can be re-imported later

---

## Failover Configuration

### Netwatch-Based Automatic Failover

System monitors RADIUS server health and automatically switches authentication mode.

#### How It Works

```
┌─────────────────────────────────────────┐
│     Netwatch monitors RADIUS server     │
│         Ping every 1 minute             │
└───────────┬─────────────────────┬───────┘
            │                     │
    RADIUS UP ✅            RADIUS DOWN ❌
            │                     │
            ▼                     ▼
   Use RADIUS Auth         Use Router Auth
   Disable local secrets   Enable local secrets
   Kill non-RADIUS sessions  Allow local sessions
```

#### Configuration

Failover is configured automatically with RADIUS setup. Manual configuration:

1. Go to **Network** → **Routers** → **Failover**
2. Select router
3. Click **Configure Netwatch**
4. Settings:
   - **Monitor Host:** RADIUS server IP
   - **Interval:** 1m
   - **Timeout:** 1s
   - **Up Script:** `/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];`
   - **Down Script:** `/ppp secret enable [find disabled=yes];`

#### Testing Failover

To test failover manually:

1. Stop RADIUS service temporarily
2. Wait 1-2 minutes
3. Check router: `/ppp/secret/print` (should show enabled)
4. Try user login (should work with local secret)
5. Restart RADIUS
6. Wait 1-2 minutes
7. Check router: `/ppp/secret/print` (should show disabled)
8. RADIUS authentication active again

---

## Troubleshooting

### Connection Issues

**Problem:** Cannot connect to router

**Solutions:**
1. Verify API service is enabled: `/ip service print`
2. Check API port: Default 8728 (binary) or 8729 (SSL)
3. Verify firewall allows connection
4. Test credentials in WinBox/WebFig
5. Check router CPU/memory usage

### RADIUS Issues

**Problem:** RADIUS authentication failing

**Solutions:**
1. Check RADIUS server status
2. Verify shared secret matches
3. Check firewall rules (ports 1812, 1813)
4. Review `/log print where topics~"radius"`
5. Check `/ppp/aaa/print` settings

**Problem:** Users can't authenticate

**Check:**
- User exists in `radcheck` table
- Password is correct
- `radreply` attributes configured
- Router can reach RADIUS server
- `/radius/monitor` shows connection

### Import Issues

**Problem:** Import failed or incomplete

**Solutions:**
1. Check router connectivity
2. Verify API permissions
3. Review import logs: **Logs** → **Router Logs**
4. Check for duplicates in database
5. Restore from backup if needed

### Provisioning Issues

**Problem:** User not provisioned to router

**Check:**
1. Router status (online/offline)
2. API connectivity
3. Profile exists on router
4. IP pool has available IPs
5. Check provisioning logs

---

## Best Practices

### 1. Always Test Before Production

- Test RADIUS setup on development router first
- Verify failover works as expected
- Test user authentication in all modes

### 2. Regular Backups

- Schedule daily automatic backups
- Keep at least 30 days of backups
- Export critical backups to external storage

### 3. Use Descriptive Names

- Router names: "Main-Gateway", "Branch-Router-1"
- Profile names: "10Mbps-Residential", "Business-50M"
- Pool names: "Residential-Pool", "Static-IPs"

### 4. Monitor Regularly

- Check RADIUS monitoring dashboard daily
- Review provisioning logs for errors
- Monitor router health and performance

### 5. Document Changes

- Add notes when creating manual backups
- Document custom configurations
- Keep network topology diagram updated

---

## Quick Reference Commands

### Router (MikroTik) Commands

```bash
# Check RADIUS configuration
/radius print

# Check PPP AAA settings
/ppp/aaa print

# Check active PPPoE sessions
/ppp active print

# View RADIUS logs
/log print where topics~"radius"

# Check netwatch configuration
/tool/netwatch print

# Manual export of PPP secrets
/ppp/secret export file=manual-backup
```

### ISP Solution CLI Commands

```bash
# Import IP pools from router
php artisan mikrotik:import-pools {router_id}

# Import PPP profiles
php artisan mikrotik:import-profiles {router_id}

# Import PPP secrets
php artisan mikrotik:import-secrets {router_id}

# Sync all routers
php artisan mikrotik:sync-all

# Create router backup
php artisan router:backup {router_id}

# Test router connectivity
php artisan router:test-connection {router_id}
```

---

## Support & Resources

- **Documentation:** Full guide at `/docs/router-radius`
- **API Reference:** `/docs/api/radius`
- **Troubleshooting:** `/docs/troubleshooting/router`
- **Community Forum:** Support portal
- **GitHub Issues:** Report bugs and feature requests

---

## Changelog

**Version 1.0 (2026-01-30)**
- Initial release
- IspBills pattern implementation
- One-click RADIUS configuration
- Enhanced comment system
- Automatic failover
- Comprehensive import/export

---

## Credits

Implementation based on patterns from **IspBills ISP Billing System** with enhancements for ISP Solution platform.
