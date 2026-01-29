# RADIUS Integration Guide for ISP Solution

## Overview

This ISP Solution uses a **two-tier RADIUS architecture**:

1. **External RADIUS Server** (FreeRADIUS) - Handles UDP protocol communication with Mikrotik routers
2. **ISP Solution Database** - Stores RADIUS user data (radcheck, radreply, radacct tables)

## Architecture

```
┌─────────────────┐      RADIUS Protocol       ┌──────────────────┐
│  Mikrotik Router│  ◄──────(UDP 1812/1813)───►│  FreeRADIUS      │
│  103.138.147.185│                             │  Server          │
└─────────────────┘                             └──────────────────┘
                                                         │
                                                         │ SQL Queries
                                                         ▼
                                                ┌──────────────────┐
                                                │  MySQL/MariaDB   │
                                                │  (radius DB)     │
                                                └──────────────────┘
                                                         │
                                                         │ HTTP API
                                                         ▼
                                                ┌──────────────────┐
                                                │  ISP Solution    │
                                                │  Laravel App     │
                                                └──────────────────┘
```

## Why Mikrotik Receives No Response

**Issue**: Mikrotik sends RADIUS requests to port 1812/1813 but receives no response.

**Root Cause**: The ISP Solution is a Laravel web application that:
- Provides HTTP/JSON API endpoints for RADIUS operations
- Stores RADIUS data in MySQL database tables
- **Does NOT listen on UDP ports 1812/1813**
- **Does NOT implement the RADIUS protocol directly**

### Current Implementation

The application provides REST API endpoints:
- `POST /api/v1/radius/authenticate` - Check credentials via HTTP
- `POST /api/v1/radius/accounting/start` - Start session via HTTP
- `POST /api/v1/radius/accounting/update` - Update session via HTTP
- `POST /api/v1/radius/accounting/stop` - Stop session via HTTP

These endpoints are meant for:
- Web-based authentication
- Administrative tools
- Integration with external systems via HTTP

## Solution: Install FreeRADIUS

To enable RADIUS protocol communication with Mikrotik, you need to install FreeRADIUS:

### 1. Install FreeRADIUS

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install freeradius freeradius-mysql

# CentOS/RHEL
sudo yum install freeradius freeradius-mysql
```

### 2. Configure FreeRADIUS to Use ISP Solution Database

Edit `/etc/freeradius/3.0/mods-available/sql`:

```conf
sql {
    driver = "rlm_sql_mysql"
    
    server = "localhost"
    port = 3306
    login = "radius_user"
    password = "your_radius_password"
    
    radius_db = "ispsolution_radius"  # Your radius database name
    
    # Read queries from the database
    read_clients = yes
    
    # Connection info
    connection {
        pool {
            start = 5
            min = 4
            max = 10
            spare = 3
            uses = 0
            lifetime = 0
            cleanup_interval = 30
            idle_timeout = 60
        }
    }
}
```

### 3. Enable SQL Module

```bash
sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/sql
```

### 4. Configure NAS Clients

Edit `/etc/freeradius/3.0/clients.conf` to add your Mikrotik router:

```conf
client mikrotik_main {
    ipaddr = 103.138.147.185
    secret = your_shared_secret_here
    nastype = mikrotik
    shortname = mikrotik-main
}
```

### 5. Restart FreeRADIUS

```bash
sudo systemctl restart freeradius
sudo systemctl enable freeradius

# Check status
sudo systemctl status freeradius

# Test in debug mode
sudo freeradius -X
```

## Mikrotik Configuration

### Configure RADIUS on Mikrotik

```bash
# Via CLI or API
/radius add address=YOUR_SERVER_IP secret=your_shared_secret_here service=ppp

