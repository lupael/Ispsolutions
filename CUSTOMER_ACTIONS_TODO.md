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
- [ ] Add RADIUS integration for PPPoE customers
- [ ] Add MikroTik API integration for network provisioning
- [ ] Add notification to customer
- [ ] Add audit logging

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
- [ ] Add suspend reason parameter
- [ ] Add RADIUS integration to disable network access
- [ ] Add MikroTik API integration to disconnect session
- [ ] Add notification to customer
- [ ] Add audit logging
- [ ] Create suspend modal UI with reason selection

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

### 2.2 Generate Bill ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/bills/create`  
**Policy:** `generateBill()`

**Requirements:**
- Create invoice for customer
- Calculate amount based on package
- Set due date
- Support different billing cycles (monthly, daily, etc.)
- Support prorated billing
- Generate invoice number
- Store in invoices table
- Support partial billing

---

### 2.3 Edit Billing Profile ⚪
**Status:** Planned  
**Route:** `GET/PUT /panel/admin/customers/{id}/billing-profile`  
**Policy:** `editBillingProfile()`

**Requirements:**
- Change billing date
- Change billing cycle (monthly/daily/yearly)
- Change payment method
- Change billing contact info
- Show impact of changes
- Require confirmation for major changes

---

### 2.4 Advance Payment ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/advance-payment`  
**Policy:** `advancePayment()`

**Requirements:**
- Record advance payment
- Update customer balance
- Create payment record
- Link to invoice (if applicable)
- Support different payment methods
- Generate receipt
- Update accounting records

---

### 2.5 Other Payment ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/other-payment`

**Requirements:**
- Record non-package payments (installation, equipment, etc.)
- Specify payment type/category
- Update accounting
- Generate receipt
- Link to expense if applicable

---

## 3. Network & Speed Management

