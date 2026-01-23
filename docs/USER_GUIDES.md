# ISP Solution - User Guides

Complete user documentation for all user roles in the ISP Solution system.

---

## Table of Contents

1. [Super Admin Guide](#1-super-admin-guide)
2. [Admin Guide](#2-admin-guide)
3. [Operator Guide](#3-operator-guide)
4. [Sub-Operator Guide](#4-sub-operator-guide)
5. [Manager Guide](#5-manager-guide)
6. [Staff Guide](#6-staff-guide)
7. [Customer Guide](#7-customer-guide)
8. [Developer Guide](#8-developer-guide)
9. [Accountant Guide](#9-accountant-guide)

---

## 1. Super Admin Guide

### Overview
Super Admins manage their own tenancies and can create and manage Admins (ISP Owners) within those tenancies.

### Key Responsibilities
- Manage Admins (ISPs) within their own tenancies
- Configure tenant-wide settings
- Monitor tenant health
- Manage subscription billing for Admins
- Configure payment and SMS gateways for Admins
- Access features within own tenancies only

### Access Level
- **Scope**: Own tenancies only
- **URL**: `/panel/super-admin/*`

### Common Tasks

#### 1.1 Create New Admin (ISP)
1. Navigate to **Panel → Super Admin → Admin Management**
2. Click **"Add Admin"**
3. Fill in Admin details:
   - Company name
   - Contact email
   - Admin username and password
   - Billing plan (fixed, user-based, or panel-based)
4. Click **"Create Admin"**

#### 1.2 Manage Subscriptions
1. Go to **Panel → Super Admin → Subscription Management**
2. Select subscription type:
   - Fixed billing plans
   - User-based billing
   - Panel-based billing
3. Configure billing parameters
4. Track subscription renewals

#### 1.3 Configure Tenant Settings
1. Navigate to **Panel → Super Admin → Tenant Configuration**
2. Configure:
   - Tenant-wide settings
   - Payment gateways (for own tenancy)
   - SMS gateways (for own tenancy)
3. Set tenant defaults and limits

#### 1.4 Monitor System
1. Go to **Panel → Super Admin → Monitoring**
2. View system health metrics
3. Check performance indicators
4. Review error rates

#### 1.5 View System Logs
1. Navigate to **Panel → Super Admin → System Logs**
2. Filter by:
   - Date range
   - Log level (info, warning, error)
   - Module
3. Review logs and take action

---

## 2. Admin Guide

### Overview
Admins (also known as ISP Owners) manage a single ISP and have full control over their organization's operations within their tenant.

### Key Responsibilities
- Manage users and customers
- Configure packages and pricing
- Monitor network devices
- Handle billing and payments
- Manage operators and staff

### Common Tasks

#### 2.1 Add New Customer
1. Navigate to **Panel → Admin → Customers**
2. Click **"Add Customer"**
3. Fill in customer details:
   - Name, email, phone
   - Package selection
   - Connection type (PPPoE, Hotspot, Static IP)
   - Billing start date
4. Click **"Create Customer"**
5. Credentials will be auto-generated and can be sent via email/SMS

Note: Admins, Operators, and Sub-Operators can all create customers.

#### 2.2 Create Service Package
1. Go to **Panel → Admin → Packages**
2. Click **"Create Package"**
3. Enter package details:
   - Package name
   - Bandwidth (upload/download speeds)
   - Price
   - Billing type (daily, monthly)
   - Validity days (for daily packages)
4. Click **"Save Package"**

#### 2.3 Process Manual Payment
1. Navigate to **Panel → Admin → Customers**
2. Find the customer
3. Click **"Record Payment"**
4. Select payment method (cash, bank transfer, check)
5. Enter amount and transaction ID (optional)
6. Click **"Record Payment"**
7. Invoice status will update automatically

#### 2.4 Add MikroTik Router
1. Go to **Panel → Admin → Network → MikroTik**
2. Click **"Add Router"**
3. Enter router details:
   - Name/identifier
   - IP address
   - API port (usually 8728)
   - Username and password
4. Click **"Test Connection"**
5. If successful, click **"Save"**

#### 2.5 Generate Reports
1. Navigate to **Panel → Admin → Accounting → Reports**
2. Select report type:
   - Income/Expense Report
   - Payment Gateway Transactions
   - Customer Payments
   - VAT Collections
3. Select date range
4. Click **"Generate Report"**
5. Export to PDF or Excel

---

## 3. Operator Guide

### Overview
Operators manage day-to-day operations with a restricted panel based on menu configuration set by the Admin. They can manage assigned customers and perform operational tasks.

### Access Level
- **Scope**: Assigned customers and features only
- **URL**: `/panel/operator/*`
- **Menu Control**: Admin configures which menus are visible

### Key Responsibilities
- Manage assigned customers
- Process customer payments
- Handle customer complaints
- Create sub-operators (if enabled)
- Send SMS to customers
- Generate reports for own data

### Common Tasks

#### 3.1 View Dashboard
1. Login to **Operator Panel**
2. View operator-specific metrics:
   - Assigned customer count
   - Payment collection summary
   - Performance indicators

#### 3.2 Manage Customers
1. Navigate to **Panel → Operator → Customers**
2. View only assigned customers
3. Add new customers (if menu enabled)
4. Update customer information
5. View connection status

#### 3.3 Process Payment
1. Go to **Panel → Operator → Bills & Payments**
2. Search for customer
3. Select pending invoice
4. Click **"Process Payment"**
5. Enter payment details
6. Confirm transaction

#### 3.4 Handle Complaints
1. Navigate to **Panel → Operator → Complaints**
2. View tickets for own customers
3. Respond to customer issues
4. Update ticket status
5. Escalate if needed

#### 3.5 Send SMS
1. Go to **Panel → Operator → SMS**
2. Select customers (own customers only)
3. Compose message
4. Send or schedule

#### 3.6 View Reports
1. Navigate to **Panel → Operator → Reports**
2. Generate reports limited to own data
3. Export for records

### Restrictions
- Cannot create Admins or Operators
- Cannot access other operators' data
- Cannot modify system configurations
- Menu visibility controlled by Admin

---

## 4. Sub-Operator Guide

### Overview
Sub-Operators work under Operators with further restricted access to a subset of customers.

### Access Level
- **Scope**: Assigned customer subset only
- **URL**: `/panel/sub-operator/*`
- **Reports to**: Parent Operator

### Key Responsibilities
- Manage assigned customer subset
- Process payments for assigned customers
- Basic reporting

### Common Tasks

#### 4.1 View Dashboard
1. Login to **Sub-Operator Panel**
2. View limited metrics for assigned customers

#### 4.2 Manage Assigned Customers
1. Navigate to **Panel → Sub-Operator → Customers**
2. View only assigned customers (subset)
3. Update basic customer information
4. Monitor connection status

#### 4.3 Process Payments
1. Go to **Panel → Sub-Operator → Bills & Payments**
2. Select customer
3. Process payment
4. View payment history

#### 4.4 Generate Basic Reports
1. Navigate to **Panel → Sub-Operator → Reports**
2. View collection reports
3. Check customer activity
4. Review own performance metrics

### Restrictions
- Cannot create any operators
- Cannot manage packages or profiles
- Limited to assigned customers only
- Most administrative features disabled
- Cannot see other sub-operators' data

---

## 5. Manager Guide

### Overview
Managers have task-specific access with permission-based features, focusing on payment processing and complaint management.

### Access Level
- **Scope**: Group metrics and assigned permissions
- **URL**: `/panel/manager/*`

### Key Responsibilities
- View customers based on permissions (can view operators' or sub-operators' customers)
- Process payments (if authorized)
- Manage assigned department complaints
- Generate basic reports

### Common Tasks

#### 5.1 View Customers
1. Navigate to **Panel → Manager → Customer Viewing**
2. View customers based on permissions
3. Search and filter customers
4. View customer details (read-only typically)

#### 5.2 Process Payments
1. Go to **Panel → Manager → Payment Processing**
2. Find customer
3. Record payment (if authorized)
4. Verify payment details
5. View payment history

#### 5.3 Manage Complaints
1. Navigate to **Panel → Manager → Complaint Management**
2. View assigned department tickets
3. Assign tickets to team members
4. Respond to complaints
5. Update status and escalate if needed

#### 5.4 Generate Reports
1. Go to **Panel → Manager → Basic Reports**
2. View department-level reports
3. Check performance metrics
4. Export reports

### Restrictions
- Cannot modify operators or sub-operators
- Cannot modify packages or configurations
- Can view operators or sub-operators customers (read-only)
- Limited to assigned permissions

---

## 6. Staff Guide

#### 3.1 Monitor Active Sessions
1. Navigate to **Panel → Manager → Active Sessions**
2. View online users with details:
   - Username
   - IP address
   - Session duration
   - Bandwidth usage
3. Disconnect user if needed (click "Disconnect")

#### 3.2 Check Network User Status
1. Go to **Panel → Manager → Network Users**
2. Search for specific user
3. View connection details:
   - Current status (active, suspended, expired)
   - Last login time
   - Package details
4. Take action (suspend, reactivate, change package)

#### 3.3 Generate Operational Report
1. Navigate to **Panel → Manager → Reports**
2. Select report period
3. Review:
   - Total active users
   - New connections
   - Disconnections
   - Revenue summary
4. Export report

---

## 6. Staff Guide

### Overview
Staff members provide frontline customer support and handle basic account management.

### Key Responsibilities
- Answer customer queries
- Process support tickets
- Assist with basic account issues
- Help customers with connectivity problems

### Common Tasks

#### 6.1 Handle Support Ticket
1. Navigate to **Panel → Staff → Support Tickets**
2. Click on unassigned ticket
3. Assign to yourself
4. Read customer issue
5. Provide solution or escalate if needed
6. Update ticket status
7. Close ticket when resolved

#### 6.2 Help Customer Reset Password
1. Go to **Panel → Staff → Network Users**
2. Search for customer by username
3. Click **"Reset Password"**
4. New password generated automatically
5. Share with customer via secure channel

#### 6.3 Check Customer Connection Status
1. Search for customer in Network Users
2. View connection status
3. Check for:
   - Unpaid invoices
   - Suspended account
   - Expired package
4. Guide customer to resolve issue

---

## 7. Customer Guide

### Overview
Customers access their account to view usage, pay bills, and manage their service.

### Key Responsibilities
- Pay invoices
- Monitor usage
- Update profile
- Submit support tickets

### Common Tasks

#### 7.1 View and Pay Invoice
1. Login to **Customer Panel**
2. Navigate to **Billing**
3. View pending invoices
4. Click **"Pay Now"** on invoice
5. Select payment method:
   - Online (bKash, Nagad, Card)
   - Offline (Bank transfer, Card distributor)
6. Complete payment
7. Account activated automatically after payment

#### 7.2 Check Usage Statistics
1. Go to **Panel → Customer → Usage**
2. View:
   - Data uploaded/downloaded
   - Session history
   - Current bandwidth
3. Review monthly trends

#### 7.3 Submit Support Ticket
1. Navigate to **Panel → Customer → Support**
2. Click **"New Ticket"**
3. Select issue category
4. Describe problem
5. Attach screenshots if needed
6. Submit ticket
7. Track ticket status and responses

#### 7.4 Update Profile
1. Go to **Panel → Customer → Profile**
2. Update:
   - Contact information
   - Email address
   - Phone number
3. Change password if needed
4. Save changes

---

## 8. Developer Guide

### Overview
Developers integrate with the system via API and manage system customizations.

### Key Responsibilities
- API integration
- System debugging
- Custom feature development
- API documentation review

### Common Tasks

#### 8.1 Generate API Token
1. Login to **Developer Panel**
2. Navigate to **API Settings**
3. Click **"Generate New Token"**
4. Name the token
5. Select scopes/permissions
6. Copy token (shown only once)
7. Use in API requests

#### 8.2 View API Documentation
1. Go to **Panel → Developer → API Docs**
2. Browse endpoint documentation
3. Test endpoints using built-in API tester
4. Copy sample code

#### 8.3 Check System Logs
1. Navigate to **Panel → Developer → Logs**
2. Filter by:
   - Log level
   - Module
   - Date range
3. Debug issues
4. Export logs for analysis

#### 8.4 Run Debug Tools
1. Go to **Panel → Developer → Debug**
2. Available tools:
   - Database query monitor
   - Cache manager
   - Queue monitor
   - Schedule tester
3. Use tools to diagnose issues

---

## 9. Accountant Guide

### Overview
Accountants have read-only access to financial data and can generate comprehensive financial reports for compliance and analysis.

### Access Level
- **Scope**: Financial data and reports (read-only)
- **URL**: `/panel/accountant/*`

### Key Responsibilities
- Generate financial reports
- Track income/expense
- Monitor VAT collections
- Review payment history
- Prepare customer statements

### Common Tasks

#### 9.1 View Dashboard
1. Login to **Accountant Panel**
2. View financial overview:
   - Revenue metrics
   - Outstanding balances
   - Collection summary

#### 9.2 Generate Financial Reports
1. Navigate to **Panel → Accountant → Financial Reports**
2. Select report type:
   - Income/Expense Report
   - Payment History Report
   - Customer Statement Report
   - Revenue Analysis
3. Select date range
4. Click **"Generate Report"**
5. Export to PDF or Excel

#### 9.3 Track Income/Expense
1. Go to **Panel → Accountant → Income/Expense Tracking**
2. View transaction history (read-only)
3. Review expense records
4. Analyze category-wise breakdown
5. Compare periods

#### 9.4 Review VAT Collections
1. Navigate to **Panel → Accountant → VAT Collections**
2. View VAT collected summary
3. Generate tax reports
4. Download compliance documentation
5. Track period-wise VAT

#### 9.5 View Payment History
1. Go to **Panel → Accountant → Payment History**
2. View all payment records (read-only)
3. Filter by:
   - Date range
   - Payment method
   - Customer
4. Export payment history

#### 9.6 Generate Customer Statements
1. Navigate to **Panel → Accountant → Customer Statements**
2. Select customer
3. Choose date range
4. Generate statement
5. Export or email to customer

### Restrictions
- Typically read-only access (view and export only)
- Cannot modify customer data
- Cannot process payments (view-only payment data)
- No system configuration access
- Focus on financial viewing and reporting

### Key Features
- Comprehensive financial reporting
- Export capabilities (PDF, Excel)
- Period-wise analysis
- Compliance reporting
- Audit trail viewing

---

## Common Features Across All Roles

### Notifications
- Email notifications for important events
- SMS notifications (if enabled)
- In-app notification center

### Search and Filters
- Global search bar in navigation
- Advanced filters on list pages
- Export to Excel/PDF

### Dark Mode
- Toggle dark mode in user menu
- Setting persists across sessions

### Multi-language Support
- Change language in user preferences
- Currently supported: English, Bengali

---

## Getting Help

### Support Channels
- **Help Center:** Built-in help documentation
- **Support Tickets:** Submit ticket from any panel
- **Email:** support@ispsolution.com
- **Phone:** See contact page for support hours

### Training Resources
- Video tutorials (coming soon)
- Knowledge base articles
- FAQ section

---

## Best Practices

### Security
- Use strong passwords (minimum 8 characters)
- Enable 2FA if available
- Don't share account credentials
- Logout after session
- Review login activity regularly

### Data Management
- Regular backups of customer data
- Export reports periodically
- Archive old records
- Keep payment records for minimum 3 years

### Customer Service
- Respond to tickets within 24 hours
- Keep customers informed about issues
- Send pre-expiration reminders
- Follow up on resolved issues

---

**Document Version:** 2.0  
**Last Updated:** 2026-01-18  
**Next Review:** 2026-04-18
