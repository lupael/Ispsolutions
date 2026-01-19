# Unified Enhanced Multi-Tenancy ISP Billing System

This document consolidates and rephrases the previous ISP Billing System Feature List with the new multi-tenancy isolation architecture and upgraded technology stack.

## 🔥 Recent Updates (January 2026)

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


## Data Isolation & Roles Clarified:

Developer: Supreme authority. All tenants (can create/manage tenants)
Super Admin: Only OWN tenants. Represents the overarching tenant context. (can create/manage admins)
Admin: Admin (Formerly Group Admin) ISP Owner, Own ISP data within a tenancy (can create/manage operators)
Operator: Own + sub-operator customers (can create/manage sub-operators)
Sub-Operator: Only own customers
Manager/Staff: View based on permissions

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

## Multi-Tenancy Infrastructure
- **Tenant Isolation**: Global scopes enforce tenant-specific query filtering.  
- **Automatic Tenant Assignment**: Models with `BelongsToTenant` auto-assign `tenant_id`.  
- **Domain/Subdomain Resolution**: Middleware resolves tenant from request host.  
- **Role-Based Permissions**: Fine-grained access control.  
- **Soft Deletes**: Tenant data preserved with soft deletion.  

---

## Models
- **Tenant Model**: Manages tenant metadata (domain, subdomain, database, settings, status).  
  - Supports soft deletes  
  - Relationships with users, IP pools, packages, and network users  

- **Role Model**: Defines hierarchical roles (levels 0–100) with permissions.  
  - Helpers: `hasPermission()`, `getPermissions()`  
  - Many-to-many user relationships  

---

## Migrations
- `create_tenants_table`: Tenant metadata with domain/subdomain  
- `create_roles_table`: Roles and role_user pivot  
- `add_tenant_id_to_tables`: Adds `tenant_id` to users, service_packages, ip_pools, ip_subnets, ip_allocations, network_users, mikrotik_routers  

---

## Services
- **TenancyService**:  
  - Manages tenant context  
  - Resolves tenant by domain/subdomain  
  - Executes callbacks in tenant scope  
  - Caches for performance  

---

## Traits
- **BelongsToTenant**:  
  - Auto-sets `tenant_id`  
  - Adds global scope  
  - Provides `forTenant()` and `allTenants()` scopes  

---

## Middleware
- **ResolveTenant**:  
  - Resolves tenant from request host  
  - Sets tenant in `TenancyService`  
  - Allows public routes  
  - Returns 404 for invalid tenants  

---

## Service Provider
- **TenancyServiceProvider**: Registers `TenancyService` as singleton in `bootstrap/providers.php`.  

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
- **Access Control List (ACL)**: CIDR-based IP access restrictions for administrative interface
- **Activity Logging**: Complete audit trail of all user actions and system events
- **Admin Authentication**: Multi-level admin authentication system
- **Affiliate Program**: Referral and commission tracking system for customer acquisition
- **API Authentication**: Token-based API authentication for external integrations
- **Authentication Logs**: Failed and successful login attempt tracking
- **Auto Debit**: Automatic payment collection from customer accounts

### Account Management
- **Account Balance Management**: Track and manage customer account balances
- **Account Holder Details**: Comprehensive account holder information management
- **Account Statement Generation**: Detailed financial statements for customers
- **Accounts Receivable**: Track outstanding payments and dues
- **Accounts Daily Reports**: Daily financial activity summaries
- **Accounts Monthly Reports**: Monthly aggregated financial reports
- **Advance Payments**: Customer advance payment handling and tracking

### Administrative Features
- **Admin Dashboard**: Comprehensive dashboard with key metrics and charts
- **Admin Roles & Permissions**: Granular permission system for different admin types
- **Archived Complaints**: Historical complaint record management
- **ARP Management**: Address Resolution Protocol table management for network devices

---

## B

### Billing & Invoicing
- **Billing Profile Management**: Create and manage different billing profiles
- **Billing Profile Operator Assignment**: Assign operators to specific billing profiles
- **Billing Profile Replacement**: Bulk replacement of customer billing profiles
- **Bill Generation**: Automated and manual customer bill generation
- **Bill Payment Processing**: Process customer bill payments through multiple channels
- **Bills Summary Reports**: Comprehensive billing summary and analytics
- **Bills vs Payments Chart**: Visual comparison of billed amounts vs collected payments
- **Bulk Bill Generation**: Generate bills for multiple customers simultaneously
- **Bulk Bill Payment Processing**: Process multiple payments in batch operations
- **BTRC Reports**: Bangladesh Telecom Regulatory Commission compliance reports

### Backup & Data Management
- **Backup Settings**: Configure automated backup schedules and destinations
- **Customer Backup Requests**: Handle customer data backup requests
- **Database Backup**: Automated database backup to multiple destinations (FTP, SFTP, local)
- **Bridge Management**: Network bridge configuration and management

### Business Intelligence
- **Bills vs Payments Analysis**: Financial analysis and tracking
- **Business Statistics**: Customer growth, revenue, and churn analytics
- **Billed Customer Widget**: Dashboard widget showing billing statistics

---

## C

### Customer Management
- **Customer Registration**: New customer onboarding and registration
- **Customer Activation**: Activate new or suspended customer accounts
- **Customer Suspension**: Temporarily suspend customer services
- **Customer Disable**: Permanently disable customer accounts
- **Customer Details Management**: Comprehensive customer information management
- **Customer Search**: Search by username, mobile, name, customer ID
- **Customer Import**: Bulk import customers from Excel/CSV files
- **Customer Export**: Export customer data in various formats
- **Customer Backup**: Individual customer data backup and restore
- **Customer Zones**: Geographic zone-based customer organization
- **Customer Custom Attributes**: Flexible custom fields for customer data
- **Customer Change Log**: Track all changes made to customer records
- **Customer Package Change**: Change customer service packages
- **Customer Package Purchase**: Handle new package purchases
- **Child Customer Accounts**: Create sub-accounts under parent customers
- **Calling Station ID Management**: MAC address tracking and management
- **Credit Limit Management**: Set and manage customer credit limits

### Complaints & Support
- **Complaint Management System**: Complete ticketing system for customer issues
- **Complaint Categories**: Organize complaints by category
- **Complaint Department Assignment**: Route complaints to appropriate departments
- **Complaint Comments**: Thread-based comment system for complaint resolution
- **Complaint Acknowledgement**: Acknowledge and track complaint responses
- **Complaint Ledger**: Financial tracking for complaint-related costs
- **Complaint Statistics**: Analytics and reporting on complaint metrics
- **Complaint Reports**: Generate comprehensive complaint reports

### Card & Recharge System
- **Recharge Card Generation**: Generate prepaid recharge cards
- **Recharge Card Management**: Track and manage recharge card inventory
- **Card Distributors**: Manage recharge card distributor network
- **Card Distributor Payments**: Process distributor commission payments
- **Card Distributor Dashboard**: Separate interface for card distributors
- **Card Usage Tracking**: Monitor card usage and redemption
- **Card Validation**: Prevent duplicate cards and validate authenticity

### Cash Management
- **Cash In Tracking**: Record cash received from various sources
- **Cash Out Tracking**: Record cash disbursements and expenses
- **Cash Payment Invoices**: Generate runtime invoices for cash payments
- **Cash Received Entry**: Manual cash receipt entry system

### Communication
- **SMS Gateway Integration**: Multiple SMS gateway support
- **SMS Broadcasting**: Send bulk SMS to customers
- **SMS History**: Complete SMS sending history and logs
- **SMS Balance Tracking**: Monitor SMS credit balance
- **SMS Payment Management**: Handle SMS service payments
- **SMS Events**: Automated SMS for specific events (payment received, bill generated, etc.)
- **Email Notifications**: Automated email notifications for various events
- **Telegram Integration**: Bot integration for customer notifications
- **Telegram Chat Management**: Handle customer queries via Telegram

