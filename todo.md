✅ Developer TODO Checklist 

## 1. Laravel 12.x Upgrade
- [ ] All upgrade tasks completed, including dependency updates, refactoring, and validation of routes, middleware, and authentication flows.

---
## 2. Authentication & AAA
- [ ] Validate **FreeRADIUS** integration for PPPoE and Hotspot.
- [ ] Implement **WebAuthn** for passwordless login.
  - [ ] Backend routes and controller are in place.
  - [ ] Created a test page for registration and login.
  - [ ] Integrated WebAuthn into login and profile pages.
- [ ] Enforce MAC binding and duplicate session prevention.
- [ ] Test router → RADIUS → Laravel flow for PPPoE and Hotspot.

---

## 5. Billing & Payments
- [ ] Implement daily vs monthly billing cycles.
- [ ] Ensure prepaid/postpaid logic consistency.
- [ ] Validate commission splits across reseller hierarchy.
- [ ] Add SQL constraints to prevent duplicate bills/payments.
- [ ] Test invoice generation (PDF/Excel).
- [x] **Monthly Billing Customers**: Auto-generate bills on the 1st of each month.
- [ ] **Network Access Termination**: Ensure service is cut off immediately upon package expiry.

---

## 6. SMS Gateway Providers Integration
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

## 7. Payment Gateway Integration
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

## 8. Router & Network Integration
- [ ] Refactor MikroTik API calls into modular services.
- [ ] Move hardcoded IP ranges/firewall rules into config files.
- [ ] Add error handling for router API failures.
- [ ] Validate suspended user blocking via firewall rules.
- [ ] Test PPPoE and Hotspot provisioning end-to-end.

---

## 9. Database Schema & Integrity
- [ ] Add foreign key constraints for customer–bill–payment relationships.
- [ ] Enforce unique indexes for usernames, MAC addresses, and IPs.
- [ ] Run migrations to clean deprecated fields.
- [ ] Document schema with ERD diagrams.

---

## 10. Frontend & UX
- [ ] Align dashboards with Metronic demo1.
- [ ] Ensure role-based visibility of menus and charts.
- [ ] Validate Chart.js and Mapael integrations.
- [ ] Refactor Axios calls to standardized API endpoints.
- [ ] **Customer Registration**: Implement mobile phone number registration flow.

---

## 11. Testing & CI/CD
- [ ] Implement **PestPHP** or PHPUnit tests.
- [ ] Add frontend tests with Vitest/Jest.
- [ ] Run static analysis with PHPStan/Larastan.
- [ ] Enforce coding standards with PHP-CS-Fixer.
- [ ] Configure CI/CD pipeline for automated builds and tests.

---

## 12. Documentation
- [ ] Update developer onboarding guide with stack requirements.
- [ ] Document Vite + Tailwind build process.
- [ ] Provide migration notes for Laravel 12 changes.
- [ ] Maintain Markdown checklists for each module.
- [ ] Create Postman collection for API endpoints.


