# ISP Solution - User Guides

Complete user documentation for all 9 user roles in the ISP Solution system.

---

## Table of Contents

1. [Super Admin Guide](#1-super-admin-guide)
2. [Admin Guide](#2-admin-guide)
3. [Manager Guide](#3-manager-guide)
4. [Staff Guide](#4-staff-guide)
5. [Reseller Guide](#5-reseller-guide)
6. [Sub-Reseller Guide](#6-sub-reseller-guide)
7. [Card Distributor Guide](#7-card-distributor-guide)
8. [Customer Guide](#8-customer-guide)
9. [Developer Guide](#9-developer-guide)

---

## 1. Super Admin Guide

### Overview
Super Admins have system-wide access and can manage all ISPs, tenants, and system configuration.

### Key Responsibilities
- Manage ISP accounts (tenants)
- Configure system-wide settings
- Monitor overall system health
- Manage billing configurations
- Configure payment and SMS gateways

### Common Tasks

#### 1.1 Create New ISP/Tenant
1. Navigate to **Panel → Super Admin → ISP Management**
2. Click **"Add New ISP"**
3. Fill in ISP details:
   - Company name
   - Contact email
   - Admin username and password
   - Billing plan (fixed, user-based, or panel-based)
4. Click **"Create ISP"**

#### 1.2 Configure Payment Gateway
1. Go to **Panel → Super Admin → Payment Gateway**
2. Click **"Add Gateway"**
3. Select gateway type:
   - bKash (Bangladeshi)
   - Nagad (Bangladeshi)
   - SSLCommerz (Bangladeshi)
   - Stripe (International)
4. Enter API credentials
5. Enable test mode for testing
6. Click **"Save"**

#### 1.3 Configure SMS Gateway
1. Navigate to **Panel → Super Admin → SMS Gateway**
2. Select SMS provider (Twilio, Nexmo, BulkSMS, Local)
3. Enter API credentials
4. Test SMS sending
5. Enable for production use

#### 1.4 View System Logs
1. Go to **Panel → Super Admin → Logs**
2. Filter by:
   - Date range
   - Log level (info, warning, error)
   - Module
3. Review error logs and take action

---

## 2. Admin Guide

### Overview
Admins manage a single ISP/tenant and have full control over their organization's operations.

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

## 3. Manager Guide

### Overview
Managers oversee daily operations, monitor network users, and handle customer support issues.

### Key Responsibilities
- Monitor active network sessions
- Review customer connections
- Generate operational reports
- Handle escalated support issues

### Common Tasks

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

## 4. Staff Guide

### Overview
Staff members provide frontline customer support and handle basic account management.

### Key Responsibilities
- Answer customer queries
- Process support tickets
- Assist with basic account issues
- Help customers with connectivity problems

### Common Tasks

#### 4.1 Handle Support Ticket
1. Navigate to **Panel → Staff → Support Tickets**
2. Click on unassigned ticket
3. Assign to yourself
4. Read customer issue
5. Provide solution or escalate if needed
6. Update ticket status
7. Close ticket when resolved

#### 4.2 Help Customer Reset Password
1. Go to **Panel → Staff → Network Users**
2. Search for customer by username
3. Click **"Reset Password"**
4. New password generated automatically
5. Share with customer via secure channel

#### 4.3 Check Customer Connection Status
1. Search for customer in Network Users
2. View connection status
3. Check for:
   - Unpaid invoices
   - Suspended account
   - Expired package
4. Guide customer to resolve issue

---

## 5. Reseller Guide

### Overview
Resellers sell services to customers and earn commissions on sales and renewals.

### Key Responsibilities
- Acquire new customers
- Manage customer accounts
- Track commission earnings
- Renew customer packages

### Common Tasks

#### 5.1 Add New Customer
1. Navigate to **Panel → Reseller → Customers**
2. Click **"Add Customer"**
3. Fill in customer details
4. Select package
5. Click **"Create"**
6. Commission will be calculated automatically on first payment

#### 5.2 View Commission Earnings
1. Go to **Panel → Reseller → Commission**
2. View commission summary:
   - Total earned
   - Pending commissions
   - Paid commissions
3. Filter by date range
4. Request payment for pending commissions

#### 5.3 Renew Customer Package
1. Navigate to **Panel → Reseller → Customers**
2. Find customer with expiring package
3. Click **"Renew"**
4. Confirm package and duration
5. Generate invoice
6. Collect payment from customer

---

## 6. Sub-Reseller Guide

### Overview
Sub-Resellers work under Resellers and earn commissions on their sales.

### Key Responsibilities
- Similar to Reseller but reports to parent Reseller
- Earn sub-reseller commission rate (typically lower)

### Common Tasks
(Same as Reseller Guide but with different commission structure)

---

## 7. Card Distributor Guide

### Overview
Card Distributors manage prepaid recharge cards for services.

### Key Responsibilities
- Generate recharge cards
- Track card inventory
- Monitor sales
- Manage distributor balance

### Common Tasks

#### 7.1 Generate Recharge Cards
1. Navigate to **Panel → Card Distributor → Cards**
2. Click **"Generate Cards"**
3. Specify:
   - Quantity (max 1000)
   - Denomination (card value)
   - Expiration date
4. Click **"Generate"**
5. Cards generated with unique numbers and PINs
6. Download card list (secure it properly)

#### 7.2 Check Card Inventory
1. Go to **Panel → Card Distributor → Inventory**
2. View:
   - Total cards
   - Unused cards
   - Used cards
   - Expired cards
3. Filter by denomination or status

#### 7.3 View Sales Report
1. Navigate to **Panel → Card Distributor → Sales**
2. View sales transactions:
   - Cards sold
   - Revenue generated
   - Commission earned
3. Export report for records

---

## 8. Customer Guide

### Overview
Customers access their account to view usage, pay bills, and manage their service.

### Key Responsibilities
- Pay invoices
- Monitor usage
- Update profile
- Submit support tickets

### Common Tasks

#### 8.1 View and Pay Invoice
1. Login to **Customer Panel**
2. Navigate to **Billing**
3. View pending invoices
4. Click **"Pay Now"** on invoice
5. Select payment method:
   - Online (bKash, Nagad, Card)
   - Offline (Bank transfer, Card distributor)
6. Complete payment
7. Account activated automatically after payment

#### 8.2 Check Usage Statistics
1. Go to **Panel → Customer → Usage**
2. View:
   - Data uploaded/downloaded
   - Session history
   - Current bandwidth
3. Review monthly trends

#### 8.3 Submit Support Ticket
1. Navigate to **Panel → Customer → Support**
2. Click **"New Ticket"**
3. Select issue category
4. Describe problem
5. Attach screenshots if needed
6. Submit ticket
7. Track ticket status and responses

#### 8.4 Update Profile
1. Go to **Panel → Customer → Profile**
2. Update:
   - Contact information
   - Email address
   - Phone number
3. Change password if needed
4. Save changes

---

## 9. Developer Guide

### Overview
Developers integrate with the system via API and manage system customizations.

### Key Responsibilities
- API integration
- System debugging
- Custom feature development
- API documentation review

### Common Tasks

#### 9.1 Generate API Token
1. Login to **Developer Panel**
2. Navigate to **API Settings**
3. Click **"Generate New Token"**
4. Name the token
5. Select scopes/permissions
6. Copy token (shown only once)
7. Use in API requests

#### 9.2 View API Documentation
1. Go to **Panel → Developer → API Docs**
2. Browse endpoint documentation
3. Test endpoints using built-in API tester
4. Copy sample code

#### 9.3 Check System Logs
1. Navigate to **Panel → Developer → Logs**
2. Filter by:
   - Log level
   - Module
   - Date range
3. Debug issues
4. Export logs for analysis

#### 9.4 Run Debug Tools
1. Go to **Panel → Developer → Debug**
2. Available tools:
   - Database query monitor
   - Cache manager
   - Queue monitor
   - Schedule tester
3. Use tools to diagnose issues

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

**Document Version:** 1.0  
**Last Updated:** 2026-01-18  
**Next Review:** 2026-04-18
