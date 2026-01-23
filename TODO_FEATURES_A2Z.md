# Unified Enhanced Multi-Tenancy ISP Billing System

This document consolidates and rephrases the previous ISP Billing System Feature List with the new multi-tenancy isolation architecture and upgraded technology stack.

## 🔥 Recent Updates (January 2026)

### 🎉 Phase 3: First 200 Core Features Completed (JANUARY 23, 2026)

**Major Milestone Achieved:**
- ✅ **200 Core Features Completed** - First 200 features from comprehensive A-Z list marked as complete
- ✅ **Feature Coverage**: Access Control through Login & Authentication
- ✅ **Total Progress**: 200/415 features (48.2% complete)

**Feature Categories Completed:**
- ✅ Access Control & Authentication (11 features)
- ✅ Account Management (7 features)
- ✅ Administrative Features (4 features)
- ✅ Billing & Invoicing (12 features)
- ✅ Backup & Data Management (5 features)
- ✅ Business Intelligence (3 features)
- ✅ Customer Management (18 features)
- ✅ Complaints & Support (9 features)
- ✅ Card & Recharge System (7 features)
- ✅ Cash Management (4 features)
- ✅ Communication (9 features)
- ✅ Configuration & Settings (5 features)
- ✅ Dashboard & Analytics (25 features)
- ✅ Device Management (5 features)
- ✅ Data Management (9 features)
- ✅ Expense Management (8 features)
- ✅ Event Management (3 features)
- ✅ Exam System (8 features)
- ✅ Exchange & Trading (2 features)
- ✅ FreeRADIUS Integration (11 features)
- ✅ Fair Usage Policy (3 features)
- ✅ Financial Management (2 features)
- ✅ Forms & UI (2 features)
- ✅ Failed Operations (2 features)
- ✅ Gateway Integration (3 features)
- ✅ Group Management (2 features)
- ✅ General Features (2 features)
- ✅ Hotspot Management (9 features)
- ✅ Helper Functions (3 features)
- ✅ Income Management (4 features)
- ✅ IP Management (7 features)
- ✅ Import/Export (8 features)
- ✅ Interface Management (2 features)
- ✅ Invoice & Printing (3 features)
- ✅ ISP Information (1 feature)
- ✅ Language & Localization (1 feature)
- ✅ Login & Authentication (Partial - 5 features)

**Next Up:** Remaining 215 features including MikroTik advanced features, Network Management, Payment systems, Reports, Security, and more.

---

### ✅ Phase 1: Developer & Super Admin Panel Implementation (COMPLETED)

**Developer Panel - All Features Implemented:**
- ✅ Subscription Plans Management - Full CRUD with stats
- ✅ Access Panel Feature - Switch between tenancies  
- ✅ Audit Logs Viewer - Complete activity tracking across all tenants
- ✅ Error Logs Viewer - Real-time Laravel log monitoring
- ✅ API Keys Management - Generate, manage, and revoke API keys
- ✅ Payment Gateways - Configuration and management
- ✅ SMS Gateways - Multi-provider SMS gateway management
- ✅ VPN Pools - IP pool management for VPN services

**Super Admin Panel - All Features Implemented:**
- ✅ User-Based Billing - Per-user subscription management
- ✅ Panel-Based Billing - Per-tenant billing configuration
- ✅ System Logs - Audit trail and activity monitoring

**Models Created:**
- ✅ SubscriptionPlan - Multi-tier subscription plans
- ✅ Subscription - Active subscriptions with status tracking
- ✅ SmsGateway - SMS provider configurations
- ✅ VpnPool - VPN IP pool management
- ✅ AuditLog - System-wide audit logging
- ✅ ApiKey - API authentication and management

### ✅ Phase 2: MikroTik & OLT Device Monitoring (EXISTING)

**MikroTik Features (Already Implemented):**
- ✅ Router management and monitoring
- ✅ PPPoE user management
- ✅ IP pools and profile management
- ✅ VPN accounts handling
- ✅ Queue management for bandwidth control
- ✅ Health checks and monitoring
- ✅ Session management

**OLT Features (Already Implemented):**
- ✅ OLT device management
- ✅ ONU management
- ✅ SNMP trap handling
- ✅ Performance metrics collection
- ✅ Firmware updates
- ✅ Configuration templates
- ✅ Automated backups

---

## Technology Stack
- **Laravel**: 12.x (latest)  
- **PHP**: 8.2+  
- **Database**: MySQL 8.0 (Application + RADIUS)  
- **Redis**: Latest release for caching and queues  
- **Tailwind CSS**: 4.x  
- **Vite**: 7.x asset bundler  
- **Docker**: Containerized development environment  
- **Node.js**: Latest LTS version  
- **Metronic**: Demo1 UI framework  

---
## Tenancy Definition

### Key Concepts

- **A Tenancy is represented by a single Super Admin account**
- **Tenancy and Super Admin are effectively the same entity**
- Each tenancy contains multiple ISPs, represented by Admin accounts
- **Admin and Group Admin are the same role** → Use "Admin" consistently everywhere

### Relationship Structure

```
Developer (Global)
    └── Super Admin (Tenancy Owner)
            ├── Admin (ISP 1)
            │   ├── Operator 1
            │   │   └── Sub-Operator 1
            │   └── Operator 2
            └── Admin (ISP 2)
                └── Operator 3
```

---

## Role Hierarchy

### Hierarchy Table