# Or via web interface:
# IP > Hotspot > Server Profiles > RADIUS
```

### Test RADIUS from Mikrotik

```bash
# Test authentication
/radius monitor 0
```

## Logging and Troubleshooting

### Application Logs

With the recent changes, the ISP Solution now logs all RADIUS-related operations:

1. **Authentication Attempts** (`storage/logs/laravel.log`):
```
[timestamp] local.INFO: RADIUS authentication request received
{
    "username": "testuser",
    "client_ip": "103.138.147.185",
    "timestamp": "2026-01-29T09:30:00+00:00"
}
```

2. **Database Lookups**:
```
[timestamp] local.DEBUG: RADIUS authenticate: Checking credentials in database
{
    "username": "testuser",
    "connection": "radius"
}
```

3. **Authentication Results**:
```
[timestamp] local.INFO: RADIUS authenticate: User found and authenticated
{
    "username": "testuser"
}
```

Or:
```
[timestamp] local.WARNING: RADIUS authenticate: User not found in database
{
    "username": "testuser"
}
```

### FreeRADIUS Logs

Check FreeRADIUS logs for protocol-level issues:

```bash
# Live debugging
sudo freeradius -X

# Check logs
sudo tail -f /var/log/freeradius/radius.log
```

### Database Verification

Check if users exist in RADIUS tables:

```sql
-- Check radcheck table
SELECT * FROM radcheck WHERE username = 'testuser';

-- Check radreply table
SELECT * FROM radreply WHERE username = 'testuser';

-- Check active sessions
SELECT * FROM radacct WHERE acctstoptime IS NULL;
```

## Creating RADIUS Users

### Via ISP Solution API

```bash
curl -X POST http://your-app-url/api/v1/radius/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "username": "testuser",
    "password": "testpass",
    "attributes": {
      "Framed-Protocol": "PPP",
      "Service-Type": "Framed-User"
    }
  }'
```

### Via Database (Manual)

```sql
-- Add user credentials
INSERT INTO radcheck (username, attribute, op, value) 
VALUES ('testuser', 'Cleartext-Password', ':=', 'testpass');

-- Add reply attributes
INSERT INTO radreply (username, attribute, op, value) 
VALUES 
  ('testuser', 'Framed-Protocol', '=', 'PPP'),
  ('testuser', 'Service-Type', '=', 'Framed-User');
```

## Testing the Complete Flow

1. **Create a test user** via ISP Solution API or database
2. **Start FreeRADIUS in debug mode**: `sudo freeradius -X`
3. **Monitor ISP Solution logs**: `tail -f storage/logs/laravel.log`
4. **Test from Mikrotik**: Create a PPP secret and try to connect
5. **Check logs on both sides**:
   - FreeRADIUS should show the RADIUS exchange
   - ISP Solution logs will show database queries (if FreeRADIUS queries the DB)

## Recent Changes (2026-01-29)

### Enhanced Logging

All RADIUS endpoints now include comprehensive logging:

1. **Incoming Request Logging**:
   - Username, session ID, client IP
   - ISO8601 timestamps
   - User agent information

2. **Validation Failure Logging**:
   - Detailed error messages
   - Request parameters that failed validation

3. **Database Operation Logging**:
   - User existence checks
   - Password match status
   - Connection information

4. **Success/Failure Outcomes**:
   - Clear indication of authentication result
   - Reason for failure (user not found vs wrong password)

### Mikrotik API Improvements

The MikrotikApiService now provides detailed error reporting:

- Returns array instead of boolean
- Includes per-row error details
- Logs HTTP response bodies for debugging
- Tracks success/failure counts

## Support

For issues:
1. Check application logs: `storage/logs/laravel.log`
2. Check FreeRADIUS logs: `sudo tail -f /var/log/freeradius/radius.log`
3. Verify database connectivity
4. Test RADIUS with `radtest` tool:
   ```bash
   radtest testuser testpass localhost 0 testing123
   ```

## Security Notes

- The current implementation uses `Cleartext-Password` attribute
- For production, consider using PAP with hashed passwords or CHAP
- Ensure RADIUS database has appropriate access controls
- Use strong shared secrets between FreeRADIUS and Mikrotik
- Consider using RADIUS over TLS (RadSec) for enhanced security
