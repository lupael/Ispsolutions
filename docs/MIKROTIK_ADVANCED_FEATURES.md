# MikroTik Advanced Features Documentation

This document describes the advanced ISP management features added to the MikroTik service.

## Table of Contents

1. [Profile Management](#profile-management)
2. [IP Pool Management](#ip-pool-management)
3. [Secret Import](#secret-import)
4. [One-Click Configuration](#one-click-configuration)
5. [VPN Management](#vpn-management)
6. [Queue Management](#queue-management)
7. [Firewall Management](#firewall-management)
8. [Package Speed Mapping](#package-speed-mapping)
9. [Artisan Commands](#artisan-commands)
10. [API Endpoints](#api-endpoints)

---

## Profile Management

### Create PPPoE Profile

Create a new PPPoE profile on the MikroTik router.

**Endpoint:** `POST /api/v1/mikrotik/profiles`

**Request Body:**
```json
{
  "router_id": 1,
  "name": "profile-10mbps",
  "local_address": "192.168.1.1",
  "remote_address": "192.168.1.0/24",
  "rate_limit": "10M/10M",
  "session_timeout": 3600,
  "idle_timeout": 600
}
```

### Import Profiles

Import all profiles from a MikroTik router to the database.

**Endpoint:** `POST /api/v1/mikrotik/routers/{routerId}/import-profiles`

**Command:**
```bash
php artisan mikrotik:import-profiles {router}
```

---

## IP Pool Management

### Create IP Pool

Create a new IP pool on the MikroTik router. Supports both IPv4 and IPv6.

**Endpoint:** `POST /api/v1/mikrotik/ip-pools`

**Request Body:**
```json
{
  "router_id": 1,
  "name": "pool-clients",
  "ranges": [
    "192.168.1.10-192.168.1.100",
    "192.168.1.200-192.168.1.254"
  ]
}
```

### Import IP Pools

Import all IP pools from a MikroTik router.

**Endpoint:** `POST /api/v1/mikrotik/routers/{routerId}/import-pools`

**Command:**
```bash
php artisan mikrotik:import-pools {router}
```

---

## Secret Import

Import PPPoE secrets (user credentials) from MikroTik router.

**Endpoint:** `POST /api/v1/mikrotik/routers/{routerId}/import-secrets`

**Command:**
```bash
php artisan mikrotik:import-secrets {router}
```

---

## One-Click Configuration

Configure multiple aspects of a MikroTik router with a single request.

**Endpoint:** `POST /api/v1/mikrotik/routers/{routerId}/configure`

**Request Body:**
```json
{
  "ppp": true,
  "pools": true,
  "hotspot": true,
  "pppoe": true,
  "firewall": true,
  "queue": true,
  "radius": true
}
```

**Command:**
```bash
php artisan mikrotik:configure {router} --ppp --pools --firewall
```

---

## VPN Management

### Create VPN Account

Create a VPN account (L2TP/PPTP) on the router.

**Endpoint:** `POST /api/v1/mikrotik/vpn-accounts`

**Request Body:**
```json
{
  "router_id": 1,
  "username": "vpnuser1",
  "password": "securepass123",
  "profile": "default",
  "enabled": true
}
```

### Get VPN Status

Get the status of VPN servers on the router.

**Endpoint:** `GET /api/v1/mikrotik/routers/{routerId}/vpn-status`

---

## Queue Management

### Create Queue

Create a simple queue for bandwidth management.

**Endpoint:** `POST /api/v1/mikrotik/queues`

**Request Body:**
```json
{
  "router_id": 1,
  "name": "queue-client1",
  "target": "192.168.1.10/32",
  "parent": "none",
  "max_limit": "10M/10M",
  "burst_limit": "15M/15M",
  "burst_threshold": "8M/8M",
  "burst_time": 10,
  "priority": 5
}
```

### List Queues

**Endpoint:** `GET /api/v1/mikrotik/queues?router_id=1`

---

## Firewall Management

### Add Firewall Rule

Add a firewall filter rule.

**Endpoint:** `POST /api/v1/mikrotik/firewall-rules`

**Request Body:**
```json
{
  "router_id": 1,
  "chain": "forward",
  "action": "accept",
  "protocol": "tcp",
  "src-address": "192.168.1.0/24",
  "dst-port": "80,443",
  "comment": "Allow HTTP/HTTPS"
}
```

### List Firewall Rules

**Endpoint:** `GET /api/v1/mikrotik/routers/{routerId}/firewall-rules`

---

## Package Speed Mapping

Link service packages to MikroTik profiles for automatic speed control.

### Map Package to Profile

**Endpoint:** `POST /api/v1/mikrotik/package-mappings`

**Request Body:**
```json
{
  "package_id": 1,
  "router_id": 1,
  "profile_name": "profile-10mbps"
}
```

### Apply Speed to User

Automatically apply the correct speed profile to a user based on their package.

**Endpoint:** `POST /api/v1/mikrotik/users/{userId}/apply-speed`

**Request Body:**
```json
{
  "user_id": 1,
  "method": "router"
}
```

**Methods:**
- `router` - Apply speed via router profile
- `radius` - Apply speed via RADIUS (future implementation)

---

## Artisan Commands

### Import Profiles
```bash
php artisan mikrotik:import-profiles {router}
```

### Import IP Pools
```bash
php artisan mikrotik:import-pools {router}
```

### Import Secrets
```bash
php artisan mikrotik:import-secrets {router}
```

### Configure Router
```bash
php artisan mikrotik:configure {router} --ppp --pools --firewall
```

### Sync All Data
```bash
php artisan mikrotik:sync-all {router}
```

This command imports profiles, IP pools, and secrets in one go.

---

## API Endpoints

### Router Management
- `GET /api/v1/mikrotik/routers` - List all routers
- `POST /api/v1/mikrotik/routers/{id}/connect` - Test connection
- `GET /api/v1/mikrotik/routers/{id}/health` - Health check
- `POST /api/v1/mikrotik/routers/{routerId}/configure` - One-click configuration
- `GET /api/v1/mikrotik/routers/{routerId}/configurations` - List configurations

### Profile Management
- `GET /api/v1/mikrotik/profiles` - List profiles
- `POST /api/v1/mikrotik/profiles` - Create profile
- `POST /api/v1/mikrotik/routers/{routerId}/import-profiles` - Import profiles

### IP Pool Management
- `GET /api/v1/mikrotik/ip-pools` - List IP pools
- `POST /api/v1/mikrotik/ip-pools` - Create IP pool
- `POST /api/v1/mikrotik/routers/{routerId}/import-pools` - Import pools

### Secret Management
- `POST /api/v1/mikrotik/routers/{routerId}/import-secrets` - Import secrets

### VPN Management
- `GET /api/v1/mikrotik/vpn-accounts` - List VPN accounts
- `POST /api/v1/mikrotik/vpn-accounts` - Create VPN account
- `GET /api/v1/mikrotik/routers/{routerId}/vpn-status` - Get VPN status

### Queue Management
- `GET /api/v1/mikrotik/queues` - List queues
- `POST /api/v1/mikrotik/queues` - Create queue

### Firewall Management
- `GET /api/v1/mikrotik/routers/{routerId}/firewall-rules` - List firewall rules
- `POST /api/v1/mikrotik/firewall-rules` - Add firewall rule

### Package Speed Mapping
- `GET /api/v1/mikrotik/package-mappings` - List mappings
- `POST /api/v1/mikrotik/package-mappings` - Create mapping
- `POST /api/v1/mikrotik/users/{userId}/apply-speed` - Apply speed to user

---

## Database Schema

### mikrotik_profiles
- `id` - Primary key
- `router_id` - Foreign key to mikrotik_routers
- `name` - Profile name
- `local_address` - Local address
- `remote_address` - Remote address
- `rate_limit` - Speed limit (e.g., "10M/10M")
- `session_timeout` - Session timeout in seconds
- `idle_timeout` - Idle timeout in seconds

### mikrotik_ip_pools
- `id` - Primary key
- `router_id` - Foreign key to mikrotik_routers
- `name` - Pool name
- `ranges` - JSON array of IP ranges

### mikrotik_vpn_accounts
- `id` - Primary key
- `router_id` - Foreign key to mikrotik_routers
- `username` - VPN username
- `password` - Encrypted password
- `profile` - VPN profile
- `enabled` - Account status

### mikrotik_queues
- `id` - Primary key
- `router_id` - Foreign key to mikrotik_routers
- `name` - Queue name
- `target` - Target IP/network
- `parent` - Parent queue
- `max_limit` - Maximum bandwidth
- `burst_limit` - Burst bandwidth
- `burst_threshold` - Burst threshold
- `burst_time` - Burst time in seconds
- `priority` - Queue priority (1-8)

### router_configurations
- `id` - Primary key
- `router_id` - Foreign key to mikrotik_routers
- `config_type` - Configuration type
- `config_data` - JSON configuration data
- `applied_at` - Timestamp when applied
- `status` - Configuration status

### package_profile_mappings
- `id` - Primary key
- `package_id` - Foreign key to packages
- `router_id` - Foreign key to mikrotik_routers
- `profile_name` - MikroTik profile name
- `speed_control_method` - Method (router/radius)

---

## Security Notes

1. **Authentication**: All API endpoints require authentication via Laravel Sanctum.
2. **Encryption**: Router passwords and VPN account passwords are encrypted at rest using Laravel's encrypted casting.
3. **HTTPS**: For production, configure HTTPS to protect credentials in transit.
4. **Input Validation**: All inputs are validated before being sent to routers.
5. **Logging**: All operations are logged for audit purposes.

---

## Usage Examples

### Example 1: Setting up a new ISP package with speed control

```bash
# 1. Create a PPPoE profile on the router
curl -X POST http://your-app.com/api/v1/mikrotik/profiles \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "name": "package-50mbps",
    "rate_limit": "50M/50M"
  }'

# 2. Map the package to the profile
curl -X POST http://your-app.com/api/v1/mikrotik/package-mappings \
  -H "Content-Type: application/json" \
  -d '{
    "package_id": 1,
    "router_id": 1,
    "profile_name": "package-50mbps"
  }'

# 3. Apply speed to a user
curl -X POST http://your-app.com/api/v1/mikrotik/users/1/apply-speed \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "method": "router"
  }'
```

### Example 2: Importing existing configuration

```bash
# Sync all data from a router
php artisan mikrotik:sync-all 1

# Or import individually
php artisan mikrotik:import-profiles 1
php artisan mikrotik:import-pools 1
php artisan mikrotik:import-secrets 1
```

---

## Testing

Run the test suite:

```bash
# Run all MikroTik tests
php artisan test --filter=MikrotikAdvancedFeaturesTest

# Run package speed tests
php artisan test --filter=PackageSpeedServiceTest

# Run all tests
php artisan test
```

---

## Support

For issues or questions, please refer to the main README or create an issue in the repository.
