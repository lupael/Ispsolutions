# ISP Solution Development TODO

This document outlines the tasks required to build the multi-tier ISP management platform.

## Phase 1: Core Structure and Developer Panel


- [ ] **Task 1.2: Database Schema**
    - [ ] Create migrations for users (developers, super-admins, admins, operators, customers).
    - [ ] Create migrations for ISP slots, subscriptions, and tenants.
    - [ ] Create migrations for hardware nodes (MikroTik, OLTs) and inventory.
    - [ ] Create migrations for billing, invoices, and payment gateways.
    - [ ] Create migrations for audit trails and signal monitoring.
- [ ] **Task 1.3: Tier 1 - Developer Panel**
    - [ ] Implement Super Admin account lifecycle management (Create, Suspend, Manage).
    - [ ] Develop ISP Slot management functionality.
    - [ ] Build the "Login As" impersonation engine.
    - [ ] Create the global revenue analytics dashboard.
    - [ ] Implement the system-wide audit trail.

## Phase 2: Super Admin and Admin Panels

- [ ] **Task 2.1: Tier 2 - Super Admin Panel**
    - [ ] Implement ISP (Admin) account management within allocated slots.
    - [ ] Automate suspension of child ISPs based on subscription status.
    - [ ] Develop Business Intelligence dashboard for sub-ISP analytics.
- [ ] **Task 2.2: Tier 3 - Admin Panel**
    - [ ] Implement Hardware Node Manager for MikroTik and OLT devices.
    - [ ] Develop the auto-provisioning engine for ONTs/ONUs.
    - [ ] Create the inventory tracking system.
    - [ ] Implement staff role management for "Technician" accounts.

## Phase 3: Network, Billing, and Customer Portal

- [ ] **Task 3.1: Network Automation**
    - [ ] Develop multi-vendor OLT drivers (VSOL, BDCOM, Huawei, ZTE).
    - [ ] Implement "Signal Guard" for background signal strength polling.
    - [ ] Create the "Heartbeat Monitor" for network nodes.
- [ ] **Task 3.2: Billing & Financials**
    - [ ] Implement the automated monthly billing cycle.
    - [ ] Integrate bKash and Nagad payment gateways.
    - [ ] Develop the auto-suspension mechanism for non-payment.
- [ ] **Task 3.3: Customer Self-Service Portal**
    - [ ] Implement real-time signal strength and uptime visibility.
    - [ ] Develop billing management features (view/download invoices, pay).
    - [ ] Create the "Bandwidth Turbo" feature for temporary speed boosts.

## Phase 4: Public Frontend and Security

- [ ] **Task 4.1: Public Landing Page**
    - [ ] Design and build the SaaS marketing website.
    - [ ] Implement the automated onboarding flow for Super Admins.
- [ ] **Task 4.2: Security & Performance**
    - [ ] Implement AES-256 encryption for all sensitive credentials.
    - [ ] Ensure strict tenant isolation across the database.
    - [ ] Optimize database queries and add necessary indexes.
    - [ ] Conduct a final security audit.
