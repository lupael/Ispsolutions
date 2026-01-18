# ISP Solution - Complete Panel Specifications

**Version:** 2.0  
**Last Updated:** 2026-01-18  
**Status:** Implementation Complete

---

## Overview

This document provides comprehensive specifications for all user panels in the ISP Solution system. Each panel is designed with role-specific access controls, features, and restrictions to ensure proper security and workflow management.

---

## Table of Contents

1. [Super Admin Panel](#1-super-admin-panel)
2. [Admin Panel](#2-admin-panel)
3. [Operator Panel](#3-operator-panel)
4. [Sub-Operator Panel](#4-sub-operator-panel)
5. [Manager Panel](#5-manager-panel)
6. [Card Distributor Portal](#6-card-distributor-portal)
7. [Developer Panel](#7-developer-panel)
8. [Accountant Panel](#8-accountant-panel)
9. [Customer Panel](#9-customer-panel)

---

## 1. Super Admin Panel

### Access Level
- **Highest Authority**: Full system-wide access
- **Scope**: All tenant features without restrictions
- **URL Prefix**: `/panel/super-admin/*`

### Main Sections

#### 1.1 Dashboard
- Tenant-wide metrics and analytics
- System health monitoring
- Quick stats across all tenants
- Revenue overview

#### 1.2 Tenant Management
- View all ISP/tenants
- Create new tenants
- Manage tenant status (active/inactive)
- Configure tenant billing plans

#### 1.3 Admin Management
- View all tenant administrators
- Create/edit admin users
- Assign roles and permissions
- Manage admin access levels

#### 1.4 Subscription Management
- Fixed billing plans
- User-based billing
- Panel-based billing
- Subscription renewals and upgrades

#### 1.5 Global Configuration
- System-wide settings
- Payment gateway configuration (all tenants)
- SMS gateway configuration (all tenants)
- System defaults and limits

#### 1.6 System Logs and Monitoring
- Application logs
- Error tracking
- Performance metrics
- Security events
- Audit trails

#### 1.7 All Features from Lower-Level Panels
- Can access any tenant's panel
- Override permissions when needed
- System-wide reporting

### Key Features
- Multi-tenancy management
- Global system configuration
- Cross-tenant analytics
- Subscription billing management
- System-wide monitoring and alerts

### Restrictions
- None - full system access

---

## 2. Admin Panel

### Access Level
- **Full Administrative Access**: Complete control over their ISP/tenant
- **Scope**: Single tenant operations
- **URL Prefix**: `/panel/admin/*`

### Main Menu Sections

#### 2.1 Dashboard
- Overview with widgets and charts
- Revenue metrics
- Customer statistics
- Network status
- Quick actions

#### 2.2 Resellers & Managers
**Controllable Menu** - Can be disabled per operator
- **Operators**: Create and manage operator accounts
- **Sub-Operators**: Manage sub-level operators
- **Managers**: Task-specific manager accounts
- Configure disabled menus for each operator
- Set permissions and access levels

#### 2.3 Routers & Packages
**Controllable Menu** - Can be disabled per operator
- **Master Packages**: Service package definitions
- **PPPoE Profiles**: RADIUS profile management
- **NAS Management**: Network Access Server configuration
- **Router Management**: MikroTik, Cisco, OLT devices
- IP pool management

#### 2.4 Recharge Cards
**Controllable Menu** - Can be disabled per operator
- **Card Generation**: Create prepaid recharge cards
- **Distributor Management**: Manage card distributors
- Card batch tracking
- Usage reports

#### 2.5 Customers
**Controllable Menu** - Can be disabled per operator
- **Full Customer Management**:
  - All customers list
  - Online customers
  - Offline customers
  - Import customers (PPPoE, bulk)
  - Customer zones/areas
  - Suspended/expired accounts
  - Customer profiles

#### 2.6 Bills & Payments
**Controllable Menu** - Can be disabled per operator
- **Billing**: Invoice generation and management
- **Payment Verification**: Process and verify payments
- **Due Notifications**: Automated reminders via SMS/email
- Payment gateway integration
- Manual payment recording

#### 2.7 Incomes & Expenses
**Controllable Menu** - Can be disabled per operator
- **Financial Tracking**:
  - Transaction history
  - Expense management
  - Income/Expense reports
  - Account statements
  - Payable/Receivable tracking

#### 2.8 Complaints & Support
- **Ticket Management**: Customer support tickets
- **Categories**: Ticket categorization
- Assignment and escalation
- Status tracking
- Response templates

#### 2.9 Reports
- **BTRC Reports**: Regulatory compliance reports (Bangladesh)
- **Financial Reports**: Revenue, collections, outstanding
- **Customer Reports**: Growth, churn, demographics
- Export functionality (PDF, Excel)

#### 2.10 Affiliate Program
**Controllable Menu** - Can be disabled per operator
- **Referral Tracking**: Customer referral system
- **Commission Tracking**: Multi-level commissions
- Payout management
- Performance metrics

#### 2.11 VAT Management
**Controllable Menu** - Can be disabled per operator
- **Tax Profiles**: VAT/tax configuration
- **Collections**: VAT collection tracking
- Tax reports
- Compliance documentation

#### 2.12 SMS Services
- **Gateway Configuration**: SMS provider setup
- **Broadcasting**: Bulk SMS campaigns
- **SMS History**: Delivery tracking
- Event-based SMS triggers
- Due date notifications

#### 2.13 Configuration
- **Billing Profiles**: Billing cycles and rules
- **Custom Fields**: Custom customer/package fields
- **Devices**: Network device configuration
- System preferences
- Integration settings

#### 2.14 Activity Logs
- **Audit Trail**: All administrative actions
- **Authentication Logs**: Login/logout history
- User activity tracking
- Security events
- Change history

### Controllable Menus
The following menus can be disabled per operator using the `disabled_menus` field:
1. `resellers_managers` - Resellers & Managers menu
2. `routers_packages` - Routers & Packages menu
3. `recharge_cards` - Recharge Card menu
4. `customers` - Customer menu
5. `bills_payments` - Bills & Payments menu
6. `incomes_expenses` - Incomes & Expenses menu
7. `affiliate_program` - Affiliate Program menu
8. `vat_management` - VAT menu

### Key Features
- Complete ISP management
- Operator permission control
- Financial management
- Network device integration
- Customer lifecycle management
- Reporting and analytics

### Restrictions
- Limited to single tenant
- Cannot access other tenants
- Cannot modify global system settings
- Cannot access super admin features

---

## 3. Operator Panel

### Access Level
- **Restricted Panel**: Based on menu configuration by Admin
- **Scope**: Assigned customers and features only
- **URL Prefix**: `/panel/operator/*`

### Main Sections

#### 3.1 Dashboard
- Operator-specific metrics
- Assigned customer statistics
- Payment collection summary
- Performance indicators

#### 3.2 Sub-Operator Management
- Create sub-operators (if enabled)
- Manage assigned sub-operators
- Track sub-operator performance

#### 3.3 Customer Management
- View assigned customers only
- Add new customers (if enabled)
- Customer profile management
- Connection management
- Cannot access other operators' customers

#### 3.4 Bills and Payments
- Process payments for own customers
- View billing history
- Generate invoices (if enabled)
- Payment collection tracking

#### 3.5 Recharge Cards
- View/use recharge cards (if enabled)
- Card distribution (if authorized)
- Cannot generate cards

#### 3.6 Complaints
- Handle tickets for own customers
- Submit and track complaints
- Response management

#### 3.7 Reports
- Limited to own data
- Customer reports
- Collection reports
- Performance metrics

#### 3.8 SMS
- Send SMS to own customers
- View SMS history (own customers)
- Cannot broadcast to all customers

### Restrictions
- **Cannot create Admins or Operators**: Only sub-operators if enabled
- **Cannot access other operators' data**: Strict data isolation
- **Cannot modify system configurations**: Network devices, packages, billing rules
- **Menu visibility controlled by `disabled_menus`**: Admin configures available menus

### Data Isolation
- Operator Level: 30 (lower than Admin's 20)
- Can only see customers created by themselves or their sub-operators
- Cannot modify packages or network settings

---

## 4. Sub-Operator Panel

### Access Level
- **Further Restricted**: Subset of Operator panel
- **Scope**: Assigned customer subset only
- **URL Prefix**: `/panel/sub-operator/*`

### Main Sections

#### 4.1 Dashboard
- Limited metrics (own customers only)
- Payment collection summary
- Basic statistics

#### 4.2 Customer Management
- View assigned subset of customers only
- Basic customer operations
- Connection status monitoring
- Cannot access operator's full customer base

#### 4.3 Bills and Payments
- Process payments for assigned customers
- View payment history
- Basic invoice viewing

#### 4.4 Basic Reports
- Collection reports
- Customer activity
- Own performance metrics

### Restrictions
- **Cannot create any operators**: No user management
- **Cannot manage packages or profiles**: Read-only network config
- **Limited to assigned customers only**: Strict subset isolation
- **Most administrative features disabled**: Minimal operational access

### Data Isolation
- Operator Level: 40 (lower than Operator's 30)
- Reports to parent Operator
- Cannot see other sub-operators' data

---

## 5. Manager Panel

### Access Level
- **Task-Specific Panel**: Permission-based feature access
- **Scope**: Group metrics and assigned permissions
- **URL Prefix**: `/panel/manager/*`

### Main Sections

#### 5.1 Dashboard
- Group metrics (department or team)
- Assigned task overview
- Performance indicators
- Quick actions

#### 5.2 Customer Viewing
- View customers based on permissions
- Can view operators' or sub-operators' customers
- Read-only access (typically)
- Search and filter capabilities

#### 5.3 Payment Processing
- Process customer payments (if authorized)
- Verify payments
- Record transactions
- Payment history viewing

#### 5.4 Complaint Management
- Assigned department tickets
- Ticket assignment and escalation
- Response management
- Status tracking

#### 5.5 Basic Reports
- Department-level reports
- Customer satisfaction metrics
- Resolution statistics
- Performance reports

### Restrictions
- **Cannot modify operators or sub-operators**: No user hierarchy management
- **Cannot modify packages or configurations**: No system changes
- **Can view operators or sub-operators customers**: Read-only access
- **Limited to assigned permissions**: Granular permission control

### Permission Model
- Operator Level: 50
- Permission-based feature access
- Can be assigned specific departments or teams
- View-only access to most features

---

## 6. Card Distributor Portal

### Access Level
- **Separate Portal**: Not part of admin panel
- **Scope**: Card inventory and sales only
- **URL Prefix**: `/card-distributors/*` (separate routing)

### Main Sections

#### 6.1 Dashboard
- Card inventory summary
- Sales statistics
- Commission overview
- Recent transactions

#### 6.2 Card Inventory View
- Available cards by denomination
- Sold cards
- Expired cards
- Card status tracking

#### 6.3 Sales Tracking
- Transaction history
- Card redemption tracking
- Customer information (limited)
- Sales by period

#### 6.4 Commission Reports
- Earned commissions
- Pending payouts
- Commission history
- Performance metrics

#### 6.5 Payment History
- Distributor account balance
- Payment received history
- Outstanding balance
- Settlement records

### Restrictions
- **Read-only access**: Cannot modify system data
- **No customer management**: Cannot create or modify customers
- **No administrative features**: Limited to card operations only
- **Separate portal**: Isolated from main admin panel

### Key Features
- Card inventory management (view only)
- Sales tracking and reporting
- Commission calculation
- Simple, focused interface

---

## 7. Developer Panel

### Access Level
- **Source Code Owner**: Technical configuration access
- **Scope**: System infrastructure and API
- **URL Prefix**: `/panel/developer/*`

### Main Sections

#### 7.1 Dashboard
- System overview
- API usage metrics
- Performance indicators
- Recent activities

#### 7.2 Tenant Management
- View all tenants
- Create/modify tenants
- Tenant database management
- Tenant configuration

#### 7.3 Subscription Management
- Billing plan configuration
- Subscription tracking
- Payment integration testing
- License management

#### 7.4 Global Configuration
- System-wide settings
- Feature flags
- Environment configuration
- System limits and quotas

#### 7.5 System Logs and Monitoring
- Application logs
- Error tracking
- Performance monitoring
- Database query logs
- Queue monitoring

#### 7.6 SMS Gateway Configuration
- Provider setup and testing
- API credential management
- Delivery rate monitoring
- Test message sending

#### 7.7 Payment Gateway Configuration
- Payment provider integration
- Sandbox/production toggle
- Transaction testing
- Webhook configuration

#### 7.8 VPN Pools
- VPN server management
- IP pool allocation
- Connection monitoring
- Security configuration

#### 7.9 API Management
- API documentation
- API key management
- Rate limiting configuration
- Endpoint monitoring
- Webhook management

### Restrictions
- **Cannot manage customers or billing**: View-only access to business data
- **Focus on technical infrastructure**: System and integration management
- **Limited business operations**: No day-to-day ISP operations

### Key Features
- Full technical access
- API and integration management
- System debugging tools
- Infrastructure configuration
- Multi-tenant technical support

---

## 8. Accountant Panel

### Access Level
- **Financial Reporting Panel**: Read-only financial access
- **Scope**: Financial data and reports
- **URL Prefix**: `/panel/accountant/*`

### Main Sections

#### 8.1 Dashboard
- Financial overview
- Revenue metrics
- Outstanding balances
- Collection summary

#### 8.2 Financial Reports
- Income/Expense reports
- Payment history reports
- Customer statement reports
- Revenue analysis
- Profit/Loss statements
- Cash flow reports

#### 8.3 Income/Expense Tracking
- Transaction history (view only)
- Expense records (view only)
- Category-wise breakdown
- Period comparisons

#### 8.4 VAT Collections
- VAT collected summary
- Tax reports
- Compliance documentation
- Period-wise VAT tracking

#### 8.5 Payment History
- All payment records (view only)
- Gateway transactions
- Manual payments
- Refunds and adjustments

#### 8.6 Customer Statements
- Individual customer statements
- Outstanding balances
- Payment history by customer
- Account receivables

### Restrictions
- **Typically read-only access**: View and export only
- **Cannot modify customer data**: No customer management
- **Cannot process payments**: View-only payment data
- **No system configuration**: Financial viewing only

### Key Features
- Comprehensive financial reporting
- Export capabilities (PDF, Excel)
- Period-wise analysis
- Compliance reporting
- Audit trail viewing

---

## 9. Customer Panel

### Access Level
- **Self-Service Portal**: Personal account management
- **Scope**: Own account only
- **URL Prefix**: `/panel/customer/*`

### Main Sections

#### 9.1 Dashboard
- Account overview
- Package details
- Usage summary
- Recent activity

#### 9.2 Profile Management
- Personal information
- Contact details
- Password change
- Notification preferences

#### 9.3 Billing
- View invoices
- Payment history
- Make payment (online/offline)
- Download receipts
- Package renewal

#### 9.4 Usage Statistics
- Data upload/download
- Session history
- Bandwidth usage
- Monthly trends

#### 9.5 Support Tickets
- Create new ticket
- View ticket history
- Track ticket status
- Respond to updates

### Key Features
- Self-service account management
- Online payment integration
- Usage monitoring
- Support system access
- Transparent billing

### Restrictions
- Limited to own account only
- Cannot view other customers
- Cannot modify package prices
- Cannot access administrative features

---

## Permission System

### Operator Levels
```
Developer:      0  (Highest - Technical)
Super Admin:   10  (System-wide)
Group Admin:   20  (Tenant Admin)
Operator:      30  (Operational)
Sub-Operator:  40  (Limited Operations)
Manager:       50  (Task-specific)
Card Distributor: 60 (Card operations)
Accountant:    70  (Financial viewing)
Staff:         80  (Support)
Customer:     100  (Lowest - Self-service)
```

### Menu Control
Admins can disable specific menus for Operators using the `disabled_menus` field in the users table:

**Available Menu Keys:**
- `resellers_managers`
- `routers_packages`
- `recharge_cards`
- `customers`
- `bills_payments`
- `incomes_expenses`
- `affiliate_program`
- `vat_management`

**Example:**
```json
{
  "disabled_menus": ["routers_packages", "recharge_cards"]
}
```

This operator would not see the "Routers & Packages" and "Recharge Cards" menus.

---

## Access Control Implementation

### Route Protection
```php
Route::middleware(['auth', 'role:operator'])->group(function () {
    // Operator routes
});
```

### Menu Visibility
```php
function canAccessMenu(array $menuItem): bool {
    $user = auth()->user();
    
    // Check if menu is disabled
    if (isset($menuItem['key']) && $user->isMenuDisabled($menuItem['key'])) {
        return false;
    }
    
    // Check permission if specified
    if (isset($menuItem['permission'])) {
        return $user->hasPermission($menuItem['permission']);
    }
    
    return true;
}
```

### Data Isolation

The system implements strict hierarchical data isolation:

```php
// Developer: All tenants (supreme authority, source code owner)
// Can create and manage ALL tenants
$tenants = Tenant::all();

// Super Admin: Only OWN tenants (NOT all tenants)
// Can create and manage admins for their tenants
$tenants = Tenant::where('created_by', auth()->id())->get();
$admins = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_type', 'group_admin')->get();

// Admin (Group Admin): All data under their own ISP
// See their customers AND operator-created customers AND sub-operator-created customers
// Can create and manage operators
$customers = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_level', 100)->get();
$operators = User::where('tenant_id', auth()->user()->tenant_id)
    ->where('operator_type', 'operator')->get();

// Operator: Only their created customers PLUS sub-operator-created customers
// Can create and manage sub-operators
$subOperators = User::where('created_by', auth()->id())
    ->where('operator_type', 'sub_operator')->get();
$customers = User::where('created_by', auth()->id())
    ->orWhereIn('created_by', $subOperators->pluck('id'))
    ->where('operator_level', 100)->get();

// Sub-Operator: Only their created customers
$customers = User::where('created_by', auth()->id())
    ->where('operator_level', 100)->get();

// Manager: View based on assigned permissions
// Can view operators' or sub-operators' customers (read-only typically)

// Staff: View based on assigned permissions
// Limited operational access
```

---

## Security Considerations

### Authentication
- Multi-factor authentication support
- Session timeout management
- Password complexity requirements
- Login attempt limiting

### Authorization
- Role-based access control (RBAC)
- Permission-based feature access
- Data isolation by operator level
- Menu-level access control

### Audit Logging
- All administrative actions logged
- Authentication events tracked
- Data modification history
- Security event monitoring

### Data Protection
- Tenant data isolation
- Encrypted sensitive data
- GDPR compliance ready
- Regular backup schedules

---

## Best Practices

### For Admins
1. Configure operator permissions carefully
2. Regularly review access logs
3. Disable unnecessary menus for operators
4. Use sub-operators for team delegation
5. Monitor financial transactions

### For Operators
1. Only access assigned customer data
2. Process payments accurately
3. Respond to customer tickets promptly
4. Keep customer information updated
5. Report system issues to admin

### For Developers
1. Test integrations thoroughly
2. Monitor API usage and errors
3. Keep documentation updated
4. Review system logs regularly
5. Maintain backup configurations

### For Accountants
1. Reconcile accounts regularly
2. Export reports for records
3. Review VAT collections
4. Track outstanding payments
5. Report discrepancies

---

## Support and Documentation

### Additional Resources
- **API Documentation**: `/docs/API.md`
- **User Guides**: `/docs/USER_GUIDES.md`
- **Role-Based Menus**: `/docs/ROLE_BASED_MENU.md`
- **Deployment Guide**: `/docs/DEPLOYMENT.md`

### Contact
- **Technical Support**: Review system logs in Developer panel
- **Business Support**: Contact your tenant administrator
- **Security Issues**: Report to system administrator immediately

---

**Document Version:** 2.0  
**Last Updated:** 2026-01-18  
**Next Review:** 2026-04-18  
**Maintained By:** Development Team