### Configuration & Settings
- **Custom Fields**: Create custom fields for customers and other entities
- **Custom Pricing**: Set customer-specific pricing overrides
- **Country Configuration**: Multi-country support with timezone and currency
- **Currency Settings**: Support for multiple currencies
- **Cache Management**: System cache clearing and optimization

---

## D

### Dashboard & Analytics
- **Dashboard Widgets**: Customizable dashboard with various widgets
- **Active Customer Widget**: Display currently active customers
- **Disabled Customer Widget**: Show disabled customer count
- **Amount Due Widget**: Display total outstanding amounts
- **Amount Paid Widget**: Show total collected payments
- **Online Customer Widget**: Real-time online customer count
- **Dashboard Charts**: Visual charts for billing, payments, and customer statistics
- **Customer Statistics Chart**: Customer growth and churn visualization
- **Complaint Statistics Chart**: Complaint trend analysis
- **Income vs Expense Chart**: Financial performance visualization

### Device Management
- **Device Registration**: Register network devices (routers, switches)
- **Device Monitoring**: Real-time device status monitoring
- **Device Identification**: Track and identify network devices
- **Device Configuration Export**: Export device configurations
- **Device Status Tracking**: Monitor uptime and connectivity

### Data Management
- **Data Policy Management**: Configure data usage policies
- **Database Connection Management**: Multi-node database architecture support
- **Database Synchronization**: Sync data across multiple nodes
- **Deleted Customer Management**: Manage soft-deleted customer records
- **Department Management**: Organize staff by departments
- **Developer Tools**: Development and debugging utilities
- **Disabled Filters**: Manage disabled system filters
- **Disabled Menus**: Control menu item visibility
- **Download Management**: Handle bulk download operations
- **Due Date Reminders**: Automated reminders for payment due dates
- **Due Notifier**: Notification system for overdue payments
- **Duplicate Customer Check**: Prevent duplicate customer entries

---

## E

### Expense Management
- **Expense Tracking**: Record and categorize business expenses
- **Expense Categories**: Organize expenses by categories
- **Expense Subcategories**: Further categorize expenses
- **Expense Reports**: Generate detailed expense reports
- **Expense Details**: View comprehensive expense information
- **Yearly Expense Reports**: Annual expense summaries

### Event Management
- **Event SMS**: Trigger SMS based on system events
- **Expiration Notifier**: Notify customers of package expiration
- **Extend Package Validity**: Manually extend package expiration dates

### Exam System
- **Exam Management**: Create and manage exams
- **Question Management**: Create exam questions
- **Question Options**: Multiple choice answer options
- **Question Answers**: Define correct answers
- **Question Explanations**: Provide answer explanations
- **Exam Attendance**: Track exam participation

### Exchange & Trading
- **Exchange Account Balance**: Inter-operator balance transfers
- **External Router Management**: Manage foreign/external routers

---

## F

### FreeRADIUS Integration
- **NAS (Network Access Server) Management**: Configure RADIUS NAS devices
- **RADIUS Accounting**: Track user session accounting data (radacct)
- **RADIUS Checks**: Manage user authentication attributes (radcheck)
- **RADIUS Replies**: Configure response attributes (radreply)
- **RADIUS Group Checks**: Group-based authentication rules (radgroupcheck)
- **RADIUS Group Replies**: Group-based response attributes (radgroupreply)
- **RADIUS User Groups**: Assign users to RADIUS groups (radusergroup)
- **RADIUS Post Auth**: Track authentication attempts (radpostauth)
- **RADIUS Accounting History**: Historical session data

### Fair Usage Policy
- **Fair Usage Policy (FUP)**: Configure data usage limits and throttling
- **FUP Activation**: Activate FUP for specific customers
- **FUP Management**: Create and manage multiple FUP profiles

### Financial Management
- **Financial Reports**: Comprehensive financial reporting
- **Foreign Currency Support**: Handle multiple currency transactions

### Forms & UI
- **Form Builder**: Dynamic form creation system
- **Firewall Management**: Customer-specific firewall rules

### Failed Operations
- **Failed Login Tracking**: Monitor and log failed login attempts
- **Failed Job Management**: Handle failed background jobs

---

## G

### Gateway Integration
- **Payment Gateway Integration**: Multiple payment gateway support (bKash, Nagad, etc.)
- **Payment Gateway Service Charges**: Configure transaction fees
- **Payment Gateway Temporary Failure Handling**: Manage failed transactions

### Group Management
- **Admin (Formerly Group Admin) Management**: Manage Admin (Formerly Group Admin)istrators
- **Group-based Permissions**: Permission assignment by admin groups

### General Features
- **Global Customer Search**: Search customers across all parameters
- **General Complaints**: Handle general customer complaints

---

## H

### Hotspot Management
- **Hotspot User Management**: Manage hotspot customers
- **Hotspot Login System**: Customer portal for hotspot access
- **Hotspot Internet Login**: Web-based internet authentication
- **Hotspot Package Change**: Change hotspot customer packages
- **Hotspot Recharge**: Top-up hotspot accounts
- **Hotspot User Profiles**: Configure user profile templates
- **Hotspot Customer Expiration**: Handle hotspot account expiration
- **Hotspot RADIUS Attributes**: Manage hotspot-specific RADIUS attributes

### Helper Functions
- **IPv4 Helper Functions**: IP address manipulation utilities
- **IPv6 Helper Functions**: IPv6 address handling
- **Billing Helper Functions**: Reusable billing calculation utilities

---

## I

### Income Management
- **Income Tracking**: Record income from various sources
- **Operator Income**: Track per-operator income
- **Yearly Operator Income**: Annual income reports per operator
- **Income vs Expense Analysis**: Comparative financial analysis

### IP Management
- **IPv4 Pool Management**: Create and manage IPv4 address pools
- **IPv4 Address Assignment**: Assign static IP addresses to customers
- **IPv4 Pool Replacement**: Bulk replace IPv4 pools
- **IPv4 Pool Subnet Management**: Configure pool subnets
- **IPv6 Pool Management**: Manage IPv6 address pools
- **IP Address Tracking**: Monitor IP address allocation

### Import/Export
- **Customer Import**: Bulk import customers from files
- **Customer Import Reports**: Track import operation results
- **Customer Import Requests**: Manage import job queue
- **PPPoE Customer Import**: Import PPPoE users from external systems
- **Excel Import**: Import data from Excel files
- **Configuration Export**: Export system configurations

### Interface Management
- **Interface Management**: Configure network interfaces
- **Interface Monitoring**: Track interface status and statistics

### Invoice & Printing
- **Invoice Generation**: Create customer invoices
- **Invoice Printing**: Print-ready invoice formatting
- **Runtime Invoice Creation**: Generate invoices on-demand

### ISP Information
- **ISP Information Management**: Configure ISP details and branding

---

## L

### Language & Localization
- **Multi-language Support**: Support for multiple languages
- **Language Configuration**: Manage language settings
- **Localization**: Regional settings and formats

### Logging & Monitoring
- **Activity Logs**: Comprehensive system activity logging
- **Log Viewer**: Browse and search system logs
- **Authentication Logs**: Track authentication events
- **SMS History Logs**: Complete SMS sending records
- **Internet History**: Customer internet usage history

### Login & Authentication
- **Admin Login**: Administrator authentication
- **Customer Web Login**: Customer portal authentication
- **Card Distributor Login**: Distributor portal access
- **Two-Factor Authentication (2FA)**: Enhanced security with 2FA
- **Mobile Verification**: Verify customer mobile numbers
- **Login Attempt Tracking**: Monitor login attempts

---

## M

### MikroTik Integration
- **MikroTik Database Sync**: Synchronize with MikroTik routers
- **MikroTik PPPoE Profiles**: Manage PPPoE server profiles
- **MikroTik PPPoE Secrets**: Manage PPPoE user credentials
- **MikroTik Hotspot Users**: Sync hotspot user database
- **MikroTik Hotspot Profiles**: Configure hotspot user profiles
- **MikroTik IP Pools**: Manage MikroTik IP pool configuration
- **MikroTik API Integration**: Direct API communication with routers

