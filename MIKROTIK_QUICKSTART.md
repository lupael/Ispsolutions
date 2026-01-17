# Quick Start Guide - MikroTik Advanced Features

This guide will help you quickly get started with the advanced MikroTik features.

## Prerequisites

1. MikroTik router configured and accessible
2. Router added to the system via the API or admin panel
3. Basic understanding of PPPoE/ISP concepts

## Quick Setup

### Step 1: Import Existing Configuration

If you already have a configured MikroTik router, import all settings:

```bash
# Import everything at once
php artisan mikrotik:sync-all 1

# Or import individually
php artisan mikrotik:import-profiles 1
php artisan mikrotik:import-pools 1
php artisan mikrotik:import-secrets 1
```

Replace `1` with your router ID or name.

### Step 2: Create Speed Packages

Create PPPoE profiles for your service packages:

**API Request:**
```bash
curl -X POST http://your-app.com/api/v1/mikrotik/profiles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "name": "package-10mbps",
    "rate_limit": "10M/10M",
    "local_address": "192.168.1.1",
    "remote_address": "pool-clients"
  }'
```

**Available Speeds:**
- `"10M/10M"` - 10 Mbps up/down
- `"50M/50M"` - 50 Mbps up/down
- `"100M/100M"` - 100 Mbps up/down
- `"10M/50M"` - 10 Mbps up, 50 Mbps down (asymmetric)

### Step 3: Create IP Pools

Create IP address pools for your clients:

```bash
curl -X POST http://your-app.com/api/v1/mikrotik/ip-pools \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "name": "pool-clients",
    "ranges": [
      "192.168.1.10-192.168.1.100",
      "192.168.1.200-192.168.1.254"
    ]
  }'
```

**IPv6 Support:**
```json
{
  "router_id": 1,
  "name": "pool-clients-v6",
  "ranges": [
    "2001:db8::10-2001:db8::100"
  ]
}
```

### Step 4: Link Packages to Profiles

Map your service packages to router profiles:

```bash
curl -X POST http://your-app.com/api/v1/mikrotik/package-mappings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "package_id": 1,
    "router_id": 1,
    "profile_name": "package-10mbps"
  }'
```

### Step 5: Apply Speed to Users

Automatically apply speed profiles to users:

```bash
curl -X POST http://your-app.com/api/v1/mikrotik/users/1/apply-speed \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "method": "router"
  }'
```

## Common Tasks

### Add Firewall Rules

```bash
# Block specific port
curl -X POST http://your-app.com/api/v1/mikrotik/firewall-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "chain": "forward",
    "action": "drop",
    "protocol": "tcp",
    "dst-port": "25",
    "comment": "Block SMTP"
  }'

# Allow HTTP/HTTPS
curl -X POST http://your-app.com/api/v1/mikrotik/firewall-rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "chain": "forward",
    "action": "accept",
    "protocol": "tcp",
    "dst-port": "80,443",
    "comment": "Allow web traffic"
  }'
```

### Create VPN Accounts

```bash
curl -X POST http://your-app.com/api/v1/mikrotik/vpn-accounts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "username": "vpnuser1",
    "password": "SecurePass123!",
    "profile": "default",
    "enabled": true
  }'
```

### Create Bandwidth Queues

```bash
# Simple queue for a specific IP
curl -X POST http://your-app.com/api/v1/mikrotik/queues \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "name": "client-192.168.1.10",
    "target": "192.168.1.10/32",
    "max_limit": "10M/10M",
    "priority": 5
  }'

# Queue with burst
curl -X POST http://your-app.com/api/v1/mikrotik/queues \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "router_id": 1,
    "name": "client-burst",
    "target": "192.168.1.20/32",
    "max_limit": "10M/10M",
    "burst_limit": "15M/15M",
    "burst_threshold": "8M/8M",
    "burst_time": 10,
    "priority": 5
  }'
```

### One-Click Router Configuration

Configure multiple aspects at once:

```bash
# Via API
curl -X POST http://your-app.com/api/v1/mikrotik/routers/1/configure \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "ppp": true,
    "pools": true,
    "firewall": true,
    "radius": true
  }'

# Via CLI
php artisan mikrotik:configure 1 --ppp --pools --firewall --radius
```

## Workflow Example

Here's a complete workflow for setting up a new ISP customer:

```bash
# 1. Create the service package (if not exists)
# (Done via admin panel or package API)

# 2. Create router profile
php artisan mikrotik:import-profiles 1

# 3. Link package to profile
curl -X POST http://your-app.com/api/v1/mikrotik/package-mappings \
  -d '{"package_id": 1, "router_id": 1, "profile_name": "package-10mbps"}'

# 4. Create customer account
# (Done via user management API)

# 5. Apply speed to customer
curl -X POST http://your-app.com/api/v1/mikrotik/users/{userId}/apply-speed \
  -d '{"user_id": 123, "method": "router"}'

# 6. Customer can now connect via PPPoE
```

## Monitoring

### View Router Configurations

```bash
curl -X GET http://your-app.com/api/v1/mikrotik/routers/1/configurations \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Check VPN Status

```bash
curl -X GET http://your-app.com/api/v1/mikrotik/routers/1/vpn-status \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### List Firewall Rules

```bash
curl -X GET http://your-app.com/api/v1/mikrotik/routers/1/firewall-rules \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Troubleshooting

### Issue: Profile not found after creation

**Solution:** Import profiles to ensure sync:
```bash
php artisan mikrotik:import-profiles 1
```

### Issue: User speed not applying

**Solution:** Check package mapping exists:
```bash
curl -X GET http://your-app.com/api/v1/mikrotik/package-mappings?package_id=1&router_id=1
```

### Issue: IP pool exhausted

**Solution:** Add more ranges to the pool:
```bash
curl -X POST http://your-app.com/api/v1/mikrotik/ip-pools \
  -d '{"router_id": 1, "name": "pool-clients-2", "ranges": ["192.168.2.10-192.168.2.254"]}'
```

## Best Practices

1. **Import Regularly**: Use `mikrotik:sync-all` to keep database in sync
2. **Use Profiles**: Create reusable profiles for common speed tiers
3. **Document Rules**: Always add comments to firewall rules
4. **Monitor Pools**: Keep track of IP pool utilization
5. **Backup Config**: Configuration history is stored in `router_configurations` table
6. **Test First**: Test new configurations on a test router before production
7. **Use Transactions**: The system uses database transactions for consistency
8. **Check Logs**: All operations are logged for troubleshooting

## Security Checklist

- [ ] Use HTTPS in production
- [ ] Rotate router passwords regularly
- [ ] Limit API access with proper authentication
- [ ] Monitor firewall rule changes
- [ ] Review VPN account permissions
- [ ] Enable router logging
- [ ] Use strong passwords for VPN accounts
- [ ] Regularly audit package mappings

## Next Steps

1. Review the full documentation in `MIKROTIK_ADVANCED_FEATURES.md`
2. Explore the test files for usage examples
3. Set up automated sync with cron: `* * * * * php artisan mikrotik:sync-all 1`
4. Integrate with your billing system
5. Create custom reports using the database tables

## Support

For detailed API documentation, see `MIKROTIK_ADVANCED_FEATURES.md`.
For code examples, see the test files in `tests/Unit/`.
