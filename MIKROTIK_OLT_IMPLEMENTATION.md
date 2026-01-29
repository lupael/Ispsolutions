# Mikrotik and OLT Module Implementation Summary

## Overview
This implementation addresses the issue #165 requirements for fixing broken Mikrotik and OLT modules by implementing a **NAS-Centric** and **Fail-Safe** architecture.

## 1. Mikrotik: NAS-Centric Integration

### Implemented Features

#### 1.1 RADIUS Health Monitoring (NasNetWatchController)
**Location**: `app/Http/Controllers/Panel/NasNetWatchController.php`

Implements automatic failover using MikroTik Netwatch:
- **RADIUS UP**: Disables local PPP secrets and removes non-RADIUS sessions
- **RADIUS DOWN**: Enables local PPP secrets for fallback authentication

**Routes**:
```php
POST /admin/nas/netwatch/routers/{router}/configure  // Configure netwatch
DELETE /admin/nas/netwatch/routers/{router}          // Remove netwatch
GET /admin/nas/netwatch/routers/{router}/status      // Get netwatch status
```

**Configuration**:
```
Interval: 1m (configurable via RADIUS_NETWATCH_INTERVAL)
Timeout: 1s (configurable via RADIUS_NETWATCH_TIMEOUT)
```

#### 1.2 Automated Provisioning (RouterRadiusProvisioningService)
**Location**: `app/Services/RouterRadiusProvisioningService.php`

Automated provisioning on first connect:
1. **RADIUS Client**: Auto-configures `/radius add service=ppp,hotspot address=[Server_IP]`
2. **PPP AAA**: Auto-sets `/ppp aaa set use-radius=yes accounting=yes`
3. **RADIUS Incoming**: Auto-sets `/radius/incoming/set accept=yes`
4. **Backup**: Creates initial backup `/system backup save`
5. **NAS Table**: Auto-inserts router into RADIUS `nas` database table

**Routes**:
```php
POST /admin/routers/provision/radius          // Provision RADIUS on router
POST /admin/routers/provision/export-secrets  // Export PPP secrets
```

**Integration**: 
- Automatic NAS entry creation via `MikrotikRouterObserver`
- Router-NAS relationship maintained automatically

#### 1.3 API Support
- **Binary API**: Full support for RouterOS v6 and v7 via `evilfreelancer/routeros-api-php`
- **REST API**: Fallback REST API support
- **Auto-detection**: Automatically detects and uses the best available API

## 2. Backup Systems

### 2.1 Router-side PPP Secret Export
**Implementation**: `RouterRadiusProvisioningService::exportPppSecrets()`

Creates timestamped backup files on router:
```php
$filename = "ppp-secret-backup-by-billing-{timestamp}";
$api->ttyWrite('/ppp/secret/export', ['file' => $filename]);
```

### 2.2 Customer Backup/Mirror (CustomerBackupController)
**Location**: `app/Http/Controllers/Panel/CustomerBackupController.php`

Mirrors customer data to router PPP secrets for fallback:
- **Single Backup**: Backup individual customer to router
- **Bulk Backup**: Backup all active customers to router
- **Remove**: Remove customer from router secrets

**Routes**:
```php
POST /admin/customers/backup/{customer}           // Backup single customer
POST /admin/customers/backup/routers/{router}/all // Backup all customers
DELETE /admin/customers/backup/{customer}         // Remove customer
```

**Conditions**: Only runs when `primary_auth !== 'Radius'`

### 2.3 App/Server Backup
Existing Spatie backup integration maintained at `config/backup.php`

## 3. OLT: Full Lifecycle Management

### 3.1 Multi-Vendor SNMP Support (OltSnmpService)
**Location**: `app/Services/OltSnmpService.php`

**Supported Vendors**:
- VSOL (V-SOL)
- Huawei
- ZTE
- BDCOM

**Features**:
- **ONU Discovery**: SNMP OID walks to discover ONUs
- **RX/TX Power**: Real-time optical power monitoring
- **Distance**: Distance monitoring for fiber links
- **Auto-detection**: Automatic vendor detection from brand/model

**OID Mappings**:
```php
'vsol' => [
    'onu_list' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.3',
    'onu_status' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.4',
    'onu_rx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5',
    'onu_tx_power' => '.1.3.6.1.4.1.37950.1.1.5.12.1.25.1.6',
],
// Similar mappings for Huawei, ZTE, BDCOM
```

### 3.2 Enhanced OLT Service
**Location**: `app/Services/OltService.php`

**Improvements**:
- SNMP discovery with SSH fallback
- Real-time ONU status via SNMP
- Backward compatibility maintained
- Vendor-specific command support

**Discovery Flow**:
1. Try SNMP discovery first (if configured)
2. Fall back to SSH if SNMP fails or returns no data
3. Log which method was used for debugging

## 4. Configuration

### 4.1 RADIUS Configuration
**Location**: `config/radius.php`

```php
'server_ip' => env('RADIUS_SERVER_IP', '127.0.0.1'),
'authentication_port' => env('RADIUS_AUTH_PORT', 1812),
'accounting_port' => env('RADIUS_ACCT_PORT', 1813),
'interim_update' => env('RADIUS_INTERIM_UPDATE', '5m'),

'netwatch' => [
    'enabled' => env('RADIUS_NETWATCH_ENABLED', true),
    'interval' => env('RADIUS_NETWATCH_INTERVAL', '1m'),
    'timeout' => env('RADIUS_NETWATCH_TIMEOUT', '1s'),
],
```