### Management Features
- **Manager Roles**: Sales manager and general manager roles
- **Mandatory Customer Attributes**: Define required customer fields
- **MAC Address Management**: Track and bind MAC addresses
- **MAC Address Replacement**: Bulk MAC address updates
- **Master Package Management**: Template packages for resellers
- **Max Subscription Payment**: Configure maximum payment limits
- **Minimum SMS Bill**: Set minimum SMS billing threshold

---

## N

### Network Management
- **NAS Management**: Network Access Server configuration
- **NAS PPPoE Profile Mapping**: Link NAS to PPPoE profiles
- **Network Device Monitoring**: Real-time network device status
- **Network Interface Management**: Configure router interfaces

### Notification System
- **Email Notifications**: Automated email alerts
- **SMS Notifications**: Automated SMS alerts
- **Payment Notifications**: Payment confirmation messages
- **Due Date Notifications**: Payment reminder system
- **Expiration Notifications**: Service expiration alerts
- **Developer Notice Broadcast**: System-wide announcements

---

## O

### Operator Management
- **Operator Registration**: Register sub-operators/resellers
- **Sub-operator Management**: Hierarchical operator structure
- **Operator Permissions**: Granular permission control per operator
- **Operator Packages**: Package assignment to operators
- **Operator Payments**: Process operator commission payments
- **Operator Income Tracking**: Track operator earnings
- **Operator Change**: Transfer customers between operators
- **Operator Billing Profile**: Operator-specific billing configurations
- **Operator Delete**: Remove operators and handle data migration
- **Operator Online Payments**: Track operator online payment collections

### Online Features
- **Online Customer Tracking**: Real-time online user monitoring
- **Online Customer Widget**: Dashboard display of online users
- **Online Payment Processing**: Accept online payments

### Other Services
- **Other Service Management**: Non-internet services (IPTV, VoIP, etc.)

---

## P

### Package Management
- **Package Creation**: Define service packages
- **Package Configuration**: Set package parameters (speed, data limit, duration)
- **Package Pricing**: Configure package pricing
- **Package Replacement**: Bulk replace packages for customers
- **Package Validity Management**: Control package duration
- **Daily Billing Packages**: Packages with daily billing cycles
- **Trial Packages**: Limited trial packages for new customers
- **Temporary Packages**: Short-term package assignments

### Payment Management
- **Payment Processing**: Handle customer payments through multiple channels
- **Payment Gateway Integration**: Support for online payment gateways
- **Payment Statement**: Customer payment history and statements
- **Payment Link Broadcasting**: Send payment links to customers
- **Payment Verification**: Verify and approve payments
- **Payment Gateway Service Charge**: Configure transaction fees
- **Pending Transaction Management**: Handle incomplete transactions
- **Customer Payment History**: Complete payment audit trail
- **Advance Payment Handling**: Manage prepaid balances

### PPPoE Management
- **PPPoE Profile Management**: Create PPPoE server profiles
- **PPPoE Customer Management**: Manage PPPoE subscribers
- **PPPoE Username Management**: Handle username updates
- **PPPoE Password Management**: Secure password management
- **PPPoE Framed IP Address**: Assign static IPs to PPPoE users
- **PPPoE Group Management**: Organize PPPoE users in groups
- **PPPoE Import**: Bulk import PPPoE users
- **PPPoE Expiration**: Handle account expiration
- **PPPoE RADIUS Attributes**: Configure PPPoE-specific attributes
- **PPPoE Profile IP Allocation**: Dynamic vs static IP configuration

### Policies
- **Policy Management**: Define system policies
- **Fair Usage Policy**: Data throttling policies
- **Data Policy**: Data usage rules and restrictions
- **Privacy Policy**: Customer data protection policies

---

## Q

### Queue Management
- **Queue Connection**: Background job processing
- **Queue Management**: Monitor and manage job queues

### Quality Control
- **QoS Management**: Quality of Service configuration

---

## R

### Reports & Analytics
- **Financial Reports**: Revenue, expense, and profit reports
- **Customer Reports**: Customer statistics and analytics
- **Billing Reports**: Billing summary and details
- **Payment Reports**: Payment collection reports
- **Expense Reports**: Business expense summaries
- **BTRC Compliance Reports**: Regulatory reporting
- **Complaint Reports**: Support ticket analytics
- **Accounts Daily Report**: Daily financial summaries
- **Accounts Monthly Report**: Monthly financial analysis
- **Yearly Reports**: Annual business reports
- **Card Distributor Payment Reports**: Distributor transaction history
- **Operator Income Reports**: Per-operator earnings
- **Customer Bills Summary**: Aggregated billing data
- **Import Reports**: Bulk import operation results

### RADIUS Management
- **RADIUS Server Integration**: FreeRADIUS backend support
- **RADIUS Attribute Management**: Configure authentication attributes
- **RADIUS Group Management**: Group-based access control
- **RADIUS Accounting**: Session tracking and usage data
- **RADIUS Cache**: Cached RADIUS data for performance

### Recharge System
- **Recharge Card Generation**: Create prepaid cards
- **Recharge Card Management**: Track card inventory
- **Card Recharge Processing**: Apply card recharges to accounts
- **Recharge Card Download**: Export generated cards
- **Duplicate Card Prevention**: Ensure card uniqueness

### Reseller Management
- **Reseller Registration**: Onboard resellers
- **Reseller Management**: Manage reseller accounts
- **Reseller Commissions**: Calculate and track commissions

### RRD (Round-Robin Database)
- **RRD Graph Generation**: Network traffic graphs
- **RRD Database Management**: Time-series data storage

---

## S

### Sales Management
- **Sales Manager Role**: Dedicated sales team management
- **Sales Comments**: Track sales interactions
- **Sales Contact Information**: Sales team contact details
- **Sales Email Configuration**: Separate email for sales

### Security Features
- **Two-Factor Authentication**: Enhanced login security
- **Access Control Lists**: IP-based access restrictions
- **SSL Certificate Support**: Secure communications
- **CSRF Protection**: Cross-site request forgery prevention
- **Session Management**: Secure session handling
- **Password Reset**: Secure password recovery
- **Failed Login Tracking**: Brute force protection
- **Authentication Logs**: Security audit trail
- **BlackList Management**: Block problematic users

### Self-Service Portal
- **Customer Portal**: Self-service customer interface
- **Customer Web Interface**: Manage account online
- **Payment Processing**: Online payment acceptance
- **Bill Viewing**: Access billing history
- **Package Purchase**: Buy packages online
- **Complaint Submission**: Report issues online
- **Mobile Verification**: Verify contact information
- **Password Management**: Change passwords
- **Usage Monitoring**: View data/time usage

### Service Management
- **Service Activation**: Enable customer services
- **Service Suspension**: Temporarily disable services
- **Service Disconnection**: Permanently disable services
- **Service Package Management**: Manage subscribed services
- **After Payment Service**: Services triggered after payment
- **VPN Services**: Virtual private network offerings
- **Other Services**: Additional service types (IPTV, VoIP)

### SMS Features
- **SMS Gateway Management**: Configure SMS providers
- **SMS Broadcasting**: Bulk SMS campaigns
- **SMS Templates**: Predefined message templates
- **SMS History**: Complete sending logs
- **SMS Balance**: Credit tracking
- **SMS Payments**: SMS service billing
- **SMS Events**: Event-triggered messages
- **Minimum SMS Bill**: Billing threshold configuration
- **SMS Counter**: Character counting for billing
- **SMS Debug Mode**: Testing and troubleshooting

### Statistics & Widgets
- **Customer Statistics**: Growth and churn metrics
- **Complaint Statistics**: Support performance metrics
- **Active Customer Count**: Real-time active users
- **Disabled Customer Count**: Suspended accounts
- **Online Customer Count**: Current online users
- **Billed Customer Count**: Billing statistics
- **Amount Due**: Outstanding receivables
- **Amount Paid**: Collection metrics