### 3.1 Edit Speed Limit 🟡
**Status:** Partial (existing controller needs enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerSpeedLimitController.php` (exists, needs review)
- Policy: `app/Policies/CustomerPolicy.php::editSpeedLimit()` ✅

**Requirements:**
- [ ] Review existing implementation
- [ ] Add RADIUS attribute updates
- [ ] Add MikroTik queue updates
- [ ] Support "0 = managed by router" option
- [ ] Add temporary vs permanent speed changes
- [ ] Add expiry date for temporary changes
- [ ] Show current speed limits
- [ ] Add validation

---

### 3.2 Edit Time Limit 🟡
**Status:** Partial (existing controller needs enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerTimeLimitController.php` (exists, needs review)
- Policy: Uses `editSpeedLimit()` permission

**Requirements:**
- [ ] Review existing implementation
- [ ] Add session timeout configuration
- [ ] Add daily/weekly time limits
- [ ] Add time-of-day restrictions
- [ ] Update RADIUS attributes
- [ ] Show current time limits

---

### 3.3 Edit Volume Limit 🟡
**Status:** Partial (existing controller needs enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerVolumeLimitController.php` (exists, needs review)
- Policy: Uses `editSpeedLimit()` permission

**Requirements:**
- [ ] Review existing implementation
- [ ] Add data quota configuration
- [ ] Add monthly/daily data limits
- [ ] Update RADIUS attributes
- [ ] Show current usage vs limit
- [ ] Support FUP integration

---

### 3.4 Activate FUP (Fair Usage Policy) ⚪
**Status:** Planned  
**Route:** `POST /panel/admin/customers/{id}/activate-fup`  
**Policy:** `app/Policies/CustomerPolicy.php::activateFup()` ✅

**Requirements:**
- Check current data usage
- Compare with package FUP threshold
- Apply reduced speed if threshold exceeded
- Update RADIUS rate limit attributes
- Disconnect to apply new limits
- Log FUP activation
- Support FUP reset (monthly/weekly)
- Show FUP status in UI

**Dependencies:**
- PackageFup model (existing)
- RADIUS integration
- MikroTik API for disconnection

---

### 3.5 Remove MAC Bind 🟡
**Status:** Partial (existing controller needs enhancement)  
**Files:**
- Controller: `app/Http/Controllers/Panel/CustomerMacBindController.php` (exists)
- Policy: `app/Policies/CustomerPolicy.php::removeMacBind()` ✅

**Requirements:**
- [ ] Review existing implementation
- [ ] Add UI button on customer details page
- [ ] Remove MAC binding from RADIUS
- [ ] Clear MikroTik MAC binding if applicable
- [ ] Log the action
- [ ] Show confirmation dialog

---

## 4. Communication & Support

### 4.1 Send SMS ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/send-sms`  
**Policy:** `app/Policies/CustomerPolicy.php::sendSms()` ✅

**Requirements:**
- Show SMS compose form
- Support predefined templates
- Support variable replacement (name, package, due amount, etc.)
- Validate phone number
- Check SMS gateway configuration
- Send via configured SMS gateway
- Store in sms_logs table
- Show delivery status
- Charge operator's SMS balance

**Dependencies:**
- SmsGateway model (existing)
- SmsLog model (existing)
- SMS gateway integration

---

### 4.2 Send Payment Link ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/send-payment-link`  
**Policy:** `app/Policies/CustomerPolicy.php::sendLink()` ✅

**Requirements:**
- Generate unique payment link
- Include customer ID and invoice ID
- Support multiple payment gateways
- Send via SMS and/or email
- Track link opens/clicks
- Support link expiry
- Show payment status

**Dependencies:**
- Payment gateway integration
- SMS/Email service

---

### 4.3 Add Complaint ⚪
**Status:** Planned (use existing ticket system)  
**Route:** `GET /panel/tickets/create?customer_id={id}` (existing)

**Implementation Notes:**
- Already implemented via ticket system
- Button exists on customer details page
- No additional work needed

---

## 5. Additional Features

### 5.1 Internet History / Export ⚪
**Status:** Planned  
**Route:** `GET /panel/admin/customers/{id}/internet-history`

**Requirements:**
- Export session history from RadAcct
- Support date range selection
- Show data usage, session time, IPs
- Export to CSV/Excel
- Filter by session type (PPPoE/Hotspot)
- Show bandwidth usage graphs
- Calculate total usage and time

---

### 5.2 Change Operator ⚪
**Status:** Planned  
**Route:** `GET/POST /panel/admin/customers/{id}/change-operator`  
**Policy:** `app/Policies/CustomerPolicy.php::changeOperator()` ✅

**Requirements:**
- Only for high-level operators (level <= 20)
- Transfer customer to different operator
- Update created_by field
- Update billing responsibility
- Transfer invoices and payments
- Update commission records
- Require confirmation from both operators
- Log the transfer
- Send notifications

---

### 5.3 Check Usage ⚪
**Status:** Planned (UI exists, backend needed)  
**Implementation:** AJAX endpoint

**Requirements:**
- Query RADIUS for real-time usage
- Show current session info
- Show data uploaded/downloaded
- Show session duration
- Show bandwidth utilization
- Refresh without page reload
- Support for offline customers

---

### 5.4 Edit Suspend Date 🟡
**Status:** Partial  
**Policy:** `app/Policies/CustomerPolicy.php::editSuspendDate()` ✅

**Requirements:**
- [ ] Set custom suspend/expiry date
- [ ] Show current suspend date
- [ ] Calculate billing impact
- [ ] Send reminder before suspension
- [ ] Auto-suspend on date
- [ ] Support recurring dates

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

### 5.6 Hotspot Recharge ⚪
**Status:** Planned  
**Policy:** `app/Policies/CustomerPolicy.php::hotspotRecharge()` ✅

**Requirements:**
- For hotspot customers
- Support voucher-based recharge
- Support time-based packages
- Support data-based packages
- Generate hotspot credentials
- Update MikroTik hotspot user
- Set expiry based on package

---

## Priority Implementation Order

### Phase 1: Critical Features (Week 1-2)
1. ✅ Disconnect Customer
2. ✅ Change Package
3. ⚪ Activate FUP
4. ⚪ Generate Bill

### Phase 2: Important Features (Week 3-4)
5. ⚪ Send SMS
6. ⚪ Send Payment Link
7. ⚪ Advance Payment
8. 🟡 Edit Speed Limit (enhance existing)
9. 🟡 Remove MAC Bind (enhance existing)

### Phase 3: Enhancement Features (Week 5-6)
10. ⚪ Edit Billing Profile
11. ⚪ Internet History Export
12. ⚪ Check Usage (real-time)
13. 🟡 Edit Time Limit (enhance existing)
14. 🟡 Edit Volume Limit (enhance existing)

### Phase 4: Advanced Features (Week 7-8)
15. ⚪ Change Operator
16. ⚪ Other Payment
17. ⚪ Hotspot Recharge
18. ⚪ Edit Suspend Date

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
4. Some existing controllers (speed/time/volume limit) need review

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

Last Updated: 2026-01-26
