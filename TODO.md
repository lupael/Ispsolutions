# ISP Solution Refactor Todo List

This file tracks the progress of the ISP Solution refactoring, based on the architecture and requirements outlined in `1. Mikrotik_Radius_architecture .md`.

## 1. Authentication & AAA
- [x] Implement FreeRADIUS integration for customer authentication and authorization.
- [x] Validate **FreeRADIUS** integration for PPPoE and Hotspot.
- [x] Implement **WebAuthn** for passwordless login.
- [x] Enforce MAC binding and duplicate session prevention.
- [x] Test router → RADIUS → Laravel flow for PPPoE and Hotspot.

---

## 2. Billing & Payments
- [ ] Implement daily vs monthly billing cycles.
- [x] Ensure prepaid/postpaid logic consistency.
- [ ] Validate commission splits across reseller hierarchy.
- [ ] Add SQL constraints to prevent duplicate bills/payments.
- [ ] Test invoice generation (PDF/Excel).
- [x] **Monthly Billing Customers**: Auto-generate bills on the 1st of each month.
- [x] **Network Access Termination**: Ensure service is cut off immediately upon package expiry.

---

## 3. SMS Gateway Providers Integration
- [ ] Integrate and test each provider:
  - [ ] Maestro
  - [ ] Robi
  - [ ] M2M
  - [ ] BDBangladesh SMS
  - [ ] Bulk SMS BD
  - [ ] BTS SMS
  - [ ] 880 SMS
  - [ ] BD Smart Pay
  - [ ] ElitBuzz
  - [ ] SSL Wireless
  - [ ] ADN
  - [ ] SMS24
  - [ ] SMS BDSMS NetBrand
  - [ ] SMSMetrotel
  - [ ] DianaHostSMS in BD
  - [ ] Dhaka Soft BD
- [ ] Standardize API wrapper for SMS sending.
- [ ] Add fallback mechanism if one provider fails.
- [ ] Log all SMS transactions for audit.
- [ ] **Customer Notifications**: Send SMS before account expiry.

---

## 4. Payment Gateway Integration
- [ ] **Local Gateways**:
  - [ ] bKash (Checkout, Tokenized Checkout, Standard Payment)
  - [ ] Nagad Mobile Financial Service
  - [ ] Rocket Mobile Financial Service
  - [ ] SSLCommerz Aggregator
  - [ ] aamarPay Aggregator
  - [ ] shurjoPay Aggregator
- [ ] **International/Regional Gateways**:
  - [ ] Razorpay
  - [ ] EasyPayWay Aggregator
  - [ ] Walletmix Aggregator
  - [ ] BD Smart Pay Aggregator Service
- [ ] **Manual/Other**:
  - [ ] Recharge Card
  - [ ] Send Money
- [ ] Implement unified payment interface for all gateways.
- [ ] Add webhook handling for payment confirmation.
- [ ] Ensure PCI-DSS compliance for sensitive data.
- [ ] Test refunds, partial payments, and reconciliation.
- [ ] **Customer Online Activation**: Enable service activation upon successful online payment.
- [ ] **Reseller/Sub-reseller Balance**: Allow online balance top-up.
- [ ] **Recharge Card Partners**: Enable online balance addition.

---

## 5. Router & Network Integration
- [ ] Refactor MikroTik API calls into modular services.
- [ ] Move hardcoded IP ranges/firewall rules into config files.
- [ ] Add error handling for router API failures.
- [ ] Validate suspended user blocking via firewall rules.
- [ ] Test PPPoE and Hotspot provisioning end-to-end.

---

## 6. Database Schema & Integrity
- [ ] Add foreign key constraints for customer–bill–payment relationships.
- [ ] Enforce unique indexes for usernames, MAC addresses, and IPs.
- [ ] Run migrations to clean deprecated fields.
- [ ] Document schema with ERD diagrams.