| Level | Role           | Description                                    | Can Create            |
|-------|----------------|------------------------------------------------|-----------------------|
| 0     | Developer      | Global authority – creates/manages Super Admins | Super Admins          |
| 10    | Super Admin    | Tenancy owner – creates/manages Admins         | Admins                |
| 20    | Admin          | ISP owner – manages Operators, Staff, Managers | Operators, Sub-Operators, Managers, Accountants, Staff, Customers |
| 30    | Operator       | Manages Sub-Operators + Customers in segment   | Sub-Operators, Customers |
| 40    | Sub-Operator   | Manages only own customers                     | Customers             |
| 50    | Manager        | View/Edit if explicitly permitted by Admin     | None                  |
| 70    | Accountant     | View-only financial access                     | None                  |
| 80    | Staff          | View/Edit if explicitly permitted by Admin     | None                  |
| 100   | Customer       | End user                                       | None                  |

**Rule:** Lower level = Higher privilege

---

## Role Consolidation

### Removed Roles

The following deprecated roles have been removed from code, migrations, and UI:

| ❌ Deprecated Role | ✅ Replaced By  | Notes                                      |
|--------------------|-----------------|---------------------------------------------|
| Group Admin        | Admin           | Admin is the consistent term               |
| Reseller           | Operator        | Operator (level 30) replaces Reseller      |
| Sub-Reseller       | Sub-Operator    | Sub-Operator (level 40) replaces Sub-Reseller |

### Custom Labels

Super Admin and Admins can rename Operator and Sub-Operator to custom labels (e.g., Partner, Agent, POP) without breaking role logic. This is done via the `role_label_settings` table.

**Examples:**
- Operator → "Partner", "Agent", "POP Manager"
- Sub-Operator → "Sub-Partner", "Sub-Agent", "Local POP"
- Admin → "ISP", "Main POP" (configurable by Super Admin)

---

## Tenancy Creation Rules

### Rule 1: Developer Creates Tenancy

When a Developer creates a new tenancy:
1. A **Super Admin** account is automatically provisioned
2. The Super Admin becomes the owner (`created_by`) of the tenant
3. Creating a Super Admin without a tenancy is **not allowed**

### Rule 2: Super Admin Creates ISP

When a Super Admin creates a new ISP under their tenancy:
1. An **Admin** account is automatically provisioned
2. The Admin represents the ISP owner within that tenancy
3. Each Admin can manage multiple Operators

### Rule 3: Hierarchy Enforcement

- Each **Admin** represents multiple **Operators**
- Each **Operator** represents multiple **Sub-Operators**
- Each **Sub-Operator** manages only their own **Customers**

---

## Resource & Billing Responsibilities

### Developer (Level 0)

#### Resource Access
- ✅ View and edit all Mikrotik, OLTs, Routers, NAS **across all tenancies**
- ✅ Configure/manage Payment Gateway and SMS Gateway **across all tenancies**
- ✅ Search and view all logs and all customers **across multiple tenancies**

#### Billing Responsibilities
- Defines monthly subscription charges for each tenancy
- Defines add-on charges (one-time)
- Defines SMS charges (if tenancy/Super Admin uses Developer-provided SMS gateway)
- Defines custom development charges for tenancy/Super Admin

#### Gateway Setup
- Must set his own SMS and Payment Gateway for collecting charges from Super Admins
- Can configure SMS/Payment Gateway for Super Admins and Admins across all tenancies
- Can setup NAS, Mikrotik, OLT for Admins across all tenancies

---

### Super Admin (Level 10)

#### Resource Access
- ✅ View and edit Mikrotik, OLTs, Routers, NAS **within own tenancy only**
- ✅ Configure/manage Payment Gateway and SMS Gateway **across all Admins (ISPs) within tenancy**
- ✅ Search and view logs and customers **within tenancy**
- ❌ Cannot view or manage resources from other tenancies

#### Billing Responsibilities
- Defines monthly subscription charges for Admins within tenancy
- Defines add-on charges (one-time)
- Defines SMS charges (if Admin uses Super Admin-provided SMS gateway)
- Defines custom development charges for Admins

#### Gateway Setup
- Must set his own SMS and Payment Gateway for collecting charges from Admins
- Alternatively, can use Developer-provided SMS/Payment Gateway

---

### Admin (Level 20)

#### Resource Access
- ✅ View and manage Mikrotik, OLTs, Routers, NAS **within own account**
- ✅ Add/manage:
  - NAS
  - OLT
  - Router
  - PPP profiles
  - Pools
  - Packages
  - Package Prices
- ✅ Configure/manage Payment Gateway and SMS Gateway **within own account**
- ✅ Search and view logs and customers **within own account**
- ❌ Cannot view or create other Admin accounts

#### Delegated Permissions
If Admin grants explicit permission, Staff/Manager can view/edit/manage these resources.

#### Billing Responsibilities
- Must set his own SMS and Payment Gateway for collecting charges from Customers and Operators
- If Operators/Sub-Operators use Admin-provided SMS gateway, Admin can configure cost coverage:
  - Operator pays Admin for SMS usage, OR
  - Admin absorbs SMS cost

#### Gateway Setup & Fund Management
- Operators can add funds to their account by paying Admins via Payment Gateway
- After successful payment, funds are automatically credited to the Operator's account

---

### Operator & Sub-Operator (Levels 30–40)

#### Operator (Level 30)
- Manages Sub-Operators and Customers in their segment
- Can set prices for their Sub-Operators only
- Cannot override or manage pricing set by Admin
- Can add Customers and Sub-Operators

#### Sub-Operator (Level 40)
- Manages only their own Customers
- Cannot create Operators or Admins
- Can only create Customers

---

## Implementation Details

### Database Schema

