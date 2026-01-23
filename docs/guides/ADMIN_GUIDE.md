# Admin Guide - ISP Solution

## Role Overview

**Level**: 20 (ISP Owner)  
**Access**: All data within your ISP tenant

As an Admin (ISP Owner), you manage your Internet Service Provider business. You can create operators, sub-operators, manage customers, service packages, billing, and all aspects of your ISP operations.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Customer Management](#customer-management)
4. [Service Packages](#service-packages)
5. [Operators & Staff](#operators--staff)
6. [Billing & Payments](#billing--payments)
7. [Network Services](#network-services)
8. [Reports](#reports)
9. [Settings](#settings)
10. [Support](#support)

## Getting Started

### First Login

1. Access your ISP Solution URL
2. Login with credentials:
   - **Demo Email**: admin@ispbills.com
   - **Password**: password (change immediately!)

### Quick Setup Wizard

After first login, complete the setup wizard:

1. **Company Profile**
   - Company name
   - Logo upload
   - Contact information
   - Business address

2. **Service Packages**
   - Create your first package
   - Set pricing
   - Configure bandwidth

3. **Network Configuration**
   - Configure RADIUS server
   - Add MikroTik routers
   - Setup IP pools

4. **Payment Gateway**
   - Configure payment methods
   - Set tax rates
   - Define billing cycle

## Dashboard

### Overview Metrics

Your dashboard displays:

- **Total Customers**: Active subscribers
- **Monthly Revenue**: Current month earnings
- **Active Sessions**: Online users now
- **Pending Payments**: Outstanding invoices
- **New Signups**: This month
- **Bandwidth Usage**: Total consumed

### Quick Actions

Common tasks accessible from dashboard:
- Add new customer
- Create invoice
- View active sessions
- Generate report
- Disconnect user
- Send notification

### Recent Activities

Track recent actions:
- New customer registrations
- Payment received
- Service activations
- Support tickets
- System alerts

## Customer Management

### Adding New Customer

1. Navigate to **Customers → Add New**
2. Fill in customer details:
   ```
   Personal Information:
   - Full Name
   - Email
   - Phone Number
   - National ID/Passport
   
   Address:
   - Street Address
   - City
   - Postal Code
   - Coordinates (optional)
   
   Connection Details:
   - Service Package
   - Connection Type (PPPoE/Hotspot/Static IP)
   - Installation Address
   - Installation Date
   
   Account Information:
   - Username (auto-generated or custom)
   - Password
   - MAC Address (for Hotspot)
   - IP Address (for Static IP)
   ```
3. Click **Create Customer**

### Customer Profile

View complete customer information:

**Account Tab**:
- Personal details
- Contact information
- Connection status
- Package details

**Billing Tab**:
- Current balance
- Payment history
- Invoices
- Transaction log

**Usage Tab**:
- Bandwidth consumption
- Session history
- Peak usage times
- Data statistics

**Support Tab**:
- Ticket history
- Notes
- Communication log
- Service issues

### Managing Customers

**Change Package**:
1. Go to customer profile
2. Click **Change Package**
3. Select new package
4. Choose effective date
5. Confirm change

**Suspend Service**:
1. Open customer profile
2. Click **Actions → Suspend**
3. Select reason
4. Set suspension period
5. Confirm

**Activate Service**:
1. Go to suspended customer
2. Click **Actions → Activate**
3. Confirm activation

**Disconnect Session**:
1. View active session
2. Click **Disconnect**
3. Confirm action

### Bulk Operations

Manage multiple customers:
1. Go to **Customers**
2. Select customers (checkbox)
3. Choose bulk action:
   - Send notification
   - Change package
   - Suspend/Activate
   - Generate invoices
   - Export data

## Service Packages

### Creating Packages

1. Navigate to **Packages → Create**
2. Fill in package details:
   ```
   Basic Information:
   - Package Name (e.g., "Home 10Mbps")
   - Description
   - Package Type (Broadband/Hotspot)
   
   Bandwidth:
   - Download Speed (Mbps)
   - Upload Speed (Mbps)
   - Data Cap (GB) - optional
   
   Pricing:
   - Monthly Price
   - Installation Fee
   - Setup Fee
   
   RADIUS Attributes:
   - Profile Name
   - Session Timeout
   - Idle Timeout
   ```
3. Click **Create Package**

### Managing Packages

**Edit Package**:
1. Go to **Packages**
2. Click on package
3. Update details
4. Save changes

**Deactivate Package**:
1. Open package
2. Click **Deactivate**
3. Existing customers remain unaffected
4. New customers cannot subscribe

**Package Pricing**:
- Set promotional pricing
- Configure discounts
- Bulk pricing for operators
- Seasonal offers

## Operators & Staff

### Team Structure

Your team hierarchy:
- **You (Admin)**: Full control
- **Operators**: Manage assigned areas/customers
- **Sub-Operators**: Limited customer management
- **Managers**: View/edit permissions
- **Staff**: View-only or specific permissions
- **Accountants**: Financial access only

### Creating Operator

1. Go to **Team → Add Operator**
2. Fill in details:
   ```
   Personal Information:
   - Name
   - Email
   - Phone
   
   Access Rights:
   - Assigned Area/Zone
   - Customer Limit
   - Can Create Sub-Operators: Yes/No
   
   Permissions:
   - Create Customers
   - Modify Packages
   - Process Payments
   - Generate Reports
   ```
3. Click **Create Operator**

### Managing Staff

**Create Manager/Staff**:
1. Navigate to **Team → Add Staff**
2. Select role:
   - Manager
   - Accountant
   - Staff
3. Assign permissions:
   - View customers
   - Edit customers
   - View billing
   - Process payments
   - View reports
4. Save

**Edit Permissions**:
1. Go to staff profile
2. Click **Permissions**
3. Check/uncheck permissions
4. Save changes

### Team Performance

Monitor team activities:
- Customer acquisitions per operator
- Revenue per operator
- Collection efficiency
- Response time
- Customer satisfaction

## Billing & Payments

### Invoice Generation

**Manual Invoice**:
1. Go to **Billing → Create Invoice**
2. Select customer
3. Add items:
   - Service charges
   - Late fees
   - One-time charges
4. Set due date
5. Generate invoice

**Automatic Invoices**:
1. Configure in **Settings → Billing**
2. Set generation schedule
3. Enable auto-send
4. Configure reminders

### Payment Collection

**Record Payment**:
1. Go to **Billing → Record Payment**
2. Select customer
3. Enter amount
4. Select payment method:
   - Cash
   - Bank transfer
   - Card
   - Mobile money
5. Upload receipt (optional)
6. Save

**Payment Methods**:
Configure accepted payment methods:
1. **Settings → Payment Methods**
2. Enable/disable methods
3. Set gateway credentials
4. Configure payment fees

### Payment Gateway Integration

**Supported Gateways**:
- Stripe
- PayPal
- Razorpay
- Local payment gateways

**Setup Process**:
1. Go to **Settings → Payment Gateways**
2. Select gateway
3. Enter API credentials
4. Configure webhook
5. Test connection
6. Enable gateway

### Financial Reports

Access financial reports:
- Monthly revenue
- Outstanding payments
- Payment collection
- Revenue by package
- Operator collections
- Tax reports

## Network Services

### RADIUS Configuration

**Setup RADIUS**:
1. Go to **Settings → Network → RADIUS**
2. Enter details:
   ```
   RADIUS Server:
   - Host: IP address
   - Port: 1812 (auth), 1813 (acct)
   - Secret: Shared secret
   ```
3. Test connection
4. Save configuration

**Sync Customers to RADIUS**:
1. Go to **Network → RADIUS**
2. Click **Sync All Customers**
3. Monitor progress
4. Verify synchronization

### MikroTik Integration

**Add MikroTik Router**:
1. Navigate to **Network → Routers → Add**
2. Fill in details:
   ```
   Router Information:
   - Name/Location
   - IP Address
   - API Port (default: 8728)
   - Username
   - Password
   
   Settings:
   - Default Profile
   - Connection Type (PPPoE/Hotspot)
   - Zone/Area
   ```
3. Test connection
4. Save router

**Manage Routers**:
- View router status
- Monitor active sessions
- Configure PPPoE profiles
- Manage IP pools
- Disconnect sessions
- Router health check

### IP Address Management (IPAM)

**Create IP Pool**:
1. Go to **Network → IP Pools → Create**
2. Enter details:
   ```
   Pool Information:
   - Pool Name
   - Network (CIDR notation)
   - Gateway
   - DNS Servers
   ```
3. Save pool

**Assign IP Address**:
1. Go to customer profile
2. Navigate to **Network** tab
3. Click **Assign IP**
4. Select from pool or enter static IP
5. Save assignment

### Session Monitoring

**View Active Sessions**:
1. Go to **Network → Sessions**
2. View real-time data:
   - Customer name
   - Username
   - IP address
   - Upload/download speeds
   - Session duration
   - Data consumed

**Disconnect Session**:
1. Find session
2. Click **Disconnect**
3. Confirm action

## Reports

### Available Reports

1. **Customer Reports**
   - Active customers
   - New registrations
   - Churned customers
   - Customer by package
   - Customer by area

2. **Financial Reports**
   - Revenue summary
   - Payment collection
   - Outstanding dues
   - Revenue by operator
   - Tax summary

3. **Usage Reports**
   - Bandwidth usage
   - Top users
   - Session statistics
   - Peak usage times
   - Data consumption trends

4. **Network Reports**
   - Active sessions
   - Router performance
   - Authentication failures
   - Connection uptime

### Generating Reports

1. Navigate to **Reports**
2. Select report type
3. Set parameters:
   - Date range
   - Filters (area, operator, package)
   - Sort order
4. Click **Generate**
5. View online or export (PDF/Excel/CSV)

### Scheduled Reports

Automate reports:
1. Go to **Reports → Schedule**
2. Select report
3. Set frequency
4. Add email recipients
5. Enable schedule

## Settings

### Company Settings

**Profile**:
1. **Settings → Company**
2. Update:
   - Company name
   - Logo
   - Contact details
   - Tax information
   - Business hours

**Branding**:
- Upload logo
- Set color scheme
- Customize email templates
- Configure customer portal

### System Configuration

**General Settings**:
- Time zone
- Date format
- Currency
- Language
- Notification preferences

**Security Settings**:
- Password policy
- Session timeout
- Two-factor authentication
- IP whitelisting
- Login attempts

**Email Configuration**:
- SMTP settings
- Email templates
- Sender information
- Notification rules

### Backup & Maintenance

**Backup**:
1. Go to **Settings → Backup**
2. Click **Create Backup**
3. Wait for completion
4. Download backup file

**Maintenance Mode**:
1. **Settings → Maintenance**
2. Enable maintenance mode
3. Set message for users
4. Perform maintenance
5. Disable maintenance mode

## Support

### Customer Support Tickets

**View Tickets**:
1. Navigate to **Support → Tickets**
2. Filter by status/priority
3. Click ticket to view details

**Respond to Ticket**:
1. Open ticket
2. Type response
3. Attach files if needed
4. Click **Reply**

**Close Ticket**:
1. Open ticket
2. Add final response
3. Click **Close Ticket**

### System Support

Need help with the platform?

**Documentation**:
- Check user guides in `docs/` folder
- API documentation
- Video tutorials
- FAQ section

**Contact Support**:
- **Email**: support@example.com
- **Phone**: +1-xxx-xxx-xxxx
- **Hours**: 24/7 for critical issues

### Training Resources

- Online video tutorials
- Webinar recordings
- Best practices guide
- Community forum

## Keyboard Shortcuts

Boost your productivity:

- `Ctrl/Cmd + K`: Quick search
- `Ctrl/Cmd + N`: Add new customer
- `Ctrl/Cmd + P`: Generate report
- `/`: Focus search
- `Esc`: Close dialog

## Best Practices

### Customer Management
- Verify customer identity before activation
- Document installation details
- Set realistic expectations
- Regular follow-ups
- Maintain communication

### Financial Management
- Generate invoices on time
- Send payment reminders
- Process payments promptly
- Reconcile accounts monthly
- Monitor outstanding dues

### Network Management
- Regular router health checks
- Monitor bandwidth usage
- Update firmware regularly
- Document network changes
- Plan for capacity growth

## Troubleshooting

### Common Issues

**Customer Cannot Connect**:
1. Verify customer is active
2. Check RADIUS synchronization
3. Verify router configuration
4. Test credentials
5. Check IP allocation

**Payment Not Reflecting**:
1. Check payment gateway status
2. Verify transaction ID
3. Check webhook logs
4. Manual reconciliation
5. Contact gateway support

**Report Not Generating**:
1. Check date range
2. Reduce filters
3. Clear cache
4. Try smaller dataset
5. Contact support

## Additional Resources

- [Operator Guide](OPERATOR_GUIDE.md)
- [Sub-Operator Guide](SUBOPERATOR_GUIDE.md)
- [API Documentation](../API.md)
- [Network Services Guide](../NETWORK_SERVICES.md)

---

**Version**: 1.0  
**Last Updated**: January 2026  
**Need Help?** Contact support@example.com