### Subscription Management
- **Subscription Bills**: Recurring subscription billing
- **Subscription Payments**: Process subscription fees
- **Subscription Discounts**: Apply promotional discounts
- **Max Subscription Payment**: Payment limits

### System Configuration
- **System Settings**: Global configuration
- **Timezone Configuration**: Regional time settings
- **Currency Settings**: Multi-currency support
- **Language Settings**: Localization options
- **Email Configuration**: SMTP settings
- **Cache Configuration**: Performance optimization
- **Queue Configuration**: Background job settings
- **Backup Configuration**: Automated backup settings
- **Session Configuration**: Session timeout and storage

---

## T

### Technical Features
- **Template Management**: Blade template system
- **Temporary Customer Management**: Trial/temporary accounts
- **Temporary Billing Profiles**: Trial billing configurations
- **Temporary Packages**: Short-term package offers
- **Telegram Bot Integration**: Customer service via Telegram
- **Telegraph Chat Management**: Telegram chat handling
- **Testing Environment**: Sandbox/demo mode

### Tracking & Monitoring
- **Customer Count Tracking**: User base metrics
- **Device Monitoring**: Network device status
- **Internet History Tracking**: Usage history
- **Payment Tracking**: Transaction monitoring
- **SMS History Tracking**: Message delivery logs
- **Activity Tracking**: User action logs
- **Authentication Tracking**: Login/logout events

---

## U

### User Management
- **User Authentication**: Login and security
- **User Roles**: Admin, operator, customer, distributor
- **User Permissions**: Granular access control
- **User Profile Management**: Account information
- **Username Search**: Find users by username
- **User Session Management**: Active session tracking
- **Bulk User Updates**: Mass update operations

### Utility Features
- **Utility Functions**: Helper functions and utilities
- **URL Management**: Dynamic URL generation

---

## V

### VAT & Tax Management
- **VAT Collection**: Value-added tax tracking
- **VAT Profiles**: Multiple tax rate profiles
- **VAT Reports**: Tax collection reports
- **VAT Configuration**: Tax rate settings

### VPN Management
- **VPN Account Management**: VPN user accounts
- **VPN Pool Management**: VPN IP pool configuration
- **VPN Service Configuration**: VPN service settings

### VLAN Management
- **VLAN Configuration**: Virtual LAN setup
- **VLAN Management**: Create and manage VLANs

---

## W

### Widget System
- **Dashboard Widgets**: Customizable dashboard components
- **Active Customer Widget**: Display active users
- **Disabled Customer Widget**: Show disabled accounts
- **Online Customer Widget**: Real-time online count
- **Amount Due Widget**: Outstanding amounts
- **Amount Paid Widget**: Collected payments
- **Billed Customer Widget**: Billing statistics

### Web Interface
- **Web-based Administration**: Full web admin panel
- **Customer Web Portal**: Self-service portal
- **Responsive Design**: Mobile-friendly interface
- **Card Distributor Portal**: Distributor interface

---

## X-Y-Z

### XML/Excel Import
- **Excel Customer Import**: Import from spreadsheets
- **XML Configuration Import**: Import system configurations

### Zone Management
- **Customer Zone Management**: Geographic organization
- **Zone-based Reporting**: Location-based analytics
- **Zone Configuration**: Define coverage areas

### Yearly Reports
- **Yearly Card Distributor Payments**: Annual distributor reports
- **Yearly Cash In**: Annual income reports
- **Yearly Cash Out**: Annual expense reports
- **Yearly Operator Income**: Annual operator earnings
- **Yearly Expense Reports**: Annual cost analysis

---

## Additional System Features

### Architecture & Technical
- **Multi-tenant Architecture**: Support for multiple ISPs
- **Central Database Management**: Centralized control with distributed nodes
- **Laravel Framework**: Built on Laravel
- **MySQL/PostgreSQL Support**: Multiple database backend support
- **Redis Cache**: High-performance caching
- **Queue System**: Asynchronous job processing
- **RESTful API**: External integration capabilities
- **MVC Architecture**: Model-View-Controller design pattern
- **Middleware System**: Request filtering and processing
- **Event System**: Event-driven architecture
- **Observer Pattern**: Model event handling
- **Policy-based Authorization**: Resource access control
- **Job Queue**: Background task processing
- **Mail Queue**: Asynchronous email sending
- **Database Migrations**: Version-controlled schema changes
- **Seeding System**: Test data generation
- **Factory Pattern**: Model instance generation
- **Trait System**: Reusable code components
- **Helper Functions**: Utility functions library
- **Dockerization**: Container support


### Integration Capabilities
- **bKash Integration**: Mobile financial service
- **Nagad Integration**: Mobile payment gateway
- **RADIUS Integration**: Authentication server
- **MikroTik API**: Router management
- **Intercom/Telegram Bot API**: Messaging integration
- **SMS Gateway APIs**: Multiple provider support
- **Payment Gateway APIs**: Online payment processing
- **Email Services**: SMTP and API-based email
- **Slack Notifications**: Team alerts
- **Recaptcha**: Bot protection
- **Google 2FA**: Two-factor authentication
- **Chrome Headless**: PDF generation
- **QR Code Generation**: Quick response codes
- **Excel Export/Import**: Spreadsheet handling
- **PDF Generation**: Invoice and report PDFs
- **Image Processing**: Logo and avatar handling
- **Backup Services**: FTP/SFTP backup

### Compliance & Regulation
- **BTRC Compliance**: Bangladesh telecom regulations
- **Data Privacy**: Customer data protection
- **Audit Trail**: Complete activity logging
- **Regulatory Reporting**: Compliance report generation

### Performance Features
- **Caching System**: Redis-based caching
- **Database Indexing**: Optimized queries
- **Lazy Loading**: On-demand data loading
- **Pagination**: Efficient data browsing
- **AJAX Operations**: Asynchronous updates
- **Background Jobs**: Async processing
- **Query Optimization**: Efficient database access

### Developer Features
- **Debug Bar**: Development debugging tool
- **Log Viewer**: System log browser
- **API Documentation**: REST API specs
- **Database Migrations**: Schema version control
- **Code Style**: PSR standards compliance
- **Testing Framework**: PHPUnit integration
- **Factory Pattern**: Test data generation
- **Demo Mode**: Safe testing environment

---

## Roles, Permissions, and Access Control

### Overview


## Governance & Roles (UPDTE)
- **Developer**: Supreme authority and source code owner, with unrestricted permissions.  
- **Super Admin**: Tenancy owner. Represents the overarching tenant context.  
- **Admin (Formerly Group Admin)**: Manages ISP-specific operations within a tenancy. 

---

The ISP Billing System implements a comprehensive **9-tier role-based access control (RBAC)** system with hierarchical relationships, granular permissions, and flexible menu/panel access controls. This system ensures proper security, delegation, and workflow management across the organization.

---

### System Roles

The system defines **9 distinct roles** with hierarchical authority levels and specific functional responsibilities:

## Governance & Roles (UPDTE)
- **Developer**: Supreme authority and source code owner, with unrestricted permissions.  
- **Super Admin**: Tenancy owner. Represents the overarching tenant context.  
- **Admin (Formerly Group Admin)**: Manages ISP-specific operations within a tenancy.  

---
#### 1. **Developer** (Level 1 - Highest Authority)
- **Description**: Top-level system administrator with unrestricted access
- **Hierarchy**: Root of the entire system 
- **Key Responsibilities**:
  - Configure SMS gateways
  - Configure payment gateways
  - Access system logs and debugging tools
  - Manage API integrations
  - Access VPN pools and technical configurations
- **Panel Access**: Developer panel with technical features
- **Permissions**: Technical configuration access, API management
- **Technical Reference**: `operators.role = 'developer'`



