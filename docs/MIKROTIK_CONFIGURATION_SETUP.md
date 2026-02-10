# MikroTik Router Configuration Setup

## Overview

The MikroTik router configuration feature (`/panel/admin/mikrotik/{id}/configure`) requires a REST API endpoint on the MikroTik router to receive configuration commands.

## Current Implementation

The system attempts to send configuration requests to:
```
POST http://{router_ip}:{api_port}/api/configure
```

This endpoint does not exist by default on MikroTik RouterOS.

## Setup Options

### Option 1: Use RouterOS API Directly (Recommended)

The system includes `MikrotikApiService` which can communicate directly with RouterOS API (port 8728). To use this:

1. Ensure the router's API port (default 8728) is accessible
2. Configure proper username/password in the router settings
3. The system will automatically use the RouterOS API for operations

Currently supported via RouterOS API:
- ✅ IP Pool import
- ✅ PPP Profile import  
- ✅ PPP Secrets (customer) import
- ⚠️ One-click configuration (in development)

### Option 2: Custom REST API Wrapper

If you need the HTTP-based configuration endpoint:

1. Deploy a custom REST API service on your network that:
   - Listens on the configured `api_port` (default 8728)
   - Accepts POST requests at `/api/configure`
   - Translates requests to RouterOS API commands
   - Returns appropriate success/error responses

Example wrapper implementation structure:
```
POST /api/configure
{
  "pppoe": { "interface": "ether1", ... },
  "ippool": { "pool_name": "default", ... },
  "firewall": { "chain": "forward", ... },
  "queue": { "queue_name": "default", ... }
}
```

## Troubleshooting

### Error: "Failed to apply configuration to the router"

This error occurs when:
1. The REST API endpoint is not available
2. The router credentials are incorrect
3. The router is not reachable
4. SSRF protection blocked the request (private IPs need to be explicitly allowed)

**Solution**: 
- Verify router connectivity with the connection test feature
- Check router credentials
- Ensure the router has a REST API wrapper deployed (Option 2 above)
- Or wait for direct RouterOS API configuration support (Option 1, in development)

### SSRF Protection

The system includes SSRF (Server-Side Request Forgery) protection that blocks requests to:
- Private IP ranges (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
- Metadata services (169.254.169.254)
- Localhost (127.0.0.1)

For production use with routers on private networks, you may need to:
1. Use public IPs or VPN
2. Modify `isValidRouterIpAddress()` in `MikrotikService.php` to allow your network ranges
3. Deploy the system on the same network as the routers

## IP Pool Import

✅ **This feature is now fully functional!**

The IP pool import feature uses the RouterOS API directly and does not require a custom wrapper:

```
POST /panel/admin/mikrotik/import/ip-pools
{
  "router_id": 3
}
```

The system will:
1. Connect to the router via RouterOS API
2. Fetch all IP pools from `/ip/pool`
3. Parse the IP ranges
4. Import them into the local database
5. Create a backup before import

## Future Development

Direct RouterOS API support for one-click configuration is planned. This will allow configuration without requiring a custom REST API wrapper.