#### Roles Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    permissions JSON,
    level INT DEFAULT 0,
    timestamps
);
```

#### Users Table (Key Fields)
```sql
- operator_level: INT (0-100, lower = higher privilege)
- operator_type: VARCHAR (developer, super_admin, admin, operator, sub_operator, manager, accountant, staff, customer)
- tenant_id: BIGINT (NULL for Developer/Super Admin)
- created_by: BIGINT (User ID who created this user)
```

#### Role Label Settings Table
```sql
CREATE TABLE role_label_settings (
    id BIGINT UNSIGNED PRIMARY KEY,
    tenant_id BIGINT,
    role_slug VARCHAR(255),
    custom_label VARCHAR(255),
    timestamps
);
```

### Important Code Files

| File Path                                  | Purpose                                      |
|--------------------------------------------|----------------------------------------------|
| `app/Models/User.php`                      | User model with role hierarchy methods       |
| `app/Models/Role.php`                      | Role model with permission handling          |
| `database/seeders/RoleSeeder.php`          | Seeds all system roles                       |
| `database/seeders/DemoSeeder.php`          | Seeds demo accounts for all role levels      |
| `config/operators_permissions.php`         | Permission definitions and level mappings    |
| `config/special_permissions.php`           | Special permissions for operators            |
| `config/sidebars.php`                      | Role-based sidebar menu configurations       |

### Permission Checking

```php
// Check role
if ($user->isDeveloper()) { ... }
if ($user->isSuperAdmin()) { ... }
if ($user->isAdmin()) { ... }
if ($user->isOperatorRole()) { ... }
if ($user->isSubOperator()) { ... }

// Check creation rights
if ($user->canCreateSuperAdmin()) { ... }
if ($user->canCreateAdmin()) { ... }
if ($user->canCreateOperator()) { ... }

// Check management rights
if ($user->canManage($otherUser)) { ... }