#### 2. **Super Admin** (Level 2 - tenant Authority)
- **Description**: Top-level tenant administrator with unrestricted access to own tenant
- **Hierarchy**: Special access role identified by `sid` field (typically reports to Developer)
- **Key Responsibilities**:
  - Oversee all Admin (Formerly Group Admin)s and their operations
  - Manage tenant-wide configurations
  - Suspend/activate Admin/operator subscriptions
  - Access all features and data across the tenant
  - Handle billing and subscriptions for Admins
- **Restrictions**: None - full system access to own tenant
- **Technical Reference**: `operators.role = 'super_admin'`

#### 3. **Admin** (Level 3 - ISP/Master Account)
- **Description**: Main ISP distributor managing operators and their customers
- **Hierarchy**: Reports to Super Admin (identified by `mgid` field)
- **Key Responsibilities**:
  - Create and manage Operators and Sub-operators
  - Create and assign Managers
  - Manage master packages and billing profiles
  - Configure PPPoE profiles, IP pools (IPv4/IPv6), and NAS devices
  - Assign special permissions to Operators
  - Control menu visibility for child operators
  - Manage recharge card system
  - Access all customer data within their group
  - Handle VAT, expenses, and financial operations
  - Generate BTRC compliance reports
- **Panel Access**: Full administrative panel with all menus
- **Account Types**: Credit (postpaid) or Debit (prepaid) models
- **Technical Reference**: `operators.role = 'admin'`, `operators.mgid = [self_id]`

#### 4. **Operator** (Level 4 - Reseller)
- **Description**: Primary reseller managing their own customer base
- **Hierarchy**: Reports to Admin (identified by `gid` field where `gid === mgid`)
- **Key Responsibilities**:
  - Manage assigned customers
  - Create and manage Sub-operators
  - Process bills and payments
  - Generate customer invoices
  - Access assigned packages and billing profiles
  - Handle customer complaints and support
  - Use assigned special permissions (if granted)
  - Manage recharge cards (if enabled)
- **Panel Access**: Operator panel with restricted menus based on disabled_menus configuration
- **Permissions**: Base permissions + optional special permissions assigned by Admin
- **Technical Reference**: `operators.role = 'operator'`, `operators.gid = operators.mgid`

#### 5. **Sub-Operator** (Level 5 - Sub-Reseller)
- **Description**: Secondary reseller under an Operator
- **Hierarchy**: Reports to Operator (identified by `gid` field where `gid !== mgid`)
- **Key Responsibilities**:
  - Manage assigned customer subset
  - Process customer bills and payments
  - Handle customer support within scope
  - Access limited packages and profiles
  - Use assigned special permissions (if granted)
- **Panel Access**: Restricted panel similar to Operator but with further limitations
- **Permissions**: Base permissions + optional special permissions assigned by Admin
- **Technical Reference**: `operators.role = 'operator'` where `operators.gid != operators.mgid` (role_alias = 'sub_operator')
- **Note**: Sub-operator is determined by the relationship between `gid` and `mgid`, not a separate role enum

#### 6. **Manager** (Level 6 - Staff Role)
- **Description**: Support staff under Admin with specific operational duties
- **Hierarchy**: Works under Admin (identified by `gid` field)
- **Key Responsibilities**:
  - View and manage customers based on assigned permissions
  - Process payments and generate bills
  - Handle complaints assigned to their department
  - Access features based on assigned permissions
  - Limited package and profile access
- **Panel Access**: Manager panel with feature-specific access
- **Permissions**: Base permissions + optional special permissions assigned by Admin
- **Department Assignment**: Can be assigned to specific departments for complaint routing
- **Technical Reference**: `operators.role = 'manager'`

#### 7. **Card Distributor** (Level 7 - Recharge Card Vendor)
- **Description**: Third-party distributor managing recharge card sales
- **Hierarchy**: Associated with specific Operator/Admin
- **Key Responsibilities**:
  - View assigned recharge card inventory
  - Track card sales and commissions
  - Generate distributor reports
  - Access card distributor portal
- **Panel Access**: Separate card distributor portal (not admin panel)
- **Permissions**: Read-only access to assigned cards and sales data
- **Portal**: Dedicated UI at `/card-distributors/*` routes
- **Technical Reference**: `card_distributors` table with `operator_id` foreign key

#### 8. **Sales Manager** (Level 8 - Sales Team)
- **Description**: Sales-focused role for customer acquisition and relationship management
- **Hierarchy**: Reports to Admin
- **Key Responsibilities**:
  - Track customer acquisition
  - Manage sales leads and contacts
  - Record sales comments and interactions
  - Generate sales reports
  - Limited customer management access
- **Panel Access**: Sales-focused panel features
- **Permissions**: Customer viewing, sales tracking, basic reporting
- **Technical Reference**: `operators.role = 'sales_manager'`



#### 9. **Accountant** (Level 9 - Financial Operations)
- **Description**: Financial role for accounting and bookkeeping
- **Hierarchy**: Reports to Admin
- **Key Responsibilities**:
  - View financial reports and statements
  - Track income and expenses
  - Generate accounting reports
  - Manage VAT collections
  - Handle cash in/out entries
  - Access customer payment history
- **Panel Access**: Accounting-focused panel
- **Permissions**: Financial viewing and reporting (typically read-only)
- **Technical Reference**: `operators.role = 'accountant'`

---

### Hierarchical Relationships

## Governance & Roles (UPDTE)
- **Developer**: Supreme authority and source code owner, with unrestricted permissions.  
- **Super Admin**: Tenancy owner. Represents the overarching tenant context.  
- **Admin (Formerly Group Admin)**: Manages ISP-specific operations within a tenancy. 

---
The system uses a **4-field hierarchy structure** for relationships:

```
Field Name | Description                    | Purpose
-----------|--------------------------------|------------------------------------------
sid        | Super Admin ID                 | Links to the root Super Admin
mgid       | Master Group ID / Admin        | Links to the managing Admin (Formerly Group Admin)
gid        | Group ID / Parent Operator ID  | Links to the parent Operator
new_id     | Legacy Migration ID            | Used for data migration (default: 0)
```

**Hierarchy Flow Diagram**:
```
┌─────────────────┐
│  Super Admin    │ (sid = self.id)
│  (Level 1)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Admin (Formerly Group Admin)    │ (mgid = self.id, sid = super_admin.id)
│  (Level 2)      │
└────────┬────────┘
         │
         ├──────────────────┬──────────────────┐
         ▼                  ▼                  ▼
┌─────────────────┐  ┌──────────────┐  ┌─────────────┐
│  Operator       │  │   Manager    │  │   Others    │
│  (Level 3)      │  │  (Level 5)   │  │ (Levels 6-9)│
└────────┬────────┘  └──────────────┘  └─────────────┘
         │
         ▼
┌─────────────────┐
│  Sub-Operator   │ (gid = parent_operator.id, mgid = group_admin.id)
│  (Level 4)      │
└─────────────────┘
```

**Relationship Rules**:
1. **Operator**: `gid == mgid` (same parent and master) = Primary reseller
2. **Sub-Operator**: `gid != mgid` (different parent and master) = Secondary reseller
3. **Manager**: `gid = group_admin.id` (directly under Admin (Formerly Group Admin))
4. **Account Types**: 
   - `credit` (postpaid): Credit limit-based operations
   - `debit` (prepaid): Prepaid balance-based operations

---

### Permission System

The system implements a **two-tier permission model**:

#### A. Standard Permissions (Base Level)
Default permissions available to all operational roles (Managers, Operators, Sub-operators).

**Configuration File**: `/config/operators_permissions.php`

**Standard Permission List**:
```php
1.  Dashboard                    // Access to dashboard
2.  view-customer-details        // View customer information
3.  view-online-customers        // See currently online customers
4.  view-offline-customers       // See offline customers
5.  create-customer              // Register new customers
6.  edit-customer                // Modify customer details
7.  activate-customer            // Activate customer accounts
8.  suspend-customer             // Suspend customer services
9.  disable-customer             // Disable customer accounts
10. change-customer-package      // Change customer packages
11. receive-payment              // Process customer payments
12. print-invoice                // Generate and print invoices
13. generate-bill                // Create customer bills
14. send-sms                     // Send SMS to customers
15. expense-management           // Record and track expenses
16. view-customer-payments       // View payment history
```