### 4.2 Mikrotik Configuration
**Location**: `config/services.php` (mikrotik section)

```php
'mikrotik' => [
    'timeout' => env('MIKROTIK_TIMEOUT', 30),
    'max_retries' => env('MIKROTIK_MAX_RETRIES', 3),
    'retry_delay' => env('MIKROTIK_RETRY_DELAY', 1000),
],
```

## 5. Testing

### Test Results
```
✓ MikrotikServiceTest - 8 tests passed
  - connect router successfully
  - connect nonexistent router fails
  - connect router with connection error
  - create pppoe user successfully
  - update pppoe user successfully
  - delete pppoe user successfully
  - get active sessions
  - disconnect session

✓ NewControllersTest - 2 tests passed
  - nas netwatch controller can be instantiated
  - customer backup controller can be instantiated
```

### Linting
All code passes Laravel Pint standards.

## 6. Security Considerations

### Implemented Security Measures
1. **Password Encryption**: All passwords and secrets encrypted at rest
2. **Sanitization**: Sensitive fields sanitized before logging
3. **SSRF Protection**: Router IP validation prevents SSRF attacks
4. **Authentication**: All routes require authentication
5. **Tenant Isolation**: All operations tenant-scoped

### Recommendations
1. Use HTTPS for production router connections
2. Enable certificate validation for REST API
3. Use strong RADIUS secrets (min 16 characters)
4. Regular backup of configuration
5. Monitor netwatch logs for failover events

## 7. Migration Path

### For Existing Installations
1. **Update Configuration**: Add netwatch settings to `.env`
2. **Configure RADIUS**: Ensure RADIUS server details are correct
3. **Run Provisioning**: Use `/admin/routers/provision/radius` endpoint
4. **Enable Netwatch**: Use `/admin/nas/netwatch/routers/{router}/configure`
5. **Backup Customers**: Use `/admin/customers/backup/routers/{router}/all`

### For New Installations
1. Create router with NAS association
2. Observer automatically creates NAS entry
3. Use provisioning endpoints to configure RADIUS
4. Netwatch configures automatically on first connect

## 8. Usage Examples

### Example 1: Configure RADIUS with Netwatch
```bash
# Step 1: Provision RADIUS on router
POST /admin/routers/provision/radius
{
    "router_id": 1
}

# Step 2: Configure netwatch
POST /admin/nas/netwatch/routers/1/configure

# Step 3: Export existing secrets as backup
POST /admin/routers/provision/export-secrets
{
    "router_id": 1
}
```

### Example 2: Backup All Customers
```bash
# Backup all active customers to router
POST /admin/customers/backup/routers/1/all
```

### Example 3: Discover ONUs via SNMP
```php
$oltService = app(OltService::class);
$onus = $oltService->discoverOnus($oltId); // Automatically tries SNMP first

$onuService = app(OltSnmpService::class);
$powerLevels = $onuService->getOnuOpticalPower($onu); // Get RX/TX power
```

## 9. API Endpoints Summary

### NAS Netwatch
- `POST /admin/nas/netwatch/routers/{router}/configure`
- `DELETE /admin/nas/netwatch/routers/{router}`
- `GET /admin/nas/netwatch/routers/{router}/status`

### Router Provisioning
- `POST /admin/routers/provision/radius`
- `POST /admin/routers/provision/export-secrets`

### Customer Backup
- `POST /admin/customers/backup/{customer}`
- `POST /admin/customers/backup/routers/{router}/all`
- `DELETE /admin/customers/backup/{customer}`

## 10. Future Enhancements

### Planned (Not Implemented)
1. **UI Drill-Down Details**:
   - Router detail pages with CPU/RAM/Uptime
   - Active sessions display
   - ONU list with signal strength indicators

2. **Auto-Backup**:
   - Scheduled OLT backups via TFTP/FTP
   - Automated backup rotation

3. **Enhanced Monitoring**:
   - Real-time dashboard for router/OLT health
   - Alert system for failover events

## 11. Documentation References

- MikroTik API Documentation: https://wiki.mikrotik.com/wiki/Manual:API
- RADIUS Protocol: RFC 2865, RFC 2866
- SNMP Protocol: RFC 1157
- evilfreelancer/routeros-api-php: https://github.com/EvilFreelancer/routeros-api-php

## 12. Support and Troubleshooting

### Common Issues

#### Issue: Netwatch not working
**Solution**: 
- Verify RADIUS server is accessible from router
- Check netwatch interval/timeout settings
- Verify scripts don't have syntax errors

#### Issue: SNMP discovery returns no ONUs
**Solution**:
- Verify SNMP community string is correct
- Check SNMP version (v1/v2c/v3)
- Ensure OLT brand is correctly set for vendor detection
- Fall back to SSH-based discovery

#### Issue: Customer backup fails
**Solution**:
- Ensure `primary_auth` is not set to 'Radius'
- Verify router connectivity
- Check customer has valid username and password

### Logs
All operations are logged with appropriate context:
```
Log::info('Successfully provisioned router for RADIUS', [
    'router_id' => $router->id,
    'steps' => $results,
]);
```

## 13. Version History

- **v1.0.0** (2026-01-29): Initial implementation
  - NAS netwatch controller
  - RADIUS provisioning service
  - Customer backup controller
  - OLT SNMP multi-vendor support
  - Tests and linting