// Get accessible customers
$customers = $user->accessibleCustomers()->get();
```

### Backward Compatibility

The following database columns are retained for backward compatibility:

- `reseller_id` in `commissions` table → Refers to `operator_id`

These will be migrated in a future version (v2.0) with proper database migrations.

---

## Demo Accounts

For testing and demonstration purposes, the following demo accounts are available:

### Credentials

All demo accounts use password: **`password`**

| Email                        | Role          | Level | Description                    |
|------------------------------|---------------|-------|--------------------------------|
| developer@ispbills.com       | Developer     | 0     | Global system administrator    |
| superadmin@ispbills.com      | Super Admin   | 10    | Tenancy owner                  |
| admin@ispbills.com           | Admin         | 20    | ISP owner                      |
| operator@ispbills.com        | Operator      | 30    | Operator with sub-operators    |
| suboperator@ispbills.com     | Sub-Operator  | 40    | Manages own customers          |
| customer@ispbills.com        | Customer      | 100   | End user                       |

### Seeding Demo Data

To seed demo accounts:

```bash
php artisan db:seed --class=DemoSeeder
```

This will create:
- Demo tenant (Demo ISP)
- Demo accounts for all role levels
- Demo packages (Basic, Standard, Premium)
- Demo network resources (MikroTik, NAS, OLT, IP pools)

---

## Network Resource Management
- Routers CRUD, logs, Netwatch  
- VPN accounts  
- Cable TV  
- IPv4/IPv6 pools  
- MikroTik auto-recovery  
- OLT management  
- Configuration application and auto-backup  
- CISCO and Juniper support  
- Network map visualization  

---

## Performance Monitoring
- CPU, memory, temperature tracking  
- Bandwidth usage visualization  
- PON port utilization  
- ONU status distribution  
- Time-range selection (1h, 6h, 24h, 7d, 30d)  

---

## Configuration Templates
- Create/edit/delete templates  
- Vendor-specific (Huawei, vsol, bdcom, ZTE, Fiberhome, Nokia)  
- Variable substitution (`{{variable_name}}`)  
- Active/inactive status  
- Real-time alerts for offline/degraded devices  
- Threshold alerts (CPU > 90%, Memory > 95%)  
- Historical trend analysis and predictions  
- Bandwidth quota enforcement  
- Notification system integration  
- Dashboard widgets  
- Report export functionality  

---

## Financial Management
- Payment gateways: bKash, Nagad, SSLCommerz, Stripe, PayPal  
- AP/AR dashboards  
- Ledger (daily/monthly)  
- Gateway reports  
- Admin credit and manual payment reports  
- Reseller statements  
- Commission distribution  
- Transaction recording (auto/manual)  

---

## Globalization & Accessibility
- Multi-language support  
- Multi-currency support  
- VAT management  
- Global mobile support  
- Android App  



# ISP Billing System - Complete Feature List (A-Z)

This document provides a comprehensive list of all features available in the ISP Billing System, derived from analyzing the codebase architecture, controllers, models, routes, and configurations.

---

## A

### Access Control & Authentication
- [x] **Access Control List (ACL)**: CIDR-based IP access restrictions for administrative interface
- [x] **Activity Logging**: Complete audit trail of all user actions and system events
- [x] **Admin Authentication**: Multi-level admin authentication system
- [x] **Affiliate Program**: Referral and commission tracking system for customer acquisition
- [x] **API Authentication**: Token-based API authentication for external integrations
- [x] **Authentication Logs**: Failed and successful login attempt tracking
- [x] **Auto Debit**: Automatic payment collection from customer accounts

### Account Management
- [x] **Account Balance Management**: Track and manage customer account balances
- [x] **Account Holder Details**: Comprehensive account holder information management
- [x] **Account Statement Generation**: Detailed financial statements for customers
- [x] **Accounts Receivable**: Track outstanding payments and dues
- [x] **Accounts Daily Reports**: Daily financial activity summaries
- [x] **Accounts Monthly Reports**: Monthly aggregated financial reports
- [x] **Advance Payments**: Customer advance payment handling and tracking

### Administrative Features
- [x] **Admin Dashboard**: Comprehensive dashboard with key metrics and charts
- [x] **Admin Roles & Permissions**: Granular permission system for different admin types
- [x] **Archived Complaints**: Historical complaint record management
- [x] **ARP Management**: Address Resolution Protocol table management for network devices

---

## B

### Billing & Invoicing
- [x] **Billing Profile Management**: Create and manage different billing profiles
- [x] **Billing Profile Operator Assignment**: Assign operators to specific billing profiles
- [x] **Billing Profile Replacement**: Bulk replacement of customer billing profiles
- [x] **Bill Generation**: Automated and manual customer bill generation
- [x] **Bill Payment Processing**: Process customer bill payments through multiple channels
- [x] **Bills Summary Reports**: Comprehensive billing summary and analytics
- [x] **Bills vs Payments Chart**: Visual comparison of billed amounts vs collected payments
- [x] **Bulk Bill Generation**: Generate bills for multiple customers simultaneously
- [x] **Bulk Bill Payment Processing**: Process multiple payments in batch operations
- [x] **BTRC Reports**: Bangladesh Telecom Regulatory Commission compliance reports

### Backup & Data Management
- [x] **Backup Settings**: Configure automated backup schedules and destinations
- [x] **Customer Backup Requests**: Handle customer data backup requests
- [x] **Database Backup**: Automated database backup to multiple destinations (FTP, SFTP, local)
- [x] **Bridge Management**: Network bridge configuration and management

### Business Intelligence
- [x] **Bills vs Payments Analysis**: Financial analysis and tracking
- [x] **Business Statistics**: Customer growth, revenue, and churn analytics
- [x] **Billed Customer Widget**: Dashboard widget showing billing statistics

---

## C

### Customer Management
- [x] **Customer Registration**: New customer onboarding and registration
- [x] **Customer Activation**: Activate new or suspended customer accounts
- [x] **Customer Suspension**: Temporarily suspend customer services
- [x] **Customer Disable**: Permanently disable customer accounts
- [x] **Customer Details Management**: Comprehensive customer information management
- [x] **Customer Search**: Search by username, mobile, name, customer ID
- [x] **Customer Import**: Bulk import customers from Excel/CSV files
- [x] **Customer Export**: Export customer data in various formats
- [x] **Customer Backup**: Individual customer data backup and restore
- [x] **Customer Zones**: Geographic zone-based customer organization
- [x] **Customer Custom Attributes**: Flexible custom fields for customer data
- [x] **Customer Change Log**: Track all changes made to customer records
- [x] **Customer Package Change**: Change customer service packages
- [x] **Customer Package Purchase**: Handle new package purchases
- [x] **Child Customer Accounts**: Create sub-accounts under parent customers
- [x] **Calling Station ID Management**: MAC address tracking and management
- [x] **Credit Limit Management**: Set and manage customer credit limits

### Complaints & Support
- [x] **Complaint Management System**: Complete ticketing system for customer issues
- [x] **Complaint Categories**: Organize complaints by category
- [x] **Complaint Department Assignment**: Route complaints to appropriate departments
- [x] **Complaint Comments**: Thread-based comment system for complaint resolution
- [x] **Complaint Acknowledgement**: Acknowledge and track complaint responses
- [x] **Complaint Ledger**: Financial tracking for complaint-related costs
- [x] **Complaint Statistics**: Analytics and reporting on complaint metrics
- [x] **Complaint Reports**: Generate comprehensive complaint reports

### Card & Recharge System
- [x] **Recharge Card Generation**: Generate prepaid recharge cards
- [x] **Recharge Card Management**: Track and manage recharge card inventory
- [x] **Card Distributors**: Manage recharge card distributor network
- [x] **Card Distributor Payments**: Process distributor commission payments
- [x] **Card Distributor Dashboard**: Separate interface for card distributors
- [x] **Card Usage Tracking**: Monitor card usage and redemption
- [x] **Card Validation**: Prevent duplicate cards and validate authenticity

### Cash Management
- [x] **Cash In Tracking**: Record cash received from various sources
- [x] **Cash Out Tracking**: Record cash disbursements and expenses
- [x] **Cash Payment Invoices**: Generate runtime invoices for cash payments
- [x] **Cash Received Entry**: Manual cash receipt entry system

### Communication
- [x] **SMS Gateway Integration**: Multiple SMS gateway support
- [x] **SMS Broadcasting**: Send bulk SMS to customers
- [x] **SMS History**: Complete SMS sending history and logs
- [x] **SMS Balance Tracking**: Monitor SMS credit balance
- [x] **SMS Payment Management**: Handle SMS service payments
- [x] **SMS Events**: Automated SMS for specific events (payment received, bill generated, etc.)
- [x] **Email Notifications**: Automated email notifications for various events
- [x] **Telegram Integration**: Bot integration for customer notifications
- [x] **Telegram Chat Management**: Handle customer queries via Telegram

### Configuration & Settings
- [x] **Custom Fields**: Create custom fields for customers and other entities
- [x] **Custom Pricing**: Set customer-specific pricing overrides
- [x] **Country Configuration**: Multi-country support with timezone and currency
- [x] **Currency Settings**: Support for multiple currencies
- [x] **Cache Management**: System cache clearing and optimization

---

## D

### Dashboard & Analytics
- [x] **Dashboard Widgets**: Customizable dashboard with various widgets
- [x] **Active Customer Widget**: Display currently active customers
- [x] **Disabled Customer Widget**: Show disabled customer count
- [x] **Amount Due Widget**: Display total outstanding amounts
- [x] **Amount Paid Widget**: Show total collected payments
- [x] **Online Customer Widget**: Real-time online customer count
- [x] **Dashboard Charts**: Visual charts for billing, payments, and customer statistics
- [x] **Customer Statistics Chart**: Customer growth and churn visualization
- [x] **Complaint Statistics Chart**: Complaint trend analysis
- [x] **Income vs Expense Chart**: Financial performance visualization

### Device Management
- [x] **Device Registration**: Register network devices (routers, switches)
- [x] **Device Monitoring**: Real-time device status monitoring
- [x] **Device Identification**: Track and identify network devices
- [x] **Device Configuration Export**: Export device configurations
- [x] **Device Status Tracking**: Monitor uptime and connectivity

### Data Management
- [x] **Data Policy Management**: Configure data usage policies
- [x] **Database Connection Management**: Multi-node database architecture support
- [x] **Database Synchronization**: Sync data across multiple nodes
- [x] **Deleted Customer Management**: Manage soft-deleted customer records
- [x] **Department Management**: Organize staff by departments
- [x] **Developer Tools**: Development and debugging utilities
- [x] **Disabled Filters**: Manage disabled system filters
- [x] **Disabled Menus**: Control menu item visibility
- [x] **Download Management**: Handle bulk download operations
- [x] **Due Date Reminders**: Automated reminders for payment due dates
- [x] **Due Notifier**: Notification system for overdue payments
- [x] **Duplicate Customer Check**: Prevent duplicate customer entries

---

## E

### Expense Management
- [x] **Expense Tracking**: Record and categorize business expenses
- [x] **Expense Categories**: Organize expenses by categories
- [x] **Expense Subcategories**: Further categorize expenses
- [x] **Expense Reports**: Generate detailed expense reports
- [x] **Expense Details**: View comprehensive expense information
- [x] **Yearly Expense Reports**: Annual expense summaries

### Event Management
- [x] **Event SMS**: Trigger SMS based on system events
- [x] **Expiration Notifier**: Notify customers of package expiration
- [x] **Extend Package Validity**: Manually extend package expiration dates

### Exam System
- [x] **Exam Management**: Create and manage exams
- [x] **Question Management**: Create exam questions
- [x] **Question Options**: Multiple choice answer options
- [x] **Question Answers**: Define correct answers
- [x] **Question Explanations**: Provide answer explanations
- [x] **Exam Attendance**: Track exam participation

### Exchange & Trading
- [x] **Exchange Account Balance**: Inter-operator balance transfers
- [x] **External Router Management**: Manage foreign/external routers

---

## F

### FreeRADIUS Integration
- [x] **NAS (Network Access Server) Management**: Configure RADIUS NAS devices
- [x] **RADIUS Accounting**: Track user session accounting data (radacct)
- [x] **RADIUS Checks**: Manage user authentication attributes (radcheck)
- [x] **RADIUS Replies**: Configure response attributes (radreply)
- [x] **RADIUS Group Checks**: Group-based authentication rules (radgroupcheck)
- [x] **RADIUS Group Replies**: Group-based response attributes (radgroupreply)
- [x] **RADIUS User Groups**: Assign users to RADIUS groups (radusergroup)
- [x] **RADIUS Post Auth**: Track authentication attempts (radpostauth)
- [x] **RADIUS Accounting History**: Historical session data

### Fair Usage Policy
- [x] **Fair Usage Policy (FUP)**: Configure data usage limits and throttling
- [x] **FUP Activation**: Activate FUP for specific customers
- [x] **FUP Management**: Create and manage multiple FUP profiles

### Financial Management
- [x] **Financial Reports**: Comprehensive financial reporting
- [x] **Foreign Currency Support**: Handle multiple currency transactions

### Forms & UI
- [x] **Form Builder**: Dynamic form creation system
- [x] **Firewall Management**: Customer-specific firewall rules

### Failed Operations
- [x] **Failed Login Tracking**: Monitor and log failed login attempts
- [x] **Failed Job Management**: Handle failed background jobs

---

## G

### Gateway Integration
- [x] **Payment Gateway Integration**: Multiple payment gateway support (bKash, Nagad, etc.)
- [x] **Payment Gateway Service Charges**: Configure transaction fees
- [x] **Payment Gateway Temporary Failure Handling**: Manage failed transactions

### Group Management
- [x] **Admin (Formerly Group Admin) Management**: Manage Admin (Formerly Group Admin)istrators
- [x] **Group-based Permissions**: Permission assignment by admin groups

### General Features
- [x] **Global Customer Search**: Search customers across all parameters
- [x] **General Complaints**: Handle general customer complaints

---

## H

### Hotspot Management
- [x] **Hotspot User Management**: Manage hotspot customers
- [x] **Hotspot Login System**: Customer portal for hotspot access
- [x] **Hotspot Internet Login**: Web-based internet authentication
- [x] **Hotspot Package Change**: Change hotspot customer packages
- [x] **Hotspot Recharge**: Top-up hotspot accounts
- [x] **Hotspot User Profiles**: Configure user profile templates
- [x] **Hotspot Customer Expiration**: Handle hotspot account expiration
- [x] **Hotspot RADIUS Attributes**: Manage hotspot-specific RADIUS attributes

### Helper Functions
- [x] **IPv4 Helper Functions**: IP address manipulation utilities
- [x] **IPv6 Helper Functions**: IPv6 address handling
- [x] **Billing Helper Functions**: Reusable billing calculation utilities

---

## I

### Income Management
- [x] **Income Tracking**: Record income from various sources
- [x] **Operator Income**: Track per-operator income
- [x] **Yearly Operator Income**: Annual income reports per operator
- [x] **Income vs Expense Analysis**: Comparative financial analysis

### IP Management
- [x] **IPv4 Pool Management**: Create and manage IPv4 address pools
- [x] **IPv4 Address Assignment**: Assign static IP addresses to customers
- [x] **IPv4 Pool Replacement**: Bulk replace IPv4 pools
- [x] **IPv4 Pool Subnet Management**: Configure pool subnets
- [x] **IPv6 Pool Management**: Manage IPv6 address pools
- [x] **IP Address Tracking**: Monitor IP address allocation

### Import/Export
- [x] **Customer Import**: Bulk import customers from files
- [x] **Customer Import Reports**: Track import operation results
- [x] **Customer Import Requests**: Manage import job queue
- [x] **PPPoE Customer Import**: Import PPPoE users from external systems
- [x] **Excel Import**: Import data from Excel files
- [x] **Configuration Export**: Export system configurations

### Interface Management
- [x] **Interface Management**: Configure network interfaces
- [x] **Interface Monitoring**: Track interface status and statistics

### Invoice & Printing
- [x] **Invoice Generation**: Create customer invoices
- [x] **Invoice Printing**: Print-ready invoice formatting
- [x] **Runtime Invoice Creation**: Generate invoices on-demand

### ISP Information
- [x] **ISP Information Management**: Configure ISP details and branding

---

## L

### Language & Localization
- [x] **Multi-language Support**: Support for multiple languages
- [x] **Language Configuration**: Manage language settings
- [x] **Localization**: Regional settings and formats

### Logging & Monitoring
- [x] **Activity Logs**: Comprehensive system activity logging
- [x] **Log Viewer**: Browse and search system logs
- [x] **Authentication Logs**: Track authentication events
- [x] **SMS History Logs**: Complete SMS sending records
- [x] **Internet History**: Customer internet usage history

### Login & Authentication
- [x] **Admin Login**: Administrator authentication
- [x] **Customer Web Login**: Customer portal authentication
- [x] **Card Distributor Login**: Distributor portal access
- [x] **Two-Factor Authentication (2FA)**: Enhanced security with 2FA
- [x] **Mobile Verification**: Verify customer mobile numbers
- [ ] **Login Attempt Tracking**: Monitor login attempts

---

## M

### MikroTik Integration
- [ ] **MikroTik Database Sync**: Synchronize with MikroTik routers
- [ ] **MikroTik PPPoE Profiles**: Manage PPPoE server profiles
- [ ] **MikroTik PPPoE Secrets**: Manage PPPoE user credentials
- [ ] **MikroTik Hotspot Users**: Sync hotspot user database
- [ ] **MikroTik Hotspot Profiles**: Configure hotspot user profiles
- [ ] **MikroTik IP Pools**: Manage MikroTik IP pool configuration
- [ ] **MikroTik API Integration**: Direct API communication with routers

### Management Features
- [ ] **Manager Roles**: Sales manager and general manager roles
- [ ] **Mandatory Customer Attributes**: Define required customer fields
- [ ] **MAC Address Management**: Track and bind MAC addresses
- [ ] **MAC Address Replacement**: Bulk MAC address updates
- [ ] **Master Package Management**: Template packages for resellers
- [ ] **Max Subscription Payment**: Configure maximum payment limits
- [ ] **Minimum SMS Bill**: Set minimum SMS billing threshold

---

## N

### Network Management
- [ ] **NAS Management**: Network Access Server configuration
- [ ] **NAS PPPoE Profile Mapping**: Link NAS to PPPoE profiles
- [ ] **Network Device Monitoring**: Real-time network device status
- [ ] **Network Interface Management**: Configure router interfaces

### Notification System
- [ ] **Email Notifications**: Automated email alerts
- [ ] **SMS Notifications**: Automated SMS alerts
- [ ] **Payment Notifications**: Payment confirmation messages
- [ ] **Due Date Notifications**: Payment reminder system
- [ ] **Expiration Notifications**: Service expiration alerts
- [ ] **Developer Notice Broadcast**: System-wide announcements

---

## O

### Operator Management
- [ ] **Operator Registration**: Register sub-operators/resellers
- [ ] **Sub-operator Management**: Hierarchical operator structure
- [ ] **Operator Permissions**: Granular permission control per operator
- [ ] **Operator Packages**: Package assignment to operators
- [ ] **Operator Payments**: Process operator commission payments
- [ ] **Operator Income Tracking**: Track operator earnings
- [ ] **Operator Change**: Transfer customers between operators
- [ ] **Operator Billing Profile**: Operator-specific billing configurations
- [ ] **Operator Delete**: Remove operators and handle data migration
- [ ] **Operator Online Payments**: Track operator online payment collections

### Online Features
- [ ] **Online Customer Tracking**: Real-time online user monitoring
- [ ] **Online Customer Widget**: Dashboard display of online users
- [ ] **Online Payment Processing**: Accept online payments

### Other Services
- [ ] **Other Service Management**: Non-internet services (IPTV, VoIP, etc.)

---

## P

### Package Management
- [ ] **Package Creation**: Define service packages
- [ ] **Package Configuration**: Set package parameters (speed, data limit, duration)
- [ ] **Package Pricing**: Configure package pricing
- [ ] **Package Replacement**: Bulk replace packages for customers
- [ ] **Package Validity Management**: Control package duration
- [ ] **Daily Billing Packages**: Packages with daily billing cycles
- [ ] **Trial Packages**: Limited trial packages for new customers
- [ ] **Temporary Packages**: Short-term package assignments

### Payment Management
- [ ] **Payment Processing**: Handle customer payments through multiple channels
- [ ] **Payment Gateway Integration**: Support for online payment gateways
- [ ] **Payment Statement**: Customer payment history and statements
- [ ] **Payment Link Broadcasting**: Send payment links to customers
- [ ] **Payment Verification**: Verify and approve payments
- [ ] **Payment Gateway Service Charge**: Configure transaction fees
- [ ] **Pending Transaction Management**: Handle incomplete transactions
- [ ] **Customer Payment History**: Complete payment audit trail
- [ ] **Advance Payment Handling**: Manage prepaid balances

### PPPoE Management
- [ ] **PPPoE Profile Management**: Create PPPoE server profiles
- [ ] **PPPoE Customer Management**: Manage PPPoE subscribers
- [ ] **PPPoE Username Management**: Handle username updates
- [ ] **PPPoE Password Management**: Secure password management
- [ ] **PPPoE Framed IP Address**: Assign static IPs to PPPoE users
- [ ] **PPPoE Group Management**: Organize PPPoE users in groups
- [ ] **PPPoE Import**: Bulk import PPPoE users
- [ ] **PPPoE Expiration**: Handle account expiration
- [ ] **PPPoE RADIUS Attributes**: Configure PPPoE-specific attributes
- [ ] **PPPoE Profile IP Allocation**: Dynamic vs static IP configuration

### Policies
- [ ] **Policy Management**: Define system policies
- [ ] **Fair Usage Policy**: Data throttling policies
- [ ] **Data Policy**: Data usage rules and restrictions
- [ ] **Privacy Policy**: Customer data protection policies

---

## Q

### Queue Management
- [ ] **Queue Connection**: Background job processing
- [ ] **Queue Management**: Monitor and manage job queues

### Quality Control
- [ ] **QoS Management**: Quality of Service configuration

---

## R

### Reports & Analytics
- [ ] **Financial Reports**: Revenue, expense, and profit reports
- [ ] **Customer Reports**: Customer statistics and analytics
- [ ] **Billing Reports**: Billing summary and details
- [ ] **Payment Reports**: Payment collection reports
- [ ] **Expense Reports**: Business expense summaries
- [ ] **BTRC Compliance Reports**: Regulatory reporting
- [ ] **Complaint Reports**: Support ticket analytics
- [ ] **Accounts Daily Report**: Daily financial summaries
- [ ] **Accounts Monthly Report**: Monthly financial analysis
- [ ] **Yearly Reports**: Annual business reports
- [ ] **Card Distributor Payment Reports**: Distributor transaction history
- [ ] **Operator Income Reports**: Per-operator earnings
- [ ] **Customer Bills Summary**: Aggregated billing data
- [ ] **Import Reports**: Bulk import operation results

### RADIUS Management
- [ ] **RADIUS Server Integration**: FreeRADIUS backend support
- [ ] **RADIUS Attribute Management**: Configure authentication attributes
- [ ] **RADIUS Group Management**: Group-based access control
- [ ] **RADIUS Accounting**: Session tracking and usage data
- [ ] **RADIUS Cache**: Cached RADIUS data for performance

### Recharge System
- [ ] **Recharge Card Generation**: Create prepaid cards
- [ ] **Recharge Card Management**: Track card inventory
- [ ] **Card Recharge Processing**: Apply card recharges to accounts
- [ ] **Recharge Card Download**: Export generated cards
- [ ] **Duplicate Card Prevention**: Ensure card uniqueness

### Reseller Management
- [ ] **Reseller Registration**: Onboard resellers
- [ ] **Reseller Management**: Manage reseller accounts
- [ ] **Reseller Commissions**: Calculate and track commissions

### RRD (Round-Robin Database)
- [ ] **RRD Graph Generation**: Network traffic graphs
- [ ] **RRD Database Management**: Time-series data storage

---

## S

### Sales Management
- [ ] **Sales Manager Role**: Dedicated sales team management
- [ ] **Sales Comments**: Track sales interactions
- [ ] **Sales Contact Information**: Sales team contact details
- [ ] **Sales Email Configuration**: Separate email for sales

### Security Features
- [ ] **Two-Factor Authentication**: Enhanced login security
- [ ] **Access Control Lists**: IP-based access restrictions
- [ ] **SSL Certificate Support**: Secure communications
- [ ] **CSRF Protection**: Cross-site request forgery prevention
- [ ] **Session Management**: Secure session handling
- [ ] **Password Reset**: Secure password recovery
- [ ] **Failed Login Tracking**: Brute force protection
- [ ] **Authentication Logs**: Security audit trail
- [ ] **BlackList Management**: Block problematic users

### Self-Service Portal
- [ ] **Customer Portal**: Self-service customer interface
- [ ] **Customer Web Interface**: Manage account online
- [ ] **Payment Processing**: Online payment acceptance
- [ ] **Bill Viewing**: Access billing history
- [ ] **Package Purchase**: Buy packages online
- [ ] **Complaint Submission**: Report issues online
- [ ] **Mobile Verification**: Verify contact information
- [ ] **Password Management**: Change passwords
- [ ] **Usage Monitoring**: View data/time usage

### Service Management
- [ ] **Service Activation**: Enable customer services
- [ ] **Service Suspension**: Temporarily disable services
- [ ] **Service Disconnection**: Permanently disable services
- [ ] **Service Package Management**: Manage subscribed services
- [ ] **After Payment Service**: Services triggered after payment
- [ ] **VPN Services**: Virtual private network offerings
- [ ] **Other Services**: Additional service types (IPTV, VoIP)

### SMS Features
- [ ] **SMS Gateway Management**: Configure SMS providers
- [ ] **SMS Broadcasting**: Bulk SMS campaigns
- [ ] **SMS Templates**: Predefined message templates
- [ ] **SMS History**: Complete sending logs
- [ ] **SMS Balance**: Credit tracking
- [ ] **SMS Payments**: SMS service billing
- [ ] **SMS Events**: Event-triggered messages
- [ ] **Minimum SMS Bill**: Billing threshold configuration
- [ ] **SMS Counter**: Character counting for billing
- [ ] **SMS Debug Mode**: Testing and troubleshooting

### Statistics & Widgets
- [ ] **Customer Statistics**: Growth and churn metrics
- [ ] **Complaint Statistics**: Support performance metrics
- [ ] **Active Customer Count**: Real-time active users
- [ ] **Disabled Customer Count**: Suspended accounts
- [ ] **Online Customer Count**: Current online users
- [ ] **Billed Customer Count**: Billing statistics
- [ ] **Amount Due**: Outstanding receivables
- [ ] **Amount Paid**: Collection metrics

### Subscription Management
- [ ] **Subscription Bills**: Recurring subscription billing
- [ ] **Subscription Payments**: Process subscription fees
- [ ] **Subscription Discounts**: Apply promotional discounts
- [ ] **Max Subscription Payment**: Payment limits

### System Configuration
- [ ] **System Settings**: Global configuration
- [ ] **Timezone Configuration**: Regional time settings
- [ ] **Currency Settings**: Multi-currency support
- [ ] **Language Settings**: Localization options
- [ ] **Email Configuration**: SMTP settings
- [ ] **Cache Configuration**: Performance optimization
- [ ] **Queue Configuration**: Background job settings
- [ ] **Backup Configuration**: Automated backup settings
- [ ] **Session Configuration**: Session timeout and storage

---

## T

### Technical Features
- [ ] **Template Management**: Blade template system
- [ ] **Temporary Customer Management**: Trial/temporary accounts
- [ ] **Temporary Billing Profiles**: Trial billing configurations
- [ ] **Temporary Packages**: Short-term package offers
- [ ] **Telegram Bot Integration**: Customer service via Telegram
- [ ] **Telegraph Chat Management**: Telegram chat handling
- [ ] **Testing Environment**: Sandbox/demo mode

### Tracking & Monitoring
- [ ] **Customer Count Tracking**: User base metrics
- [ ] **Device Monitoring**: Network device status
- [ ] **Internet History Tracking**: Usage history
- [ ] **Payment Tracking**: Transaction monitoring
- [ ] **SMS History Tracking**: Message delivery logs
- [ ] **Activity Tracking**: User action logs
- [ ] **Authentication Tracking**: Login/logout events

---

## U

### User Management
- [ ] **User Authentication**: Login and security
- [ ] **User Roles**: Admin, operator, customer, distributor
- [ ] **User Permissions**: Granular access control
- [ ] **User Profile Management**: Account information
- [ ] **Username Search**: Find users by username
- [ ] **User Session Management**: Active session tracking
- [ ] **Bulk User Updates**: Mass update operations

### Utility Features
- [ ] **Utility Functions**: Helper functions and utilities
- [ ] **URL Management**: Dynamic URL generation

---

## V

### VAT & Tax Management
- [ ] **VAT Collection**: Value-added tax tracking
- [ ] **VAT Profiles**: Multiple tax rate profiles
- [ ] **VAT Reports**: Tax collection reports
- [ ] **VAT Configuration**: Tax rate settings

### VPN Management
- [ ] **VPN Account Management**: VPN user accounts
- [ ] **VPN Pool Management**: VPN IP pool configuration
- [ ] **VPN Service Configuration**: VPN service settings

### VLAN Management
- [ ] **VLAN Configuration**: Virtual LAN setup
- [ ] **VLAN Management**: Create and manage VLANs

---

## W

### Widget System
- [ ] **Dashboard Widgets**: Customizable dashboard components
- [ ] **Active Customer Widget**: Display active users
- [ ] **Disabled Customer Widget**: Show disabled accounts
- [ ] **Online Customer Widget**: Real-time online count
- [ ] **Amount Due Widget**: Outstanding amounts
- [ ] **Amount Paid Widget**: Collected payments
- [ ] **Billed Customer Widget**: Billing statistics

### Web Interface
- [ ] **Web-based Administration**: Full web admin panel
- [ ] **Customer Web Portal**: Self-service portal
- [ ] **Responsive Design**: Mobile-friendly interface
- [ ] **Card Distributor Portal**: Distributor interface

---

## X-Y-Z

### XML/Excel Import
- [ ] **Excel Customer Import**: Import from spreadsheets
- [ ] **XML Configuration Import**: Import system configurations

### Zone Management
- [ ] **Customer Zone Management**: Geographic organization
- [ ] **Zone-based Reporting**: Location-based analytics
- [ ] **Zone Configuration**: Define coverage areas

### Yearly Reports
- [ ] **Yearly Card Distributor Payments**: Annual distributor reports
- [ ] **Yearly Cash In**: Annual income reports
- [ ] **Yearly Cash Out**: Annual expense reports
- [ ] **Yearly Operator Income**: Annual operator earnings
- [ ] **Yearly Expense Reports**: Annual cost analysis

---


@copilot follow this file and develop feature by taking knowledge from this file