#### B. Special Permissions (Enhanced Level)
Advanced permissions that must be explicitly granted by Admin.

**Configuration File**: `/config/special_permissions.php`

**Special Permission List**:
```php
1.  edit-package-price                // Modify package pricing
2.  edit-customer-payment             // Edit existing payment records
3.  delete-customer-payment           // Remove payment records
4.  edit-bills                        // Modify generated bills
5.  delete-bills                      // Remove bills
6.  discount-on-bills                 // Apply discounts to bills
7.  set-special-price-for-customer    // Customer-specific pricing
8.  reseller-module                   // Access reseller features
9.  delete-customer                   // Permanently delete customers
10. edit-customers-billing-profile    // Change customer billing profiles
```

#### Permission Assignment Rules

1. **Only Admin** can assign special permissions
2. **Only Operators** (role='operator') can receive special permissions
3. **Managers and Sub-operators** can also have special permissions if assigned
4. Permissions are stored individually in `operator_permissions` table
5. One row per permission per operator (many-to-many relationship)

---

### Panel Access by Role
## Governance & Roles (UPDTE)
- **Developer**: Supreme authority and source code owner, with unrestricted permissions.  
- **Super Admin**: Tenancy owner. Represents the overarching tenant context.  
- **Admin (Formerly Group Admin)**: Manages ISP-specific operations within a tenancy.  

---

Each role has access to a specific administrative panel with role-appropriate menus and features:

#### Super Admin Panel
- **Access**: All tenant features without restrictions
- **Main Sections**:
  - tenant-wide dashboard with all metrics
  - Admin management
  - Subscription management
  - tenant Global configuration
  - System logs and monitoring
  - All features from lower-level panels

#### Admin Panel
- **Access**: Full administrative panel for managing their ISP
- **Main Menu Sections**:
  1. **Dashboard** - Overview with widgets and charts
  2. **Resellers & Managers** - Operator, Sub-operator, Manager management
  3. **Routers & Packages** - Master packages, PPPoE profiles, NAS management
  4. **Recharge Cards** - Card generation, distributor management
  5. **Customers** - Full customer management (online, offline, import, zones)
  6. **Bills & Payments** - Billing, payment verification, due notifications
  7. **Incomes & Expenses** - Financial tracking and reporting
  8. **Complaints & Support** - Ticket management, categories
  9. **Reports** - BTRC, financial, customer reports
  10. **Affiliate Program** - Referral and commission tracking
  11. **VAT Management** - Tax profiles and collections
  12. **SMS Services** - Gateway configuration, broadcasting
  13. **Configuration** - Billing profiles, custom fields, devices
  14. **Activity Logs** - Audit trail and authentication logs

**Controllable Menus** (can be disabled per operator):
- Resellers & Managers menu
- Routers & Packages menu
- Recharge Card menu
- Customer menu
- Bills & Payments menu
- Incomes & Expenses menu
- Affiliate Program menu
- VAT menu

#### Operator Panel
- **Access**: Restricted panel based on menu configuration
- **Main Sections**:
  - Dashboard with operator metrics
  - Sub-operator management (if enabled)
  - Customer management (assigned customers only)
  - Bills and payments (own customers)
  - Recharge cards (if enabled)
  - Complaints (own customers)
  - Reports (limited to own data)
  - SMS (own customers)
- **Restrictions**:
  - Cannot create Admins or Operators
  - Cannot access other operators' data
  - Cannot modify system configurations
  - Menu visibility controlled by `disabled_menus` table

#### Sub-Operator Panel
- **Access**: Further restricted operator panel
- **Main Sections**:
  - Dashboard (limited metrics)
  - Customer management (assigned subset only)
  - Bills and payments (own customers)
  - Basic reports
- **Restrictions**:
  - Cannot create any operators
  - Cannot manage packages or profiles
  - Limited to assigned customers only
  - Most administrative features disabled

#### Manager Panel
- **Access**: Task-specific panel
- **Main Sections**:
  - Dashboard (group metrics)
  - Customer viewing (based on permissions)
  - Payment processing
  - Complaint management (assigned department)
  - Basic reports
- **Restrictions**:
  - Cannot modify operators or sub-operators
  - Cannot modify packages or configurations
  - Can view operators or sub-operators customers 
  - Limited to assigned permissions

#### Card Distributor Portal
- **Access**: Separate portal (not admin panel)
- **URL**: `/card-distributors/*` routes
- **Main Sections**:
  - Card inventory view
  - Sales tracking
  - Commission reports
  - Payment history
- **Restrictions**:
  - Read-only access
  - No customer management
  - No administrative features

#### Developer Panel
- **Access**: Source code owner and Technical configuration panel
- **Main Sections**:
  - Tenant Managment
  - Subscription management
  - Global configuration
  - System logs and monitoring
  - All features from lower-level panels
  - SMS gateway configuration
  - Payment gateway configuration
  - VPN pools
  - System logs
  - API management
- **Restrictions**:
  - Cannot manage customers or billing
  - Focus on technical infrastructure 

#### Accountant Panel
- **Access**: Financial reporting panel
- **Main Sections**:
  - Financial reports
  - Income/expense tracking
  - VAT collections
  - Payment history
  - Customer statements
- **Restrictions**:
  - Typically read-only access
  - Cannot modify customer data
  - Cannot process payments (view only)

---

### Feature Access Matrix

The following table shows which roles can access specific features:

| Feature Category | Super Admin | Admin (Formerly Group Admin) | Operator | Sub-Operator | Manager | Card Distributor | Sales Manager | Developer | Accountant |
|------------------|:-----------:|:-----------:|:--------:|:------------:|:-------:|:----------------:|:-------------:|:---------:|:----------:|
| **Operator Management** |
| Create Admin (Formerly Group Admin) | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create Operator | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create Sub-Operator | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create Manager | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Assign Special Permissions | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Suspend Operator | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Delete Operator | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Customer Management** |
| Create Customer | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✓² | ✗ | ✗ |
| Edit Customer | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| Activate Customer | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| Suspend Customer | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| Delete Customer | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| View Customer Details | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✓ | ✗ | ✓ |
| Change Package | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| **Billing & Payments** |
| Generate Bills | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| Edit Bills | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| Delete Bills | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| Receive Payments | ✓ | ✓ | ✓ | ✓ | ✓¹ | ✗ | ✗ | ✗ | ✗ |
| Edit Payments | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| Delete Payments | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| Apply Discounts | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| **Packages & Profiles** |
| Create Master Package | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create Package | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Edit Package Price | ✓ | ✓ | ✓³ | ✓³ | ✓¹³ | ✗ | ✗ | ✗ | ✗ |
| Create PPPoE Profile | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create Billing Profile | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Assign Package to Operator | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Network Configuration** |
| Create IP Pool (IPv4/IPv6) | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Configure NAS | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage Devices | ✓ | ✓ | ✓⁴ | ✓⁴ | ✓⁴ | ✗ | ✗ | ✗ | ✗ |
| Access Router Config | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Recharge Cards** |
| Generate Cards | ✓ | ✓ | ✓⁴ | ✓⁴ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage Distributors | ✓ | ✓ | ✓⁴ | ✓⁴ | ✗ | ✗ | ✗ | ✗ | ✗ |
| View Card Inventory | ✓ | ✓ | ✓⁴ | ✓⁴ | ✗ | ✓ | ✗ | ✗ | ✗ |
| Process Card Payments | ✓ | ✓ | ✓⁴ | ✓⁴ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Complaints & Support** |
| Create Complaint Categories | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage Complaints | ✓ | ✓ | ✓ | ✓ | ✓⁵ | ✗ | ✗ | ✗ | ✗ |
| Assign Department | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| View Complaint Reports | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ |
| **Financial Management** |
| Record Expenses | ✓ | ✓ | ✓⁴ | ✓⁴ | ✓¹ | ✗ | ✗ | ✗ | ✓ |
| View Income Reports | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ |
| Manage VAT | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ |
| View Financial Reports | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ |
| Cash In/Out Entry | ✓ | ✓⁶ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ |
| **Technical Configuration** |
| Configure SMS Gateway | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| Configure Payment Gateway | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| Manage VPN Pools | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| View System Logs | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| **Menu Control** |
| Disable Menus for Operators | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| View Operator Panel | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| **Subscription Management** |
| Suspend Subscription | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage Subscription Billing | ✓ | ✓⁷ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

