# Super Admin Guide - ISP Solution

## Role Overview

**Level**: 10  
**Access**: Only tenants you created

As a Super Admin, you manage admins within your own tenants. You can create new ISP tenant administrators and oversee their operations.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Admins](#managing-admins)
4. [Tenant Management](#tenant-management)
5. [System Monitoring](#system-monitoring)
6. [Reports & Analytics](#reports--analytics)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

## Getting Started

### First Login

1. Navigate to your ISP Solution URL
2. Use your Super Admin credentials:
   - **Demo Email**: superadmin@ispbills.com
   - **Password**: password (change immediately!)

### Initial Setup Tasks

1. **Change Your Password**
   - Go to Profile → Security
   - Set a strong password
   - Enable Two-Factor Authentication (2FA)

2. **Create Your First Admin**
   - Navigate to Users → Create Admin
   - Fill in ISP details
   - Assign appropriate permissions

3. **Configure Notifications**
   - Go to Settings → Notifications
   - Set email preferences
   - Configure alert thresholds

## Dashboard Overview

### Key Metrics

Your dashboard displays:
- **Total Tenants**: Number of ISP tenants you manage
- **Active Admins**: Currently active ISP administrators
- **System Health**: Overall platform status
- **Recent Activities**: Latest actions across all your tenants

### Quick Actions

- Create new Admin
- View all tenants
- Generate reports
- System health check
- Support tickets

## Managing Admins

### Creating a New Admin (ISP Owner)

1. Navigate to **Users → Create Admin**
2. Fill in the form:
   ```
   Name: ISP Name or Owner Name
   Email: admin@ispdomain.com
   Phone: Contact number
   Tenant Name: ISP Company Name
   Status: Active
   ```
3. Click **Create Admin**
4. Admin receives welcome email with credentials

### Viewing Admin Details

1. Go to **Users → Admins**
2. Click on any admin to view:
   - Contact information
   - Tenant details
   - Activity history
   - Customers count
   - Revenue statistics
   - Service packages

### Editing Admin Information

1. Navigate to admin profile
2. Click **Edit**
3. Update information
4. Save changes

**Note**: You can only edit admins within your own tenants.

### Suspending/Deactivating Admins

1. Go to admin profile
2. Click **Actions → Suspend**
3. Provide reason
4. Confirm suspension

**Effect**: Admin loses access immediately; all their customers remain active but cannot make changes.

### Deleting Admins

⚠️ **Warning**: Deleting an admin is permanent and affects all their data.

1. Navigate to admin profile
2. Click **Actions → Delete**
3. Choose data handling:
   - Migrate customers to another admin
   - Archive all data
4. Confirm deletion

## Tenant Management

### Creating New Tenants

1. Go to **Tenants → Create**
2. Fill in tenant information:
   ```
   Tenant Name: ISP Company Name
   Domain: isp-subdomain
   Admin Name: Primary admin name
   Admin Email: admin@example.com
   Package Limit: Number of packages allowed
   Customer Limit: Maximum customers
   ```
3. Click **Create Tenant**

### Viewing Tenant Details

Each tenant card shows:
- **Tenant Name**
- **Admin Count**
- **Customer Count**
- **Revenue (MTD)**
- **Status**
- **Created Date**

### Tenant Statistics

Access detailed statistics:
1. Click on tenant
2. View tabs:
   - **Overview**: Key metrics
   - **Customers**: Customer list
   - **Revenue**: Financial data
   - **Activities**: Recent actions
   - **Settings**: Tenant configuration

### Managing Tenant Limits

1. Go to tenant details
2. Click **Settings**
3. Update limits:
   - Maximum customers
   - Maximum packages
   - Bandwidth caps
   - Storage limits
4. Save changes

## System Monitoring

### Health Dashboard

Monitor system health:
- **Server Status**: Application server uptime
- **Database**: Connection and performance
- **RADIUS**: Authentication server status
- **Redis**: Cache and queue status

### Performance Metrics

Track performance indicators:
- Response times
- Database queries
- Active sessions
- Queue jobs
- Memory usage

### Alerts & Notifications

Configure alerts for:
- System downtime
- High CPU/memory usage
- Failed authentication attempts
- Database connection issues
- RADIUS server problems

## Reports & Analytics

### Available Reports

1. **Tenant Summary**
   - List all tenants
   - Customer counts
   - Revenue breakdown
   - Growth trends

2. **Admin Activity**
   - Login history
   - Actions performed
   - Changes made
   - Time tracking

3. **System Usage**
   - Platform utilization
   - Resource consumption
   - Peak usage times
   - Performance trends

### Generating Reports

1. Go to **Reports**
2. Select report type
3. Choose date range
4. Apply filters
5. Click **Generate**
6. Export as PDF or Excel

### Scheduled Reports

Set up automatic reports:
1. Go to **Reports → Schedule**
2. Select report type
3. Choose frequency (Daily/Weekly/Monthly)
4. Add recipients
5. Save schedule

## Best Practices

### Security

✅ **DO**:
- Enable 2FA on your account
- Use strong, unique passwords
- Regularly review admin access
- Monitor suspicious activities
- Keep system updated

❌ **DON'T**:
- Share your credentials
- Use simple passwords
- Leave sessions open
- Ignore security alerts
- Delay security updates

### Admin Management

✅ **Best Practices**:
- Verify admin identity before creation
- Document tenant information
- Regular access reviews
- Maintain communication
- Monitor admin activities

### Performance

- Regular system health checks
- Monitor resource usage
- Clean up inactive tenants
- Archive old data
- Optimize database

### Communication

- Respond to admin requests promptly
- Provide clear documentation
- Share system updates
- Conduct regular check-ins
- Maintain support channels

## Troubleshooting

### Common Issues

#### Cannot Create Admin

**Problem**: "Permission denied" error when creating admin

**Solution**:
1. Verify your role level (should be 10)
2. Check tenant limits
3. Clear browser cache
4. Contact Developer if issue persists

#### Tenant Not Loading

**Problem**: Tenant details page shows error

**Solution**:
1. Refresh the page
2. Check internet connection
3. Verify tenant exists
4. Check browser console for errors
5. Report to technical support

#### Reports Not Generating

**Problem**: Report generation fails or hangs

**Solution**:
1. Check date range (avoid very large ranges)
2. Reduce filters
3. Try exporting smaller datasets
4. Clear cache and retry
5. Contact support if problem continues

### Getting Help

#### Self-Service Resources

1. **Documentation**: Check `docs/` folder
2. **FAQ**: Common questions and answers
3. **Video Tutorials**: Step-by-step guides
4. **Knowledge Base**: Searchable articles

#### Support Channels

1. **Support Ticket**: Create ticket in system
2. **Email**: support@example.com
3. **Phone**: +1-xxx-xxx-xxxx (business hours)
4. **Emergency**: emergency@example.com (critical issues only)

### System Status

Check system status:
- Visit status page: status.example.com
- Subscribe to status updates
- Follow maintenance schedule

## Account Management

### Profile Settings

Update your profile:
1. Click on profile icon
2. Select **Settings**
3. Update information
4. Save changes

### Changing Password

1. Go to **Profile → Security**
2. Click **Change Password**
3. Enter current password
4. Enter new password (must be strong)
5. Confirm new password
6. Save

### Two-Factor Authentication (2FA)

Enable 2FA for security:
1. Go to **Profile → Security**
2. Click **Enable 2FA**
3. Scan QR code with authenticator app
4. Enter verification code
5. Save backup codes securely

### Session Management

Manage active sessions:
1. Go to **Profile → Security → Sessions**
2. View all active sessions
3. Revoke suspicious sessions
4. Enable "Single Device" mode if needed

## Keyboard Shortcuts

Speed up your workflow:

- `Ctrl/Cmd + K`: Quick search
- `Ctrl/Cmd + N`: Create new admin
- `Ctrl/Cmd + S`: Save changes
- `Esc`: Close modal/dialog
- `/`: Focus search bar

## Additional Resources

### Documentation

- [Admin Guide](ADMIN_GUIDE.md) - For ISP admins you create
- [API Documentation](../API.md)
- [System Architecture](../technical/ARCHITECTURE.md)

### Training

- Video tutorials available in Help Center
- Webinar schedule in dashboard
- One-on-one training sessions available

### Updates

- Check **Announcements** for platform updates
- Subscribe to changelog notifications
- Review release notes before updates

## Contact Information

### Technical Support

- **Email**: support@example.com
- **Phone**: +1-xxx-xxx-xxxx
- **Hours**: Monday-Friday, 9 AM - 6 PM EST

### Developer Access

For critical system issues:
- **Emergency Email**: emergency@example.com
- **Response Time**: < 1 hour for critical issues

### Feedback

We value your feedback:
- **Feature Requests**: features@example.com
- **Bug Reports**: bugs@example.com
- **General Feedback**: feedback@example.com

## Version Information

- **Guide Version**: 1.0
- **Last Updated**: January 2026
- **Platform Version**: 3.1

---

**Need Help?** Contact support or check the documentation at `docs/` folder in your installation.
