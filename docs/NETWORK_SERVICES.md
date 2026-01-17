# Network Services Documentation

**Version:** 1.0  
**Last Updated:** 2026-01-17  
**Status:** Production Ready

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [IPAM Service](#ipam-service)
4. [RADIUS Service](#radius-service)
5. [MikroTik Service](#mikrotik-service)
6. [Integration Guide](#integration-guide)
7. [Configuration](#configuration)
8. [Troubleshooting](#troubleshooting)

---

## Overview

The ISP Solution implements three core network services to provide comprehensive network management capabilities:

- **IPAM (IP Address Management)**: Manages IP pools, subnets, and allocations with concurrency-safe operations
- **RADIUS**: Handles authentication, accounting, and user synchronization with FreeRADIUS
- **MikroTik**: Manages PPPoE users, sessions, and router configurations via RouterOS API

### Key Features

- **Concurrency-Safe Operations**: Database transactions and row-level locking prevent conflicts
- **Separate RADIUS Database**: Dedicated database connection for RADIUS operations
- **Real-time Session Monitoring**: Track active sessions and bandwidth usage
- **Automated Synchronization**: Scheduled tasks keep systems in sync
- **Comprehensive API**: RESTful API v1 for all network operations

---

## Architecture

### Service-Oriented Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Application Layer                       │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │ Web UI      │  │ REST API v1  │  │ Artisan Commands  │  │
│  └──────┬──────┘  └──────┬───────┘  └─────────┬─────────┘  │
└─────────┼─────────────────┼────────────────────┼────────────┘
          │                 │                    │
┌─────────┼─────────────────┼────────────────────┼────────────┐
│         │    Service Layer (Contracts)         │            │
│  ┌──────▼─────────┐  ┌───▼──────────┐  ┌─────▼──────────┐ │
│  │ IpamService    │  │ RadiusService│  │ MikrotikService│ │
│  │ Interface      │  │ Interface    │  │ Interface      │ │
│  └────────────────┘  └──────────────┘  └────────────────┘ │
└─────────┬──────────────────┬─────────────────┬─────────────┘
          │                  │                 │
┌─────────┼──────────────────┼─────────────────┼─────────────┐
│         │    Data Layer    │                 │             │
│  ┌──────▼─────────┐  ┌─────▼────────┐  ┌────▼───────────┐ │
│  │ Main Database  │  │ RADIUS DB    │  │ RouterOS API   │ │
│  │ - IP Pools     │  │ - radcheck   │  │ - PPPoE Secrets│ │
│  │ - Subnets      │  │ - radreply   │  │ - Active       │ │
│  │ - Allocations  │  │ - radacct    │  │   Sessions     │ │
│  └────────────────┘  └──────────────┘  └────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Database Connections

The application uses two separate database connections:

1. **Main Database** (`mysql`): Application data, IP management, users
2. **RADIUS Database** (`radius`): RADIUS authentication and accounting data

---

## IPAM Service

### Overview

The IP Address Management (IPAM) service handles IP pool creation, subnet management, and IP allocation with concurrency-safe operations.

### Features

- **IP Pool Management**: Create and manage pools of IP addresses
- **Subnet Management**: Define subnets within pools with CIDR notation
- **IP Allocation**: Allocate IPs to users with automatic conflict prevention
- **Utilization Tracking**: Monitor pool and subnet utilization
- **History Tracking**: Complete audit trail of all allocations and releases

### Usage Examples

#### Allocate an IP Address

```php
use App\Services\IpamService;

$ipamService = app(IpamService::class);

// Allocate IP from subnet
$allocation = $ipamService->allocateIP(
    subnetId: 1,
    macAddress: '00:11:22:33:44:55',
    username: 'customer123'
);

if ($allocation) {
    echo "Allocated IP: {$allocation->ip_address}";
} else {
    echo "No available IPs in subnet";
}
```

#### Release an IP Address

```php
// Release IP allocation
$success = $ipamService->releaseIP(allocationId: $allocation->id);

if ($success) {
    echo "IP released successfully";
}
```

#### Check Pool Utilization

```php
$utilization = $ipamService->getPoolUtilization(poolId: 1);

echo "Total IPs: {$utilization['total']}\n";
echo "Allocated: {$utilization['allocated']}\n";
echo "Available: {$utilization['available']}\n";
echo "Utilization: {$utilization['utilization_percent']}%\n";
```

### API Endpoints

```http
GET    /api/v1/ipam/pools                  # List IP pools
POST   /api/v1/ipam/pools                  # Create IP pool
GET    /api/v1/ipam/pools/{id}             # Get pool details
PUT    /api/v1/ipam/pools/{id}             # Update pool
DELETE /api/v1/ipam/pools/{id}             # Delete pool

GET    /api/v1/ipam/subnets                # List subnets
POST   /api/v1/ipam/subnets                # Create subnet
GET    /api/v1/ipam/subnets/{id}           # Get subnet details
PUT    /api/v1/ipam/subnets/{id}           # Update subnet
DELETE /api/v1/ipam/subnets/{id}           # Delete subnet

GET    /api/v1/ipam/allocations            # List allocations
POST   /api/v1/ipam/allocations            # Allocate IP
DELETE /api/v1/ipam/allocations/{id}       # Release IP

GET    /api/v1/ipam/pools/{id}/utilization # Get pool utilization
GET    /api/v1/ipam/subnets/{id}/available-ips # Get available IPs
```

### Concurrency Handling

The IPAM service uses database transactions and row-level locking to prevent concurrent allocation conflicts:

```php
// Transaction ensures atomicity
return DB::transaction(function () use ($subnetId, $macAddress, $username) {
    // Lock subnet row to prevent concurrent allocations
    $subnet = IpSubnet::where('id', $subnetId)
        ->lockForUpdate()
        ->first();
    
    // Find and allocate first available IP
    $availableIP = $this->findFirstAvailableIP($subnet);
    
    if ($availableIP) {
        return IpAllocation::create([...]);
    }
    
    return null;
});
```

---

## RADIUS Service

### Overview

The RADIUS service manages authentication and accounting for network users, synchronizing with FreeRADIUS server.

### Features

- **User Authentication**: Validate user credentials
- **Accounting**: Track session start, update, and stop events
- **User Synchronization**: Sync network users to RADIUS database
- **Statistics**: Retrieve usage statistics for users
- **Separate Database**: Uses dedicated RADIUS database connection

### Usage Examples

#### Create RADIUS User

```php
use App\Services\RadiusService;

$radiusService = app(RadiusService::class);

// Create user with attributes
$success = $radiusService->createUser(
    username: 'customer123',
    password: 'secure-password',
    attributes: [
        'Framed-Protocol' => 'PPP',
        'Framed-IP-Address' => '192.168.1.100'
    ]
);
```

#### Sync Network User to RADIUS

```php
use App\Models\NetworkUser;

$user = NetworkUser::find(1);

// Sync user to RADIUS database
$success = $radiusService->syncUser(
    user: $user,
    attributes: [
        'password' => 'new-password'
    ]
);
```

#### Get User Statistics

```php
$stats = $radiusService->getUserStats(username: 'customer123');

echo "Total Sessions: {$stats['total_sessions']}\n";
echo "Total Upload: " . formatBytes($stats['total_upload_bytes']) . "\n";
echo "Total Download: " . formatBytes($stats['total_download_bytes']) . "\n";
echo "Total Time: " . formatSeconds($stats['total_session_time']) . "\n";
```

#### Accounting Operations

```php
// Start accounting session
$radiusService->accountingStart([
    'session_id' => 'session-123',
    'username' => 'customer123',
    'nas_ip' => '10.0.0.1',
    'framed_ip' => '192.168.1.100',
    'start_time' => now()
]);

// Update accounting
$radiusService->accountingUpdate([
    'session_id' => 'session-123',
    'session_time' => 3600,
    'input_octets' => 1048576,
    'output_octets' => 2097152
]);

// Stop accounting
$radiusService->accountingStop([
    'session_id' => 'session-123',
    'stop_time' => now(),
    'session_time' => 7200,
    'input_octets' => 2097152,
    'output_octets' => 4194304,
    'terminate_cause' => 'User-Request'
]);
```

### API Endpoints

```http
POST   /api/v1/radius/authenticate          # Authenticate user
POST   /api/v1/radius/users                 # Create RADIUS user
PUT    /api/v1/radius/users/{username}      # Update RADIUS user
DELETE /api/v1/radius/users/{username}      # Delete RADIUS user
POST   /api/v1/radius/users/{username}/sync # Sync user to RADIUS

POST   /api/v1/radius/accounting/start      # Start accounting session
POST   /api/v1/radius/accounting/update     # Update accounting session
POST   /api/v1/radius/accounting/stop       # Stop accounting session

GET    /api/v1/radius/users/{username}/stats # Get user statistics
```

### Database Schema

The RADIUS service uses the standard FreeRADIUS schema:

- **radcheck**: User authentication credentials
- **radreply**: User reply attributes
- **radacct**: Accounting records (sessions, traffic, duration)

---

## MikroTik Service

### Overview

The MikroTik service manages PPPoE users and sessions on MikroTik routers via RouterOS API.

### Features

- **PPPoE User Management**: Create, update, delete PPPoE secrets
- **Session Monitoring**: Track active PPPoE sessions
- **Session Control**: Disconnect active sessions
- **Profile Management**: Manage PPPoE profiles
- **IP Pool Management**: Sync IP pools from routers
- **Queue Management**: Configure bandwidth limitations
- **VPN Account Management**: Manage VPN users
- **Router Configuration**: Import and configure router settings

### Usage Examples

#### Connect to Router

```php
use App\Services\MikrotikService;

$mikrotikService = app(MikrotikService::class);

// Connect to router
$connected = $mikrotikService->connectRouter(routerId: 1);

if ($connected) {
    echo "Connected to router";
}
```

#### Create PPPoE User

```php
$success = $mikrotikService->createPppoeUser([
    'router_id' => 1,
    'username' => 'customer123',
    'password' => 'secure-password',
    'service' => 'pppoe',
    'profile' => '10Mbps',
    'local_address' => '10.0.0.1',
    'remote_address' => '192.168.1.100'
]);
```

#### Get Active Sessions

```php
$sessions = $mikrotikService->getActiveSessions(routerId: 1);

foreach ($sessions as $session) {
    echo "User: {$session['username']}\n";
    echo "IP: {$session['ip_address']}\n";
    echo "Uptime: {$session['uptime']}\n";
}
```

#### Disconnect Session

```php
$success = $mikrotikService->disconnectSession(sessionId: 'session-123');

if ($success) {
    echo "Session disconnected";
}
```

### API Endpoints

```http
GET    /api/v1/mikrotik/routers                    # List routers
POST   /api/v1/mikrotik/routers/{id}/connect       # Connect to router
GET    /api/v1/mikrotik/routers/{id}/health        # Check router health

GET    /api/v1/mikrotik/pppoe-users                # List PPPoE users
POST   /api/v1/mikrotik/pppoe-users                # Create PPPoE user
PUT    /api/v1/mikrotik/pppoe-users/{id}           # Update PPPoE user
DELETE /api/v1/mikrotik/pppoe-users/{id}           # Delete PPPoE user

GET    /api/v1/mikrotik/sessions                   # List active sessions
DELETE /api/v1/mikrotik/sessions/{id}              # Disconnect session

GET    /api/v1/mikrotik/profiles                   # List PPPoE profiles
POST   /api/v1/mikrotik/profiles                   # Create profile
POST   /api/v1/mikrotik/routers/{id}/import-profiles # Import profiles from router

GET    /api/v1/mikrotik/ip-pools                   # List IP pools
POST   /api/v1/mikrotik/routers/{id}/import-pools  # Import IP pools from router

POST   /api/v1/mikrotik/routers/{id}/configure     # Configure router
GET    /api/v1/mikrotik/routers/{id}/configurations # Get router configurations

GET    /api/v1/mikrotik/vpn-accounts               # List VPN accounts
POST   /api/v1/mikrotik/vpn-accounts               # Create VPN account

GET    /api/v1/mikrotik/queues                     # List bandwidth queues
POST   /api/v1/mikrotik/queues                     # Create queue

POST   /api/v1/mikrotik/users/{id}/apply-speed     # Apply speed limit to user
```

### Router Configuration

MikroTik routers are configured with:

```php
// Router credentials stored in database
$router = MikrotikRouter::create([
    'name' => 'Main Router',
    'ip_address' => '192.168.88.1',
    'api_port' => 8728,
    'username' => 'admin',
    'password' => 'secure-password', // Encrypted automatically
    'status' => 'active'
]);
```

---

## Integration Guide

### Setting Up Network Services

#### 1. Configure Environment

Update `.env` with RADIUS and MikroTik settings:

```env
# RADIUS Database
RADIUS_DB_HOST=127.0.0.1
RADIUS_DB_PORT=3306
RADIUS_DB_DATABASE=radius
RADIUS_DB_USERNAME=radius
RADIUS_DB_PASSWORD=secret

# MikroTik
MIKROTIK_API_TIMEOUT=30
MIKROTIK_DEFAULT_PORT=8728

# IPAM
IPAM_ALLOCATION_TIMEOUT=30
IPAM_LOCK_TIMEOUT=10
```

#### 2. Run Migrations

```bash
# Main database migrations
php artisan migrate

# RADIUS database migrations
php artisan migrate --database=radius --path=database/migrations/radius
```

#### 3. Configure Routers

Add MikroTik routers via API or web interface:

```bash
# Via artisan tinker
php artisan tinker

>>> $router = App\Models\MikrotikRouter::create([
...     'name' => 'Main Router',
...     'ip_address' => '192.168.88.1',
...     'api_port' => 8728,
...     'username' => 'admin',
...     'password' => 'password',
...     'status' => 'active'
... ]);
```

#### 4. Setup Scheduled Tasks

Scheduled tasks are already configured in `routes/console.php`:

```bash
# Start the scheduler
php artisan schedule:work
```

Or setup cron job:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Integration with FreeRADIUS

#### 1. Configure FreeRADIUS

Point FreeRADIUS to use the RADIUS database:

```sql
# /etc/freeradius/3.0/mods-available/sql
sql {
    driver = "mysql"
    server = "localhost"
    port = 3306
    login = "radius"
    password = "secret"
    radius_db = "radius"
}
```

#### 2. Enable SQL Module

```bash
cd /etc/freeradius/3.0/mods-enabled
ln -s ../mods-available/sql sql
systemctl restart freeradius
```

#### 3. Test Authentication

```bash
# Test RADIUS authentication
radtest customer123 password 127.0.0.1 0 testing123
```

---

## Configuration

### IPAM Configuration

Edit `config/ipam.php`:

```php
return [
    'default_pool_size' => 256,
    'cleanup_days' => 30,
    'allocation_ttl' => 86400, // 24 hours
    'enable_overlap_detection' => true,
];
```

### RADIUS Configuration

Edit `config/radius.php`:

```php
return [
    'connection' => 'radius',
    'hash_type' => 'cleartext', // cleartext, md5, sha1
    'timeout' => 30,
    'default_attributes' => [
        'Framed-Protocol' => 'PPP',
        'Service-Type' => 'Framed-User',
    ],
];
```

### MikroTik Configuration

Edit `config/mikrotik.php`:

```php
return [
    'host' => env('MIKROTIK_HOST', '192.168.88.1'),
    'port' => env('MIKROTIK_PORT', 8728),
    'username' => env('MIKROTIK_USERNAME', 'admin'),
    'password' => env('MIKROTIK_PASSWORD'),
    'timeout' => env('MIKROTIK_TIMEOUT', 30),
    'retry_attempts' => 3,
    'retry_delay' => 1000, // milliseconds
];
```

---

## Troubleshooting

### Common Issues

#### IPAM: No Available IPs

**Problem**: `allocateIP()` returns null

**Solutions**:
- Check subnet utilization: `getPoolUtilization()`
- Verify subnet is active: `IpSubnet::where('status', 'active')`
- Check for IP conflicts in database
- Increase subnet size or add new subnets

#### RADIUS: Authentication Fails

**Problem**: Users cannot authenticate via RADIUS

**Solutions**:
- Verify user exists in radcheck: `RadCheck::where('username', $username)->exists()`
- Check password attribute: Should be `Cleartext-Password`
- Verify RADIUS database connection: `php artisan migrate:status --database=radius`
- Test FreeRADIUS: `radtest username password localhost 0 testing123`
- Check FreeRADIUS logs: `tail -f /var/log/freeradius/radius.log`

#### MikroTik: Connection Failed

**Problem**: Cannot connect to MikroTik router

**Solutions**:
- Verify router IP address and port
- Check API service is enabled on router: `/ip service print`
- Verify credentials are correct
- Check firewall rules allow API access
- Test connection: `telnet 192.168.88.1 8728`
- Check router logs: `/log print`

#### Database: Connection Timeout

**Problem**: Database connection timeout errors

**Solutions**:
- Check database server is running
- Verify connection credentials in `.env`
- Increase timeout in `config/database.php`
- Check for long-running queries
- Monitor database locks: `SHOW PROCESSLIST;`

### Debug Mode

Enable debug logging for network services:

```php
// In .env
LOG_LEVEL=debug

// In code
Log::debug('IPAM allocation', [
    'subnet_id' => $subnetId,
    'username' => $username,
    'available_ips' => count($availableIPs)
]);
```

### Health Checks

Run health checks for all services:

```bash
# Check MikroTik routers
php artisan mikrotik:health-check

# Check OLT devices
php artisan olt:health-check

# Run all health checks
php artisan network:health-check
```

### Performance Monitoring

Monitor service performance:

```bash
# Check active sessions
php artisan mikrotik:sync-sessions --verbose

# Check RADIUS sync
php artisan radius:sync-users --verbose

# Check IPAM cleanup
php artisan ipam:cleanup --dry-run
```

---

## Additional Resources

- [API Documentation](API.md)
- [Testing Guide](TESTING.md)
- [Deployment Guide](DEPLOYMENT.md)
- [Implementation Status](IMPLEMENTATION_STATUS.md)

---

**Last Updated:** 2026-01-17  
**Maintained by:** ISP Solution Development Team
