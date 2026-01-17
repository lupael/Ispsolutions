# TODO - Remaining Features & Tasks

**Last Updated:** 2026-01-16  
**Based on:** Development Review Report

This document tracks all remaining features, enhancements, and tasks for the ISP Billing & Network Monitoring System.

---

## 📋 Quick Reference

- **Recently Completed:** None — project reset to start from the first task
- **Total Missing Features:** All (we start development from the first code/task)
- **Partial Implementations:** None (everything reset to remaining)
- **Frontend Views:** Pending
- **Panel Dashboards:** Pending
- **CRUD Views:** Pending
- **Stub Implementations:** Pending
- **Quick Wins Available:** Pending
- **Medium Features:** Pending
- **Large Features:** Pending

---

## 🎯 Priority Matrix

### Critical (Required for MVP) — Start here (in order)
1. PPPoE Daily Billing implementation
2. PPPoE Monthly Billing implementation
3. Complete Auto Bill Generation
4. Complete Payment Gateway Integration

All of the above are pending and should be implemented in sequence starting from item 1.

### High Priority (Core Functionality)
- Complete Reseller Commission Automation (multi-level hierarchy, automated distribution)
- PDF/Excel Export functionality (library integration, templates)
- Email Notification System (templates & scheduling)
- Customer Self-Service Portal (Backend + Frontend Views)
- All Frontend Blade Views

### Medium Priority (Enhanced Features)
- MikroTik Router API Integration
- RADIUS Server Integration
- IP Address Management (IPAM)
- SMS Notification Integration (gateway integration, templates)

### Low Priority (Future Enhancements)
- VPN Account Management (Multi-protocol support)
- Real-time Network Monitoring Dashboard
- Mobile Applications (Android/iOS)
- Advanced Analytics

---

## 🔢 Development Plan (Start from task 1)

We will treat this repo as a fresh start. All tasks below are unchecked and must be implemented from the beginning. Follow the order in the Critical section and move to High / Medium after completing MVP items.

### 1. PPPoE Daily Billing (First priority)
- Goals:
  - Implement BillingService daily billing logic
  - Add pro-rated billing calculation
  - Generate daily invoices
  - Update package model to support daily billing
  - Implement automatic account locking after expiration
  - Support variable validity periods (7, 15 days, etc.)
- Files to create/modify:
  - app/Services/BillingService.php
  - app/Console/Commands/LockExpiredAccounts.php
  - database/migrations/* (if package model changes required)
- Acceptance criteria:
  - Daily invoice generation for PPPoE customers with correct pro-rating
  - Accounts locked automatically after expiry
  - Tests cover billing calculations and scheduled command

### 2. PPPoE Monthly Billing
- Goals:
  - Monthly billing cycle logic & recurring invoices
  - Monthly billing automation (scheduled)
  - Update package model to support monthly billing
  - Automatic account locking via scheduler
- Files to create/modify:
  - app/Services/BillingService.php
  - app/Console/Commands/GenerateMonthlyInvoices.php
  - app/Console/Kernel.php
- Acceptance criteria:
  - Recurring invoices run on schedule and can be tested locally
  - Tests for monthly billing flows

### 3. Auto Bill Generation
- Goals:
  - Scheduled command(s) to create invoices automatically
  - Pro-rated and recurring rules enforced
  - Expiration detection and handling
- Files:
  - app/Services/BillingService.php
  - app/Console/Commands/*
  - app/Console/Kernel.php

### 4. Payment Gateway Integration
- Goals:
  - Integrate primary gateways (start with bKash, nagad, SSLCommerz and strip as applicable)
  - Webhook/callback handlers and verification flows
  - Payment processing hooks to BillingService (auto-unlock on payment)
- Files:
  - app/Services/PaymentGatewayService.php
  - app/Http/Controllers/PaymentController.php
  - config/payment.php
- Acceptance criteria:
  - Simulated payments pass through and trigger account activation logic
  - Tests for payment callback handling and verification

---

## 🗂 Remaining Features (Updated with completed panel work)

### ✅ Recently Completed (2026-01-17)
- ✅ All Panel Controllers (SuperAdmin, Admin, Manager, Staff, Reseller, Sub-Reseller, Customer, Card Distributor, Developer)
- ✅ All Panel Views (50+ views across 9 roles)
- ✅ Role-based middleware (CheckRole, CheckPermission)
- ✅ Routes with proper middleware protection
- ✅ Dashboard views for all 9 roles
- ✅ CRUD views for primary entities
- ✅ Responsive layouts with Tailwind CSS
- ✅ Dark mode support

### 🚧 In Progress
- Backend logic implementation for controllers
- Form validation and CRUD operations
- Testing infrastructure

### 📋 Remaining Features
(Each item below must be implemented after panel foundation work)

- Reseller Commission Automation (percentage-based, multi-level)
- Hotspot User Management
- Hotspot User self signup using Mobile - OTP
- Static IP Monthly Billing
- Cable TV automation
- Other service types
- Notifications (Email, Whatsapp & SMS) for Transaction
- Pre-expiration Notifications (Email, Whatsapp & SMS)
- Auto Unlock on Payment
- Test Coverage (unit, feature, integration, E2E)
- VPN Account Management for connecting router without public ip
- Real-time Network Monitoring Dashboard (partially implemented with stats)
- IPAM (exists, needs integration with panels)
- MikroTik Router API Integration (exists, needs panel integration)
- RADIUS Server Integration (exists, needs panel integration)
- PDF/Excel Export Functionality
- Accounting Automation
- VAT
- Documentation (API, developer & user guides)
- Security (2FA, rate limiting, audit)
- Performance & DevOps (CI/CD, monitoring, backups)
- Mobile apps & Advanced features
- Third-party integrations (WhatsApp, Telegram, CRM, Accounting tools)

---

## ✅ Checklist for starting development (concrete next steps)

1. Create a new branch for the first task:
   - Suggested branch name: feature/billing-pppoe-daily
2. Implement BillingService skeleton and daily billing logic
3. Add migration(s) if package model requires new fields
4. Add scheduled command and local testing instructions
5. Add unit and feature tests covering billing calculations and scheduled tasks
6. Open a pull request for review once tests pass

If you confirm, I can:
- Create branch and open a PR with the updated TODO.md (provide repo owner/name and branch title), or
- Create an issue for the first task (PPPoE Daily Billing) in the repo, or
- Produce a minimal starter patch (BillingService skeleton + migration + test skeleton) here for you to review.

---

## 📊 Progress Tracking (reset)
- Fully Implemented: 0/31 (0%)
- Partial Implementation: 0/31 (0%)
- Missing: 31/31 (100%)
- Critical Tasks: 0/4 completed (0%)
- Test Coverage: To be established (start adding tests with first PR)

---

## 🔄 Update History

| Date | Updated By | Changes |
|------|------------|---------|
| 2026-01-16 | lupael | Reset "Recently Completed" to None and marked all tasks as remaining — start development from the first code/task |

---

**Note:** The file has been reset so development begins from the top (PPPoE Daily Billing). Reply with which next action you want:
- "create-branch PR" — include repo owner/name and branch title (I will prepare commit/PR instructions), or
- "create-issue" — I will draft an issue for PPPoE Daily Billing, or
- "generate-starter-code" — I will produce the BillingService skeleton, migration, command and tests to paste into your repo.