**Legend**:
- ✓ = Full Access
- ✗ = No Access
- ¹ = Requires specific standard permission
- ² = Limited to lead management
- ³ = Requires special permission
- ⁴ = If menu not disabled by Admin (Formerly Group Admin)
- ⁵ = Limited to assigned department
- ⁶ = Only for own account
- ⁷ = For own subscription only

---

### Menu and Page Access Control

The system provides **dynamic menu visibility control** allowing Admin (Formerly Group Admin)s to customize the operator experience.

#### Disabled Menu System

**Purpose**: Allow Admin (Formerly Group Admin) to hide specific menu sections from Operators and Sub-operators

**Database Table**: `disabled_menus`
```sql
disabled_menus
├── id              (Primary Key)
├── operator_id     (Foreign Key → operators.id)
├── menu            (String - menu identifier)
└── timestamps
```

#### Available Menu Sections for Control

| Menu Identifier | Menu Name | Description |
|-----------------|-----------|-------------|
| `resellers_and_managers_group_admin` | Resellers & Managers | Operator, Sub-operator, Manager management |
| `routers_packages_menu` | Routers & Packages | Master packages, PPPoE profiles, NAS, IP pools |
| `recharge_card_menu_group_admin` | Recharge Cards | Card generation and distributor management |
| `customer_menu` | Customers | Customer management and related features |
| `bills_and_payments_menu` | Bills & Payments | Billing and payment processing |
| `incomes_expenses_menu_group_admin` | Incomes & Expenses | Financial tracking |
| `affiliate_program_menu_group_admin` | Affiliate Program | Referral and commission system |
| `vat_menu_group_admin` | VAT Management | Tax configuration and collection |

#### Menu Checking Logic

**Helper Function** (in `/app/Helpers/Helper.php`):
```php
function isMenuActive(string $menu, operator $operator): bool
{
    $disabled_menus = CacheController::getDisabledMenus($operator);
    $disabled_count = $disabled_menus->where('menu', $menu)->count();
    return $disabled_count == 0;
}
```

**Usage in Views**:
```blade
@if(isMenuActive('recharge_card_menu_group_admin', auth()->user()))
    <!-- Show Recharge Card Menu -->
@endif
```

**Caching**: 
- Cache Key: `app_models_disabled_menus_{operator_id}`
- Improves performance by reducing database queries
- Cache cleared when menu configuration changes

#### Page-Level Access Control

Beyond menu visibility, the system enforces **page-level authorization** using Laravel Policies:

**Policy-Based Checks**:
```php
// In controllers
$this->authorize('view', $operator);
$this->authorize('update', $customer);
$this->authorize('assignSpecialPermission', $operator);
```

**Middleware Checks**:
- `auth:operator` - Ensures authenticated operator
- `verified` - Ensures email verification
- `2fa.verify` - Enforces two-factor authentication (if enabled)
- `AccessControlList` - IP-based access restrictions

---

### Relations and Restrictions
## NOTE Governance & Roles (UPDTE)
- **Developer**: Supreme authority and source code owner, with unrestricted permissions.  
- **Tenancy (formerly Super Admin)**: Represents the overarching tenant context.  
- **Admin (Formerly Group Admin)**: Manages ISP-specific operations within a tenancy.  

---
#### Hierarchical Relationships

**1. Super Admin → Admin (Formerly Group Admin)**
- **Relation**: One Super Admin can manage multiple Admin (Formerly Group Admin)s
- **Field**: `operators.sid` links to Super Admin
- **Restrictions**:
  - Super Admin can suspend Admin (Formerly Group Admin) subscriptions
  - Admin (Formerly Group Admin) cannot modify Super Admin settings
  - Billing flows from Admin (Formerly Group Admin) to Super Admin

**2. Admin (Formerly Group Admin) → Operators**
- **Relation**: One Admin (Formerly Group Admin) can manage multiple Operators
- **Field**: `operators.mgid` links to Admin (Formerly Group Admin)
- **Restrictions**:
  - Admin (Formerly Group Admin) assigns packages and billing profiles
  - Operators cannot access other operators' data
  - Admin (Formerly Group Admin) can view all operator customer data
  - Admin (Formerly Group Admin) controls operator menu visibility

**3. Operator → Sub-Operators**
- **Relation**: One Operator can manage multiple Sub-operators
- **Field**: `operators.gid` links to parent Operator (where gid != mgid)
- **Restrictions**:
  - Sub-operators inherit package and profile limitations
  - Sub-operators cannot create additional sub-operators
  - Operator can view all sub-operator customer data
  - Sub-operators have more restricted panel access

**4. Admin (Formerly Group Admin) → Managers**
- **Relation**: One Admin (Formerly Group Admin) can create multiple Managers
- **Field**: `operators.gid` links to Admin (Formerly Group Admin)
- **Restrictions**:
  - Managers work within Admin (Formerly Group Admin) scope
  - Managers have permission-based feature access
  - Cannot manage operators or configurations
  - Department-based complaint assignment

**5. Operator/Admin (Formerly Group Admin) → Card Distributors**
- **Relation**: Many-to-one relationship via `card_distributors` table
- **Field**: `card_distributors.operator_id`
- **Restrictions**:
  - Distributors have read-only access
  - Cannot manage customers or billing
  - Commission-based payment system
  - Separate portal (not admin panel)

#### Data Isolation Rules

**1. Customer Data**
- Each operator can only access their own customers
- Admin (Formerly Group Admin) can access all customers in their group
- Super Admin has global access
- Enforced at query level using operator_id filters

**2. Financial Data**
- Operators see only their own financial data
- Admin (Formerly Group Admin) sees aggregated group financial data
- Account balances tracked per operator (prepaid/postpaid)
- Credit limits enforced for postpaid accounts

**3. Package Assignment**
- Admin (Formerly Group Admin) controls which packages operators can use
- Operators can only assign their assigned packages to customers
- Package pricing can be operator-specific (with special permission)
- Master packages managed only by Admin (Formerly Group Admin)

**4. Billing Profile Assignment**
- Controlled via `billing_profile_operator` pivot table
- Operators can only use assigned billing profiles
- Admin (Formerly Group Admin) can change assignments
- Affects billing calculation and invoice generation

#### Account Type Restrictions

**Credit/Postpaid Accounts** (`account_type = 'credit'`):
- **Feature**: Credit limit tracking
- **Restriction**: Cannot exceed credit limit
- **Applies To**: Operators, Sub-operators
- **Balance**: Tracked as accounts payable
- **Policy Check**: `editLimit()` policy requires Admin (Formerly Group Admin)

**Debit/Prepaid Accounts** (`account_type = 'debit'`):
- **Feature**: Prepaid balance management
- **Restriction**: Must maintain positive balance
- **Applies To**: Operators, Sub-operators
- **Balance**: Tracked as account balance
- **Policy Check**: `addBalance()` policy requires Admin (Formerly Group Admin)

#### Subscription Restrictions

**Subscription Types**:
- `Paid`: Full access to all assigned features
- `Free`: Limited trial or demo access

**Subscription Status**:
- `active`: Normal operation
- `suspended`: Access restricted until payment

**Enforcement**:
- Checked in all policies before granting access
- Super Admin can suspend subscriptions
- Admin (Formerly Group Admin) manages own subscription status
- Suspended accounts cannot perform any operations

