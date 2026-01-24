# Zero-Touch Router Provisioning

## Overview

The Zero-Touch Router Provisioning feature automates the complete configuration of MikroTik routers with a single click. This flagship feature eliminates manual configuration errors and reduces router setup time from hours to minutes.

## Features

### 1. Automated Configuration
- **RADIUS Server**: Configures authentication and accounting (ports 1812/1813, shared secret)
- **Hotspot Profile**: Sets up MAC authentication, cookie timeout, login methods
- **PPPoE Server**: Configures IP pools, authentication methods, session limits
- **NAT Rules**: Automates srcnat masquerade and dstnat redirect rules
- **Firewall Rules**: Configures SNMP access, suspended pool blocking, security rules
- **System Settings**: Sets router identity, NTP servers, timezone
- **Walled Garden**: Configures access to central server, payment gateway, DNS
- **Suspended Pool**: Automates 10.255.255.0/24 pool with payment page redirect

### 2. Template System
- **Pre-built Templates**: 5 default templates for common scenarios
- **Custom Templates**: Create your own templates with JSON configuration
- **Placeholder Support**: Use variables like `{{ radius_secret }}` for dynamic values
- **Template Types**: radius, hotspot, pppoe, firewall, system, nat, walled_garden, suspended_pool, full_provisioning

### 3. Safety & Reliability
- **Pre-Provisioning Backup**: Automatic configuration backup before changes
- **Validation**: Connectivity and RADIUS server testing
- **Step-by-Step Progress**: Real-time feedback for each configuration step
- **Rollback**: Automatic rollback on failure, manual rollback from backup history
- **Audit Trail**: Complete logging of all provisioning actions

## Usage

### Accessing the Feature

Navigate to: **Admin Panel → Routers → Zero-Touch Provisioning**

URL: `/panel/admin/routers/provision`

### Step-by-Step Guide

#### 1. Select Router
- Choose a router from the dropdown
- Click "Test Connection" to verify router is reachable
- View router status and IP information

#### 2. Create Backup (Recommended)
- Click "Create Backup" to save current configuration
- Backups are stored for rollback if needed

#### 3. Select Template
- Choose a provisioning template
- Available templates:
  - **Full ISP Provisioning**: Complete setup with all features
  - **RADIUS Only**: Configure only RADIUS authentication
  - **Hotspot Profile**: Setup hotspot with RADIUS
  - **PPPoE Server**: Configure PPPoE with IP pools
  - **System Configuration**: Basic system settings

#### 4. Configure Variables
Fill in the required variables:
- **Central Server IP**: Your management server IP
- **RADIUS Server**: RADIUS authentication server
- **RADIUS Secret**: Shared secret for RADIUS
- **System Identity**: Router name/identity
- **Hotspot Address**: Hotspot gateway IP (default: 10.5.50.1)
- **DNS Name**: Hotspot DNS name (default: hotspot.local)
- **PPPoE Pool**: IP range for PPPoE clients
- **Timezone**: Router timezone (e.g., Asia/Kolkata)
- **NTP Server**: Time synchronization server

#### 5. Preview Configuration
- Click "Preview Configuration" to see the generated config
- Review all settings before applying
- Make adjustments to variables if needed

#### 6. Execute Provisioning
- Click "Execute Provisioning"
- Confirm the action (this will modify router configuration)
- Monitor real-time progress
- Each step shows success/failure status

#### 7. Verify Results
- Check provisioning logs for details
- Test router functionality
- If issues occur, use rollback feature

### Rollback Procedure

If provisioning fails or causes issues:

1. Navigate to the Configuration Backups section
2. Find the backup created before provisioning
3. Click "Rollback" button
4. Confirm rollback action
5. Wait for restoration to complete

## Template Configuration

### Creating Custom Templates

Templates use JSON configuration with placeholder variables:

```json
{
    "radius": {
        "server": "{{ radius_server }}",
        "secret": "{{ radius_secret }}",
        "auth_port": 1812,
        "acct_port": 1813
    },
    "hotspot": {
        "profile_name": "default",
        "hotspot_address": "{{ hotspot_address }}",
        "dns_name": "{{ dns_name }}"
    }
}
```

### Available Placeholders

- `{{ central_server_ip }}` - Central management server
- `{{ radius_server }}` - RADIUS server IP
- `{{ radius_secret }}` - RADIUS shared secret
- `{{ system_identity }}` - Router identity
- `{{ hotspot_address }}` - Hotspot gateway IP
- `{{ dns_name }}` - Hotspot DNS name
- `{{ pppoe_pool_start }}` - PPPoE IP pool start
- `{{ pppoe_pool_end }}` - PPPoE IP pool end
- `{{ timezone }}` - System timezone
- `{{ ntp_server }}` - NTP server address

### Template Types

1. **full_provisioning**: Complete router setup
2. **radius**: RADIUS authentication only
3. **hotspot**: Hotspot configuration
4. **pppoe**: PPPoE server setup
5. **firewall**: Firewall rules only
6. **system**: System settings only
7. **nat**: NAT rules configuration
8. **walled_garden**: Walled garden entries
9. **suspended_pool**: Suspended users pool

