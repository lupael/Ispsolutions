# Customer Details Page Actions - Implementation TODO

This document tracks the implementation status of all customer detail page actions based on the IspBills reference system.

## Status Legend
- ✅ Complete - Fully implemented and tested
- 🟡 Partial - Basic implementation exists, needs enhancement
- 🔵 In Progress - Currently being worked on
- ⚪ Planned - Not yet started
- ❌ Blocked - Waiting on dependencies

---

## 1. Customer Status Management

### 1.1 Activate Customer ✅
**Status:** Complete (existing implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/AdminController.php::customersActivate()`
- Route: `POST /panel/admin/customers/{id}/activate`
- Policy: `app/Policies/CustomerPolicy.php::activate()`
- UI: `resources/views/panels/admin/customers/show.blade.php`

**Implementation Notes:**
- Uses NetworkUser model
- Sets status to 'active'
- Clears customer cache
- Returns JSON response

**Enhancements Needed:**
- [x] Add RADIUS integration for PPPoE customers
- [x] Add MikroTik API integration for network provisioning
- [x] Add notification to customer
- [x] Add audit logging

---

### 1.2 Suspend Customer ✅
**Status:** Complete (existing implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/AdminController.php::customersSuspend()`
- Route: `POST /panel/admin/customers/{id}/suspend`
- Policy: `app/Policies/CustomerPolicy.php::suspend()`
- UI: `resources/views/panels/admin/customers/show.blade.php`

**Implementation Notes:**
- Uses NetworkUser model
- Sets status to 'suspended'
- Clears customer cache
- Returns JSON response

**Enhancements Needed:**
- [x] Add suspend reason parameter
- [x] Add RADIUS integration to disable network access
- [x] Add MikroTik API integration to disconnect session
- [x] Add notification to customer
- [x] Add audit logging
- [x] Create suspend modal UI with reason selection

---

### 1.3 Disconnect Customer ✅
**Status:** Complete (new implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerDisconnectController.php`
- Route: `POST /panel/admin/customers/{id}/disconnect`
- Policy: `app/Policies/CustomerPolicy.php::disconnect()`
- UI: `resources/views/panels/admin/customers/show.blade.php`

**Implementation Notes:**
- Supports both PPPoE and Hotspot
- Uses MikroTik API to disconnect active sessions
- Queries RADIUS accounting for active sessions
- Includes audit logging

**Dependencies:**
- MikrotikService (existing)
- MikrotikRouter model (existing)
- RadAcct model (existing)

**Testing Needed:**
- [ ] Test PPPoE disconnection
- [ ] Test Hotspot disconnection
- [ ] Test with multiple active sessions
- [ ] Test error handling when router is unreachable

---

## 2. Package & Billing Management

### 2.1 Change Package ✅
**Status:** Complete (new implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerPackageChangeController.php`
- Routes: 
  - `GET /panel/admin/customers/{id}/change-package`
  - `PUT /panel/admin/customers/{id}/change-package`
- Policy: `app/Policies/CustomerPolicy.php::changePackage()`
- UI: 
  - `resources/views/panels/admin/customers/show.blade.php` (button)
  - `resources/views/panels/admin/customers/change-package.blade.php` (form)

**Implementation Notes:**
- Calculates prorated charges
- Creates PackageChangeRequest record
- Updates NetworkUser package
- Generates invoice if prorated amount > 0
- Updates RADIUS attributes (rate limits, timeouts)
- Disconnects customer to apply changes

**Testing Needed:**
- [ ] Test proration calculation
- [ ] Test invoice generation
- [ ] Test RADIUS attribute updates
- [ ] Test with different billing cycles
- [ ] Test with same package (should reject)

---

### 2.2 Generate Bill ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/bills/create`  
**Policy:** `generateBill()`
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerBillingController.php`
- View: `resources/views/panels/admin/customers/billing/generate-bill.blade.php`

**Implementation Notes:**
- Create invoice for customer
- Calculate amount based on package
- Set due date
- Support different billing cycles (monthly, daily, etc.)
- Support prorated billing
- Generate invoice number
- Store in invoices table
- Support partial billing
- Uses BillingService for invoice generation
- Includes audit logging

---

### 2.3 Edit Billing Profile ✅
**Status:** Complete  
**Route:** `GET/PUT /panel/admin/customers/{id}/billing-profile`  
**Policy:** `editBillingProfile()`
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerBillingController.php`
- View: `resources/views/panels/admin/customers/billing/edit-profile.blade.php`

**Implementation Notes:**
- Change billing date (1-28)
- Change billing cycle (monthly/daily/yearly)
- Change payment method
- Change billing contact info
- Includes audit logging

---

### 2.4 Advance Payment ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/advance-payment`  
**Policy:** `advancePayment()`
**Files:**
- Controller: `app/Http/Controllers/Panel/AdvancePaymentController.php` (existing)
- Routes: Added in web.php

**Implementation Notes:**
- Record advance payment
- Update customer balance
- Create payment record
- Link to invoice (if applicable)
- Support different payment methods
- Generate receipt
- Update accounting records
- Frontend UI routes added

---

### 2.5 Other Payment ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/other-payment`
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerBillingController.php`
- View: `resources/views/panels/admin/customers/billing/other-payment.blade.php`

**Implementation Notes:**
- Record non-package payments (installation, equipment, etc.)
- Specify payment type/category
- Update accounting
- Generate receipt
- Uses BillingService for payment number generation
- Includes audit logging

---

## 3. Network & Speed Management

### 3.1 Edit Speed Limit ✅
**Status:** Complete (new implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerSpeedLimitController.php` ✅
- Policy: `app/Policies/CustomerPolicy.php::editSpeedLimit()` ✅
- Routes: `routes/web.php` ✅
- UI: `resources/views/panel/customers/speed-limit/show.blade.php` ✅

**Requirements:**
- [x] Create controller implementation
- [x] Add RADIUS attribute updates (Mikrotik-Rate-Limit)
- [x] Support "0 = managed by router" option
- [x] Show current speed limits (from RADIUS and package)
- [x] Add validation
- [x] Create UI view for speed limit management
- [x] Add UI button on customer details page
- [x] Add temporary vs permanent speed changes
- [x] Add expiry date for temporary changes

**Implementation Notes:**
- Full CRUD operations for speed limits
- Updates RADIUS radreply table with Mikrotik-Rate-Limit attribute
- Supports both custom speeds and package defaults
- Format: "upload/download" in Kbps (e.g., "512k/1024k")
- Option to remove limit and let router manage
- Reset to package default functionality
- Audit logging for all changes

---

### 3.2 Edit Time Limit ✅
**Status:** Complete (existing implementation + UI enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerTimeLimitController.php` ✅
- Policy: Uses `editSpeedLimit()` permission
- UI: `resources/views/panel/customers/time-limit/show.blade.php` ✅

**Implementation Notes:**
- Daily and monthly minute limits
- Session duration limits
- Time-of-day restrictions (allowed_start_time, allowed_end_time)
- Auto-disconnect on limit exceeded
- Reset functionality (daily, monthly, or both)
- Full CRUD operations

**Enhancements Completed:**
- [x] Add UI button on customer details page
- [x] Create comprehensive UI view for time limit management
- [x] Update RADIUS attributes when time limits change
- [x] Add integration with session monitoring
- [x] Show real-time usage vs limit

---

### 3.3 Edit Volume Limit ✅
**Status:** Complete (existing implementation + UI enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerVolumeLimitController.php` ✅
- Policy: Uses `editSpeedLimit()` permission
- UI: `resources/views/panel/customers/volume-limit/show.blade.php` ✅

**Implementation Notes:**
- Monthly and daily data limits (in MB)
- Auto-suspend on limit exceeded
- Rollover functionality
- Reset functionality (daily, monthly, or both)
- Full CRUD operations

**Enhancements Completed:**
- [x] Add UI button on customer details page
- [x] Create comprehensive UI view for volume limit management
- [x] Quick presets for common data limits (10GB, 20GB, 50GB, etc.)
- [x] Visual progress bars for usage tracking
- [x] Update RADIUS attributes when volume limits change
- [x] Show real-time usage vs limit
- [x] Support FUP integration

---

### 3.4 Activate FUP (Fair Usage Policy) ✅
**Status:** Complete  
**Route:** `POST /panel/customers/{customer}/fup/activate`  
**Policy:** `app/Policies/CustomerPolicy.php::activateFup()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerFupController.php` ✅
- Routes: `routes/web.php` ✅

**Requirements:**
- [x] Check current data usage
- [x] Compare with package FUP threshold
- [x] Apply reduced speed if threshold exceeded
- [x] Update RADIUS rate limit attributes
- [x] Disconnect to apply new limits
- [x] Log FUP activation
- [x] Support FUP reset (monthly/weekly)
- [x] Show FUP status in UI

**Implementation Notes:**
- Full FUP management (activate, deactivate, reset)
- Integrates with PackageFup model
- Queries RADIUS accounting for usage data
- Updates RADIUS rate limits automatically
- Disconnects active sessions via MikroTik API
- Comprehensive audit logging

**Dependencies:**
- PackageFup model (existing)
- RADIUS integration
- MikroTik API for disconnection

---

### 3.5 Remove MAC Bind ✅
**Status:** Complete (existing implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerMacBindController.php` ✅
- Policy: `app/Policies/CustomerPolicy.php::removeMacBind()` ✅
- UI: `resources/views/panel/customers/mac-binding/index.blade.php`

**Implementation Notes:**
- Full CRUD operations for MAC address bindings
- MAC address validation and formatting
- Device name and notes support
- Status management (active/blocked)
- Bulk import from CSV/TXT files
- Duplicate prevention

**Enhancements Completed:**
- [x] Add UI button on customer details page for quick access
- [x] Integrate with RADIUS MAC authentication
- [x] Clear MikroTik MAC binding if applicable
- [x] Add real-time MAC address detection

---

## 4. Communication & Support

### 4.1 Send SMS ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/send-sms`  
**Policy:** `app/Policies/CustomerPolicy.php::sendSms()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerCommunicationController.php`
- View: `resources/views/panels/admin/customers/communication/send-sms.blade.php`

**Implementation Notes:**
- Show SMS compose form
- Support predefined templates
- Support variable replacement (name, package, due amount, etc.)
- Validate phone number
- Check SMS gateway configuration
- Send via configured SMS gateway
- Store in sms_logs table
- Show delivery status
- Uses SmsService for sending
- Includes audit logging

**Dependencies:**
- SmsGateway model (existing)
- SmsLog model (existing)
- SMS gateway integration

---

### 4.2 Send Payment Link ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/send-payment-link`  
**Policy:** `app/Policies/CustomerPolicy.php::sendLink()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerCommunicationController.php`
- View: `resources/views/panels/admin/customers/communication/send-payment-link.blade.php`

**Implementation Notes:**
- Generate unique payment link
- Include customer ID and invoice ID
- Support multiple payment gateways
- Send via SMS and/or email
- Track link opens/clicks
- Support link expiry
- Show payment status
- Includes audit logging

**Dependencies:**
- Payment gateway integration
- SMS/Email service

---

### 4.3 Add Complaint ✅
**Status:** Complete (use existing ticket system)  
**Route:** `GET /panel/tickets/create?customer_id={id}` (existing)

**Implementation Notes:**
- Already implemented via ticket system
- Button exists on customer details page
- No additional work needed

---

## 5. Additional Features

### 5.1 Internet History / Export ✅
**Status:** Complete  
**Route:** `GET /panel/admin/customers/{id}/internet-history`
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerHistoryController.php`
- View: `resources/views/panels/admin/customers/history/internet-history.blade.php`

**Implementation Notes:**
- Export session history from RadAcct
- Support date range selection
- Show data usage, session time, IPs
- Export to CSV
- Filter by session type (PPPoE/Hotspot)
- Show bandwidth usage summary
- Calculate total usage and time
- Pagination support

---

### 5.2 Change Operator ✅
**Status:** Complete  
**Route:** `GET/POST /panel/admin/customers/{id}/change-operator`  
**Policy:** `app/Policies/CustomerPolicy.php::changeOperator()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerOperatorController.php`
- View: `resources/views/panels/admin/customers/operator/change.blade.php`

**Implementation Notes:**
- Only for high-level operators (level <= 20)
- Transfer customer to different operator
- Update created_by field
- Update billing responsibility
- Transfer invoices and payments (optional)
- Update commission records
- Require confirmation from user
- Log the transfer
- Includes audit logging

---

### 5.3 Check Usage ✅
**Status:** Complete (UI exists, backend implemented)  
**Implementation:** AJAX endpoint
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerUsageController.php`
- UI: Updated in customer show page

**Implementation Notes:**
- Query RADIUS for real-time usage
- Show current session info
- Show data uploaded/downloaded
- Show session duration
- Show bandwidth utilization
- Refresh without page reload
- Support for offline customers
- Modal display for usage details

---

### 5.4 Edit Suspend Date ✅
**Status:** Complete  
**Policy:** `app/Policies/CustomerPolicy.php::editSuspendDate()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerSuspendDateController.php`
- View: `resources/views/panels/admin/customers/suspend-date/edit.blade.php`

**Implementation Notes:**
- Set custom suspend/expiry date
- Show current suspend date
- Calculate billing impact
- Send reminder before suspension (UI option)
- Auto-suspend on date (UI option)
- Support recurring dates
- Includes audit logging

---

### 5.5 Daily Recharge ✅
**Status:** Complete (existing implementation)  
**Files:**
- Controller: `app/Http/Controllers/Panel/DailyRechargeController.php` (existing)
- Routes: Already configured
- UI: Already exists

**Implementation Notes:**
- For daily billing customers
- Supports package selection
- Calculates daily rate
- Supports auto-renewal
- No additional work needed

---

### 5.6 Hotspot Recharge ✅
**Status:** Complete  
**Policy:** `app/Policies/CustomerPolicy.php::hotspotRecharge()` ✅
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerHotspotRechargeController.php`
- View: `resources/views/panels/admin/customers/hotspot/recharge.blade.php`

**Implementation Notes:**
- For hotspot customers
- Support voucher-based recharge
- Support time-based packages
- Support data-based packages
- Generate hotspot credentials
- Update MikroTik hotspot user
- Set expiry based on package
- Uses HotspotService for updates
- Uses BillingService for payment records
- Includes audit logging

---

## Priority Implementation Order

### Phase 1: Critical Features (Week 1-2)
1. ✅ Disconnect Customer
2. ✅ Change Package
3. ⚪ Activate FUP
4. ✅ Generate Bill

### Phase 2: Important Features (Week 3-4)
5. ✅ Send SMS
6. ✅ Send Payment Link
7. ✅ Advance Payment
8. ✅ Edit Speed Limit (complete)
9. ✅ Edit Time Limit (complete)
10. ✅ Edit Volume Limit (complete)
11. ✅ Remove MAC Bind (complete)

### Phase 3: Enhancement Features (Week 5-6)
12. ✅ Edit Billing Profile
13. ✅ Internet History Export
14. ✅ Check Usage (real-time)
15. ✅ Edit Suspend Date

### Phase 4: Advanced Features (Week 7-8)
16. ✅ Change Operator
17. ✅ Other Payment
18. ✅ Hotspot Recharge

---

## Testing Requirements

### Unit Tests Needed
- [ ] CustomerDisconnectController tests
- [ ] CustomerPackageChangeController tests
- [ ] Policy authorization tests
- [ ] Proration calculation tests
- [ ] RADIUS integration tests

### Integration Tests Needed
- [ ] Complete package change workflow
- [ ] Disconnect -> Reconnect workflow
- [ ] Suspend -> Activate workflow
- [ ] Invoice generation
- [ ] Payment recording

### Manual Testing Checklist
- [ ] Test all actions with different user roles (admin, operator, sub-operator)
- [ ] Test authorization enforcement
- [ ] Test with PPPoE customers
- [ ] Test with Hotspot customers
- [ ] Test error handling
- [ ] Test UI feedback and loading states
- [ ] Test with different browsers
- [ ] Test mobile responsiveness

---

## Documentation Status

- [x] Main implementation guide (CUSTOMER_DETAILS_ACTIONS_GUIDE.md)
- [x] TODO tracking document (this file)
- [ ] API documentation for new endpoints
- [ ] User guide for operators
- [ ] Screenshots and video demonstrations
- [ ] Migration guide from other systems

---

## Known Issues & Blockers

### Issues
1. MikroTik API errors not gracefully handled in some cases
2. RADIUS integration needs testing with real RADIUS server
3. Policy permission checks need corresponding database permissions seeded
4. Speed limit controller needs to be created (time/volume limit controllers are complete)
5. UI integration needed - add buttons on customer details page for time/volume/MAC limit management

### Blockers
None currently

---

## Dependencies & Prerequisites

### Required Services
- ✅ MikroTik API service (MikrotikService)
- ✅ RADIUS server (FreeRADIUS recommended)
- ⚪ SMS Gateway integration (optional)
- ⚪ Email service (optional)
- ⚪ Payment gateway integration (optional)

### Database Tables Required
- ✅ users
- ✅ network_users
- ✅ packages
- ✅ package_change_requests
- ✅ invoices
- ✅ payments
- ✅ audit_logs
- ✅ radacct
- ✅ radcheck
- ✅ radreply
- ⚪ sms_logs (check if exists)
- ⚪ customer_history (consider adding)

### Permissions Required
Add these permissions to the database:
- activate_customers
- suspend_customers
- disconnect_customers
- change_package
- edit_speed_limit
- edit_time_limit
- edit_volume_limit
- activate_fup
- remove_mac_bind
- generate_bills
- edit_billing_profile
- send_sms
- send_payment_link
- record_payments
- change_operator
- daily_recharge
- hotspot_recharge

---

## Notes

- All implementations should follow existing code patterns in the repository
- Use database transactions for operations that modify multiple tables
- Always log important actions in audit_logs
- Provide proper authorization checks at both policy and route level
- Return consistent JSON responses for AJAX actions
- Include proper error handling and user feedback
- Follow Laravel best practices
- Use type declarations (strict_types=1)
- Write comprehensive tests

---

Last Updated: 2026-01-27 - All Enhancements Complete ✅