#### Special Permission Restrictions

**Assignment Rules**:
1. **Only Admin (Formerly Group Admin)** can assign special permissions
2. **Only to role='operator'** (Operators with gid=mgid)
3. Cannot assign to:
   - Other Admin (Formerly Group Admin)s
   - Managers (but can grant if they have operator role)
   - Card Distributors
   - Sales Managers
4. Permission assignment requires `assignSpecialPermission` policy check

**Permission Scope**:
- Special permissions apply only to assigned operator
- Do not cascade to sub-operators automatically
- Admin (Formerly Group Admin) can grant same permission to multiple operators
- Revocation removes permission immediately

#### Menu Visibility Restrictions

**Control Rules**:
1. Only Admin (Formerly Group Admin) can configure disabled menus
2. Cannot disable menus for:
   - Super Admin
   - Other Admin (Formerly Group Admin)s
   - Self (demo mode protection)
3. Menu changes cached per operator
4. Applies to Operators and Sub-operators only

**Protected Menus**:
- Dashboard (always visible)
- Basic customer management (core functionality)
- Logout and profile settings

#### IP-Based Access Control

**Access Control List (ACL) Middleware**:
- **Purpose**: Restrict admin panel access by IP address
- **Configuration**: CIDR-based IP ranges
- **Enforcement**: Middleware checks incoming request IP
- **Restriction**: Blocks access if IP doesn't match configured ranges
- **Bypass**: Can be disabled in configuration

---

### Authorization Policy Details

The system uses **Laravel Policy classes** for fine-grained authorization:

#### OperatorPolicy

**Key Authorization Methods**:

| Method | Purpose | Key Logic |
|--------|---------|-----------|
| `view()` | View operator details | User is self, gid, mgid, or sid |
| `update()` | Edit operator info | User is gid (Admin (Formerly Group Admin)) |
| `delete()` | Delete operator | User is gid (Admin (Formerly Group Admin)) |
| `editLimit()` | Modify credit limit | User is gid with credit account |
| `addBalance()` | Add prepaid balance | User is gid with debit account |
| `assignPackages()` | Assign packages | User is gid, target is operator/sub-operator |
| `assignProfiles()` | Assign billing profiles | User is gid, target is operator/sub-operator |
| `assignSpecialPermission()` | Grant special perms | User is gid, target is operator only |
| `getAccess()` | Access operator panel | User is group_admin or developer |
| `suspend()` | Suspend operator | User is gid (Admin (Formerly Group Admin)) |
| `suspendSubscription()` | Suspend subscription | User is sid (Super Admin) |
| `entryCashReceived()` | Cash entry | Account provider matches user |

#### CustomerPolicy

**Key Authorization Methods**:
- `view()` - Can view customer details
- `update()` - Can edit customer information
- `delete()` - Can delete customer (requires special permission)
- `activate()` - Can activate customer account
- `suspend()` - Can suspend customer service
- `changePackage()` - Can change customer package
- `receivePay()` - Can process customer payment

**Authorization Flow**:
```
Request → Controller → Policy → Database Check → Response
                           ↓
                    Check Hierarchy
                    Check Permission
                    Check Status
                    Check Ownership
```

---

### Best Practices and Recommendations

#### For Admin (Formerly Group Admin)s
1. **Permission Assignment**: Only grant special permissions when absolutely necessary
2. **Menu Control**: Disable unused menus to simplify operator interface
3. **Operator Monitoring**: Regularly review operator access logs
4. **Credit Limits**: Set appropriate credit limits for postpaid operators
5. **Package Assignment**: Carefully control which packages operators can sell

#### For Operators
1. **Sub-Operator Creation**: Create sub-operators for regional management
2. **Permission Requests**: Request special permissions from Admin (Formerly Group Admin) when needed
3. **Customer Organization**: Use zones and custom fields for better organization
4. **Billing Profiles**: Use appropriate billing profiles for different customer types
5. **Report Generation**: Regularly generate reports for your customer base

#### Security Considerations
1. **Two-Factor Authentication**: Enable 2FA for sensitive roles
2. **IP Restrictions**: Configure ACL for production environments
3. **Subscription Monitoring**: Keep subscriptions active to maintain access
4. **Permission Auditing**: Review granted special permissions regularly
5. **Activity Logging**: Monitor activity logs for suspicious behavior
6. **Password Policy**: Enforce strong passwords for all accounts
7. **Session Management**: Configure appropriate session timeouts

---

### Technical Implementation Files

**Key Files Reference**:

| File | Purpose | Lines |
|------|---------|-------|
| `/database/migrations/mysql/2021_08_27_113525_create_operators_table.php` | Role definitions and operator structure | 36 |
| `/database/migrations/mysql/2021_08_27_113525_create_operator_permissions_table.php` | Permission storage schema | 14-21 |
| `/database/migrations/updates/2022_12_26_230202_create_disabled_menus_table.php` | Menu visibility control | - |
| `/config/operators_permissions.php` | Standard permission list | 3-20 |
| `/config/special_permissions.php` | Special permission list | 3-14 |
| `/config/sidebars.php` | Menu configuration for roles | - |
| `/app/Models/operator.php` | Operator model with relationships | 710 lines |
| `/app/Models/operator_permission.php` | Permission model | 35 lines |
| `/app/Models/disabled_menu.php` | Menu visibility model | - |
| `/app/Policies/OperatorPolicy.php` | Authorization gates | - |
| `/app/Http/Controllers/OperatorsSpecialPermissionController.php` | Permission assignment logic | - |
| `/app/Http/Controllers/DisabledMenuController.php` | Menu management | - |
| `/app/Http/Middleware/AccessControlList.php` | IP-based access control | - |
| `/app/Helpers/Helper.php` | Menu checking helper function | - |
| `/resources/views/admins/group_admin/sidebar.blade.php` | Admin (Formerly Group Admin) menu structure | - |
| `/resources/views/admins/operator/sidebar.blade.php` | Operator menu structure | - |

---

### Summary Statistics

**Role Summary**:
- **9 distinct roles** with hierarchical authority
- **4-tier hierarchy** (Super Admin → Admin (Formerly Group Admin) → Operator → Sub-operator)
- **16 standard permissions** for base functionality
- **10 special permissions** for enhanced capabilities
- **8 controllable menu sections** for customization
- **Policy-based authorization** for fine-grained control
- **IP-based access control** for security
- **Two-factor authentication** support for all roles
- **Dynamic menu visibility** per operator
- **Credit/debit account types** for flexible billing models

**Access Control Features**:
- Hierarchical data isolation
- Permission-based feature access
- Policy-based page authorization
- Menu-level visibility control
- IP address restrictions
- Session management
- Activity logging and auditing
- Subscription-based access control

This comprehensive role and permission system ensures **secure, scalable, and flexible** access control for managing ISP operations across multiple organizational levels while maintaining proper data isolation and authorization boundaries.

---

## Summary

This ISP Billing System is a comprehensive solution with **400+ distinct features** organized across multiple functional areas:

- **Customer Management**: 50+ features
- **Billing & Payments**: 40+ features  
- **Network Management**: 35+ features
- **Operator & Reseller**: 25+ features
- **Communication (SMS/Email)**: 20+ features
- **Reports & Analytics**: 30+ features
- **Security & Access Control**: 15+ features
- **Payment Gateways**: 10+ features
- **Support & Complaints**: 12+ features
- **Card & Recharge System**: 15+ features
- **System Configuration**: 30+ features
- **Integration & APIs**: 25+ features
- **Dashboard & Widgets**: 15+ features
- **Technical Infrastructure**: 40+ features

The system supports multiple user roles (Super Admin, Admin (Formerly Group Admin), Operator, Sub-operator, Manager, Card Distributor, Sales Manager, Developer, Accountant) with granular permissions, multi-node distributed architecture, and extensive third-party integrations for a complete ISP business management solution.


@copilot follow this file and develop feature by taking knowledge from this file