## API Endpoints

### Router Operations
- `GET /panel/admin/routers/provision` - Provisioning interface
- `GET /panel/admin/routers/provision/{routerId}` - Router-specific provisioning
- `POST /panel/admin/routers/provision/test-connection` - Test connectivity
- `POST /panel/admin/routers/provision/backup` - Create backup

### Provisioning Operations
- `POST /panel/admin/routers/provision/preview` - Preview configuration
- `POST /panel/admin/routers/provision/execute` - Execute provisioning
- `POST /panel/admin/routers/provision/rollback` - Rollback configuration

### Template Management
- `GET /panel/admin/routers/provision/templates/manage` - List templates
- `GET /panel/admin/routers/provision/templates/create` - Create template form
- `POST /panel/admin/routers/provision/templates` - Store new template
- `GET /panel/admin/routers/provision/templates/{id}` - Get template details

### Logs & History
- `GET /panel/admin/routers/provision/{routerId}/logs` - Provisioning logs
- `GET /panel/admin/routers/provision/{routerId}/backups` - Backup history

## Database Schema

### router_configuration_templates
Stores reusable configuration templates:
- `name`: Template name
- `description`: Template description
- `template_type`: Type of configuration
- `configuration`: JSON configuration with placeholders
- `is_default`: Default template flag
- `is_active`: Active status

### router_provisioning_logs
Tracks all provisioning actions:
- `router_id`: Target router
- `user_id`: User who initiated
- `template_id`: Template used
- `action`: provision, rollback, validate, backup
- `status`: pending, in_progress, success, failed
- `steps`: Array of executed steps
- `error_message`: Failure details

### router_configuration_backups
Stores configuration backups:
- `router_id`: Router identifier
- `backup_data`: Configuration export
- `backup_type`: manual, pre_provisioning, scheduled
- `created_by`: User who created backup
- `created_at`: Backup timestamp

## Security Considerations

### Access Control
- Feature requires admin role
- Tenant isolation enforced on all operations
- Template access restricted by tenant

### Data Protection
- Router credentials encrypted at rest (Laravel encrypted casting)
- Backup data stored securely in database
- Audit trail for all provisioning actions

### Communication Security
- Uses HTTP for router API (suitable for internal networks)
- For production with untrusted networks, configure HTTPS
- RADIUS secrets stored in database (restrict access via RBAC)

### Validation
- Router connectivity verified before provisioning
- RADIUS server reachability tested
- Configuration validated after applying
- Automatic rollback on critical failures

## Troubleshooting

### Router Connectivity Issues
**Problem**: "Cannot connect to router"
- Verify router IP address is correct
- Check router API port (default: 8728)
- Ensure router is powered on and network is reachable
- Verify firewall allows connections to router API

### Provisioning Fails
**Problem**: Provisioning completes with errors
- Check provisioning logs for specific error
- Verify RADIUS server is reachable from router
- Ensure template configuration is valid
- Use rollback to restore previous configuration

### RADIUS Not Working
**Problem**: RADIUS authentication fails after provisioning
- Verify RADIUS server IP is correct
- Check RADIUS secret matches between router and server
- Test RADIUS connectivity from router
- Verify RADIUS ports 1812/1813 are open

### Template Variables Not Replaced
**Problem**: Variables like {{ radius_server }} appear in config
- Ensure variable names match exactly (case-sensitive)
- Check template JSON syntax is valid
- Verify all required variables are provided

## Best Practices

1. **Always Create Backup**: Before provisioning, create a manual backup
2. **Test on Non-Production**: Test templates on development routers first
3. **Use Default Templates**: Start with default templates, customize as needed
4. **Document Changes**: Use template descriptions to document customizations
5. **Monitor Logs**: Review provisioning logs after each operation
6. **Validate Configuration**: Always test router functionality after provisioning
7. **Keep Backups**: Maintain multiple backups for critical routers

## Seeding Default Templates

To populate default templates:

```bash
php artisan db:seed --class=RouterConfigurationTemplateSeeder
```

This creates 5 pre-configured templates:
- Full ISP Provisioning
- RADIUS Only
- Hotspot Profile
- PPPoE Server
- System Configuration

## Support

For issues or questions:
1. Check provisioning logs in the interface
2. Review router logs via RouterOS interface
3. Verify template configuration syntax
4. Contact system administrator

## Related Documentation

- [MIKROTIK_QUICKSTART.md](MIKROTIK_QUICKSTART.md) - MikroTik integration guide
- [MIKROTIK_ADVANCED_FEATURES.md](MIKROTIK_ADVANCED_FEATURES.md) - Advanced MikroTik features
- [HOTSPOT_SELF_SIGNUP_GUIDE.md](HOTSPOT_SELF_SIGNUP_GUIDE.md) - Hotspot configuration
