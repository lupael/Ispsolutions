# ISP Solution — Comprehensive Project Overview

> **Generated:** 2026-03-24  
> **Version:** Based on current `main` branch  
> **Audience:** Developers, architects, and stakeholders

---

## Table of Contents

1. [Project Summary](#1-project-summary)
2. [Technology Stack](#2-technology-stack)
3. [System Architecture](#3-system-architecture)
4. [Role Hierarchy & Multi-Tenancy](#4-role-hierarchy--multi-tenancy)
5. [Core Modules](#5-core-modules)
6. [Database Design](#6-database-design)
7. [Authentication & Security](#7-authentication--security)
8. [Network Integration](#8-network-integration)
9. [Billing & Payments](#9-billing--payments)
10. [API Surface](#10-api-surface)
11. [Background Processing](#11-background-processing)
12. [Frontend & Views](#12-frontend--views)
13. [Testing Infrastructure](#13-testing-infrastructure)
14. [DevOps & Deployment](#14-devops--deployment)
15. [Known Technical Debt](#15-known-technical-debt)
16. [Key Documentation Map](#16-key-documentation-map)

---

## 1. Project Summary

**ISP Solution** is a comprehensive, multi-tenant Internet Service Provider management platform built on Laravel 12. It manages the complete lifecycle of ISP operations: customer onboarding, network provisioning, RADIUS authentication, billing, payments, device monitoring, and reporting.

### At a Glance

| Attribute | Value |
|-----------|-------|
| Framework | Laravel 12 (PHP 8.2+) |
| Authentication | FreeRADIUS (AAA) + Laravel Sanctum (API) |
| Network | MikroTik RouterOS API |
| OLT/ONU | SNMP + API-based management |
| Databases | MySQL 8.0 (App) + MySQL 8.0 (RADIUS) |
| Cache/Queue | Redis |
| Frontend | Blade + Tailwind CSS 4.x + Vite 7 |
| Tenancy | Domain/subdomain-based multi-tenancy |
| Role Levels | 9 levels (Developer → Staff) + Subscribers |
| Migrations | 172 database migrations |
| Services | 75+ service classes |
| Controllers | 90+ panel controllers, 15+ API controllers |
| Test Files | 60+ feature tests, 20+ unit tests |

---

## 2. Technology Stack

### Backend

| Component | Technology | Notes |
|-----------|-----------|-------|
| Framework | Laravel 12 | PHP 8.2+ required |
| ORM | Eloquent | ~100 models |
| Auth (Web) | Session + CSRF | Role-based middleware |
| Auth (API) | Laravel Sanctum | Bearer tokens |
| Auth (Network) | FreeRADIUS | AAA for PPPoE/Hotspot |
| Queues | Laravel Queues + Redis | 17+ job classes |
| PDF | barryvdh/laravel-dompdf | Invoices, reports |
| Excel | maatwebsite/excel | Export/import |
| 2FA | pragmarx/google2fa-laravel | TOTP-based |
| WebAuthn | webauthn_credentials | Passwordless login |
| Permissions | spatie/laravel-permission | Role/permission system |
| MikroTik API | evilfreelancer/routeros-api-php | RouterOS API wrapper |
| SSH/SFTP | phpseclib/phpseclib | Secure router backup |
| Static Analysis | PHPStan + Larastan | Code quality |
| Code Style | Laravel Pint | PSR-12 enforcement |

### Frontend

| Component | Technology |
|-----------|-----------|
| CSS Framework | Tailwind CSS 4.x |
| Build Tool | Vite 7 |
| UI Theme | Metronic (Tailwind variant) |
| JS Framework | Alpine.js 3.15+ |
| Charts | ApexCharts |
| HTTP Client | Axios 1.12+ |

### Infrastructure

| Component | Technology |
|-----------|-----------|
| Web Server | Nginx |
| App Server | PHP-FPM |
| Database (App) | MySQL 8.0 |
| Database (RADIUS) | MySQL 8.0 (separate) |
| Cache | Redis |
| Email Testing | Mailpit |
| Containerization | Docker + Docker Compose |
| Task Automation | Makefile |

---

## 3. System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Layer                            │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────────────┐    │
│  │  Web Browser │  │ Mobile Apps  │  │  MikroTik Router   │    │
│  └─────────────┘  └──────────────┘  └────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Web/Reverse Proxy Layer                    │
│              Nginx (port 8000 in Docker, 80/443 in prod)        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Laravel Layer                           │
│  ┌──────────────┐  ┌───────────────┐  ┌──────────────────┐    │
│  │  Controllers  │  │  Middleware   │  │  API Routes      │    │
│  │  (Panel/API)  │  │  (Auth/Roles) │  │  (Sanctum)       │    │
│  └──────────────┘  └───────────────┘  └──────────────────┘    │
│  ┌──────────────┐  ┌───────────────┐  ┌──────────────────┐    │
│  │  Services     │  │  Jobs/Queues  │  │  Observers       │    │
│  │  (75+ classes)│  │  (Redis)      │  │  Events/Listeners│    │
│  └──────────────┘  └───────────────┘  └──────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                              │
               ┌──────────────┴──────────────┐
               ▼                             ▼
┌──────────────────────────┐   ┌─────────────────────────────┐
│     Data Layer           │   │    External Services         │
│  MySQL (App DB)          │   │  MikroTik RouterOS API       │
│  MySQL (RADIUS DB)       │   │  FreeRADIUS (AAA)            │
│  Redis (Cache/Queues)    │   │  SMS Gateway                 │
└──────────────────────────┘   │  Payment Gateway (bKash etc) │
                               │  SNMP (OLT/ONU)              │
                               │  Telegram Bot                │
                               │  WhatsApp                    │
                               └─────────────────────────────┘
```

### Request Lifecycle

1. **Nginx** receives HTTP request, forwards to PHP-FPM.
2. **ResolveTenant middleware** resolves tenant from domain/subdomain.
3. **Auth middleware** verifies session (web) or Sanctum token (API).
4. **Role middleware** (`CheckRole`, `CheckPermission`) authorizes access by operator level.
5. **Controller** delegates business logic to a **Service** class.
6. **Service** interacts with **Models** (Eloquent) and external services (MikroTik, RADIUS, SMS).
7. **Response** rendered as Blade view or JSON.

---

## 4. Role Hierarchy & Multi-Tenancy

### Role Levels

| Level | Role | Scope |
|-------|------|-------|
| 0 | Developer | System-wide, bypasses all checks |
| 10 | Super Admin | Manages multiple tenants (B2B2B) |
| 20 | Admin | ISP-level operations (tenant owner) |
| 30 | Operator | Area/branch management |
| 40 | Sub-Operator | Local customer management |
| 50 | Manager | Oversight, reporting |
| 70 | Accountant | Financial operations |
| 80 | Staff | Customer support |
| — | Subscriber/Customer | `is_subscriber = true`, `operator_level = null` |

> **Key Change (v3.x):** Customers are no longer at `operator_level = 100`. They now use `is_subscriber = true` and are decoupled from the admin hierarchy.

### Multi-Tenancy Architecture

- **Tenant resolution**: Domain (`example.com`) or subdomain (`example.ispsolution.net`)
- **Data isolation**: All models use `BelongsToTenant` trait → global scope adds `WHERE tenant_id = ?`
- **Tenant context**: Managed by `TenancyService` (singleton, cached)
- **Middleware**: `ResolveTenant` runs on every web request
- **Subscription enforcement**: `CheckSubscription` middleware ensures Super Admins have active subscriptions

### Tenant Data Isolation

Every resource table includes `tenant_id`. All critical migrations also carry `operator_id` for sub-tenancy isolation. The `BelongsToTenant` trait:
- Auto-sets `tenant_id` on `create()`
- Adds global query scope to filter by current tenant
- Provides `forTenant($id)` and `allTenants()` escape hatches

---

## 5. Core Modules

### 5.1 Customer Management

**Key files:**
- `app/Models/Customer.php` — Extends `User`, scoped to `is_subscriber = true`
- `app/Http/Controllers/Panel/CustomerController.php`
- `app/Services/CustomerCreationService.php`
- `app/Services/CustomerCacheService.php`
- `app/Services/CustomerActivityService.php`

**Features:**
- Create/edit/suspend/delete customers (PPPoE, Hotspot, Static IP)
- MAC address binding
- Custom fields and attributes
- Bulk operations (suspend, reactivate, delete)
- Customer import (CSV) via `CustomerImportController`
- Customer wizard for quick onboarding
- Package change requests with approval workflow
- Customer volume/speed/time limits (FUP)

### 5.2 Network Management

**Key files:**
- `app/Models/MikrotikRouter.php` — Router configurations
- `app/Models/Nas.php` — RADIUS NAS entries
- `app/Models/IpPool.php`, `IpSubnet.php`, `IpAllocation.php` — IPAM
- `app/Services/MikrotikService.php` — Primary MikroTik integration
- `app/Services/MikrotikApiService.php` — Unified API interface
- `app/Services/RouterConfigurationService.php` — Router setup automation
- `app/Services/IpamService.php` — IP Address Management
- `app/Http/Controllers/Panel/RouterConfigurationController.php`

**Features:**
- Automatic MikroTik router configuration (RADIUS, firewall, PPPoE, Hotspot)
- PPP secret provisioning and sync
- IP pool management (IPAM)
- Router health checks and failover
- Router backup and restore
- NAS netwatch (connectivity monitoring)
- SNMP monitoring

### 5.3 OLT/ONU Management

**Key files:**
- `app/Models/Olt.php`, `app/Models/Onu.php`
- `app/Services/OltService.php`, `app/Services/OltSnmpService.php`
- `app/Http/Controllers/Api/V1/OltController.php`
- `app/Http/Controllers/Panel/OnuController.php`

**Features:**
- OLT discovery and configuration
- ONU registration and sync (vendor-specific parsing)
- SNMP trap reception (`SnmpTrapReceiverController`)
- OLT backup and firmware management
- Performance metrics and alerting

### 5.4 Billing & Invoicing

**Key files:**
- `app/Services/BillingService.php` — Core invoice generation
- `app/Services/SubscriptionBillingService.php` — Platform subscriptions
- `app/Services/CableTvBillingService.php` — Cable TV billing
- `app/Services/StaticIpBillingService.php` — Static IP billing
- `app/Services/DailyRechargeService.php` — Prepaid daily billing
- `app/Models/Invoice.php`, `app/Models/Payment.php`
- `app/Models/BillingProfile.php`

**Billing Models:**
| Type | Cycle | Method |
|------|-------|--------|
| PPPoE Daily | Flexible (7–15 days) | Manual recharge |
| PPPoE Monthly | Fixed (1st of month) | Auto-generated |
| Hotspot | Session-based | Prepaid recharge cards |
| Static IP | Monthly | Auto-generated |
| Cable TV | Monthly | Subscription-based |

**Features:**
- Auto-generate invoices (daily/monthly via Artisan commands)
- PDF/Excel invoice export
- Multi-currency support with VAT/GST
- Commission tracking across reseller hierarchy
- Advance payments and wallet management
- Recharge card distribution

### 5.5 Payments

**Key files:**
- `app/Services/PaymentGatewayService.php`
- `app/Services/BkashTokenizationService.php`
- `app/Services/WalletService.php`
- `app/Http/Controllers/PaymentController.php`

**Payment Methods:**
- Cash (manual recording)
- bKash tokenization (one-click, saved method)
- Online payment gateways (configurable)
- Auto-debit (`ProcessAutoDebitJob`)
- SMS payment credit purchase

### 5.6 RADIUS Integration

**Key files:**
- `app/Services/RadiusService.php` — Core RADIUS operations
- `app/Services/RadiusSyncService.php` — Sync app users → RADIUS DB
- `app/Models/RadCheck.php`, `RadReply.php`, `RadAcct.php`
- `database/migrations/radius/` — RADIUS DB migrations

**RADIUS Tables:**
| Table | Purpose |
|-------|---------|
| `radcheck` | User credentials (passwords, MAC) |
| `radreply` | User attributes (IP, rate limits) |
| `radacct` | Accounting/session records |
| `radgroupcheck` | Group authentication |
| `radgroupreply` | Group bandwidth profiles |
| `nas` | Network Access Servers (routers) |

**Authentication Flows:**
- **PPPoE**: Username/password in `radcheck` → RADIUS assigns IP from pool → CoA for rate limiting
- **Hotspot**: MAC-as-username → self-signup via OTP → session tracking

### 5.7 VPN Management

**Key files:**
- `app/Services/VpnService.php`
- `app/Services/VpnProvisioningService.php`
- `app/Services/VpnManagementService.php`
- `app/Models/MikrotikVpnAccount.php`, `VpnPool.php`
- `app/Http/Controllers/Panel/VpnController.php`

### 5.8 Notifications

**Key files:**
- `app/Services/NotificationService.php`
- `app/Services/SmsService.php`
- `app/Services/TelegramBotService.php`
- `app/Services/WhatsAppService.php`
- `app/Services/SmsBalanceService.php`

**Notification Channels:**
- SMS (multiple gateway support, balance management)
- Email (Laravel Mail + Mailpit for testing)
- Telegram Bot
- WhatsApp

**Notification Events:**
- Invoice generated
- Payment received
- Account suspended/activated
- Pre-expiration reminders
- Overdue alerts
- Subscription reminders

### 5.9 Analytics & Reporting

**Key files:**
- `app/Services/AdvancedAnalyticsService.php`
- `app/Services/FinancialReportService.php`
- `app/Services/GeneralLedgerService.php`
- `app/Services/ReconciliationService.php`
- `app/Services/RrdGraphService.php`
- `app/Http/Controllers/Panel/AnalyticsDashboardController.php`

**Reports:**
- Yearly financial summaries
- Commission reconciliation
- General ledger / double-entry accounting
- Bandwidth usage graphs (RRD)
- Customer growth analytics
- Reseller performance

### 5.10 Onboarding

**Key files:**
- `app/Http/Controllers/Panel/MinimumConfigurationController.php`
- `resources/views/panel/onboarding/`

**Onboarding Steps (Admin):**
1. Welcome / Exam (optional)
2. Create billing profile
3. Register router/NAS
4. Add/import customers
5. Assign billing profile to self
6. Assign billing profiles to resellers
7. Create packages from master packages
8. Set package pricing
9. Configure backup settings
10. Complete operator profile

---

## 6. Database Design

### Application Database

**User & Auth:**
| Table | Purpose |
|-------|---------|
| `users` | All system users + customers (`is_subscriber=true`) |
| `roles` | Role definitions |
| `operator_permissions` | Special permission grants |
| `webauthn_credentials` | Passwordless auth |
| `personal_access_tokens` | Sanctum API tokens |
| `otp` | OTP records |
| `audit_logs` | Activity audit trail |

**Customer & Network:**
| Table | Purpose |
|-------|---------|
| `customers` (← `network_users`) | Network credentials (PPPoE/Hotspot) |
| `customer_sessions` (← `network_user_sessions`) | Session tracking |
| `hotspot_users` | Hotspot user records |
| `hotspot_login_logs` | Login history |
| `customer_speed_limits` | FUP speed configs |
| `customer_volume_limits` | Volume-based FUP |
| `customer_time_limits` | Time-based limits |
| `customer_imports` | Bulk import jobs |
| `customer_mac_addresses` | MAC bindings |

**Network Infrastructure:**
| Table | Purpose |
|-------|---------|
| `mikrotik_routers` | Router configs |
| `nas` | RADIUS NAS entries |
| `mikrotik_ip_pools` | MikroTik IP pools |
| `mikrotik_profiles` | RouterOS profiles |
| `mikrotik_queues` | Queue configs |
| `mikrotik_ppp_secrets` | PPP secret cache |
| `mikrotik_pppoe_users` | PPPoE user cache |
| `mikrotik_vpn_accounts` | VPN accounts |
| `ip_pools` | IPAM pools |
| `ip_subnets` | IPAM subnets |
| `ip_allocations` | IP assignments |
| `ip_allocation_histories` | Allocation history |
| `network_devices` | Generic devices |
| `device_monitors` | Device health metrics |
| `bandwidth_usages` | Bandwidth time-series |
| `router_configurations` | Config snapshots |

**OLT/ONU:**
| Table | Purpose |
|-------|---------|
| `olts` | OLT devices |
| `onus` | ONU devices |
| `olt_backups` | OLT config backups |
| `olt_configuration_templates` | Config templates |
| `olt_snmp_traps` | SNMP trap logs |
| `olt_firmware_updates` | Firmware tracking |
| `olt_performance_metrics` | Performance data |

**Billing & Finance:**
| Table | Purpose |
|-------|---------|
| `packages` | Internet packages |
| `billing_profiles` | Billing cycle configs |
| `invoices` | Generated invoices |
| `payments` | Payment records |
| `commissions` | Commission entries |
| `recharge_cards` | Prepaid cards |
| `payment_gateways` | Gateway configs |
| `wallets` (via `wallet_transactions`) | Customer wallets |
| `general_ledger_entries` | Double-entry accounting |
| `expenses`, `expense_categories` | Expense tracking |
| `vat_profiles`, `vat_collections` | Tax management |

**B2B2B / Subscriptions:**
| Table | Purpose |
|-------|---------|
| `tenants` | Tenant registry |
| `subscription_plans` | Platform subscription tiers |
| `subscriptions` | Active subscriptions |
| `subscription_bills` | Platform billing |
| `subscription_payments` | Platform payments |
| `operator_subscriptions` | Operator platform subscriptions |
| `operator_costs` | Cost tracking per operator |
| `operator_package_rates` | Custom package pricing |

### RADIUS Database (`database/migrations/radius/`)

| Table | Purpose |
|-------|---------|
| `radcheck` | User credentials/constraints |
| `radreply` | Reply attributes (IP, bandwidth) |
| `radacct` | Accounting records |
| `radgroupcheck` | Group check attributes |
| `radgroupreply` | Group reply attributes |
| `radusergroup` | User-group mappings |
| `nas` | Network Access Servers |
| `radpostauth` | Post-auth logging |

---

## 7. Authentication & Security

### Web Authentication
- Session-based with CSRF protection
- Laravel's built-in `Auth` facade
- `CheckRole` middleware enforces operator level
- `CheckPermission` middleware for special permissions
- `TwoFactorAuthentication` middleware for 2FA enforcement
- `EnsureHotspotAuth` for hotspot portal sessions

### API Authentication
- Laravel Sanctum bearer tokens
- `ValidateDistributorApiKey` middleware for distributor APIs
- Rate limiting: 60 req/min (standard), custom for webhooks

### Network Authentication
- FreeRADIUS handles PPPoE/Hotspot AAA
- Shared secrets between MikroTik and RADIUS (`nas.secret`)
- MAC binding for additional security
- CoA (Change of Authorization) for live session updates

### Additional Security
- WebAuthn/passkey support (`WebAuthnCredential`)
- OTP (TOTP) for 2FA via Google Authenticator
- Audit logging (`AuditLog` model, `AuditLogService`)
- Soft deletes on critical models
- Policy classes for fine-grained authorization
- HTTPS enforced in production

---

## 8. Network Integration

### MikroTik RouterOS API

**Service layer:**
- `MikrotikService` — Primary interface (35+ methods)
- `MikrotikApiService` — Unified modern API client
- `RouterosAPI` — Legacy IspBills-pattern wrapper (present but to be removed)
- `RouterOSBinaryApiService` — Binary protocol implementation
- `MikrotikAutoProvisioningService` — Zero-touch provisioning

**Automated configuration** (`RouterConfigurationService`):
- RADIUS server settings (PPPoE + Hotspot)
- Firewall NAT rules
- Walled garden entries
- Hotspot server/profile/user-profile settings
- PPPoE server settings
- PPP AAA + duplicate session handling script
- Suspended users IP pool
- RADIUS incoming (CoA)
- SNMP community setup
- Firewall rules for suspended pool

**Scheduled tasks:**
- `MikrotikSyncSessions` — Sync online sessions
- `MikrotikSyncAll` — Full sync
- `SyncMikrotikSessionJob` — Queue-based session sync
- `MirrorUsersJob` — Mirror users to router

### SNMP Integration
- `PhpSnmpClient` — Pure PHP SNMP client
- `OltSnmpService` — OLT-specific SNMP operations
- `MonitoringService` — Device health monitoring
- SNMP trap receiver endpoint (`/api/v1/snmp/trap`)
- RRD graph generation for bandwidth charts

---

## 9. Billing & Payments

### Billing Profiles

Operators configure `BillingProfile` records that define:
- **Type**: Daily or Monthly
- **Billing day**: Day of month for monthly billing
- **Grace period**: Days after due date before suspension
- **Auto-suspend**: Whether to auto-suspend on overdue

### Invoice Generation

Artisan commands run via cron:
```
GenerateDailyInvoices      → daily recharge customers
GenerateMonthlyInvoices    → monthly PPPoE/Static IP customers
GenerateStaticIpInvoices   → static IP billing
GenerateSubscriptionBills  → platform subscription billing
GenerateOperatorSubscriptionBills → operator platform billing
```

### Commission System

- Hierarchical: Admin → Operator → Sub-Operator → Staff
- `CommissionService` calculates splits per payment
- `PayPendingCommissions` Artisan command for batch processing
- `OperatorPackageRate` stores custom pricing per operator level

### Package Hierarchy

- **Master Package** (Admin-level template)
- **Package** (Operator-level, derived from master)
- **Custom Price** (Override per customer)
- `PackageHierarchyService` resolves effective price
- `PackageSpeedService` manages bandwidth attributes

---

## 10. API Surface

### REST API (`routes/api.php`)

| Group | Base Path | Controller | Purpose |
|-------|-----------|-----------|---------|
| Data | `/api/data/*` | `DataController` | Users, customers, packages |
| Charts | `/api/chart/*` | `ChartController`, `GraphController` | Dashboard charts |
| IPAM | `/api/v1/ipam/*` | `IpamController` | IP management |
| RADIUS | `/api/v1/radius/*` | `RadiusController` | RADIUS operations |
| MikroTik | `/api/v1/mikrotik/*` | `MikrotikController` | Router operations |
| Network Users | `/api/v1/network-users/*` | `NetworkUserController` | Customer API (backward compat) |
| OLT | `/api/v1/olt/*` | `OltController` | OLT management |
| Monitoring | `/api/v1/monitoring/*` | `MonitoringController` | Device monitoring |
| Card Dist. | `/api/v1/distributor/*` | `CardDistributorController` | Card distribution |
| SNMP Trap | `/api/v1/snmp/trap` | `SnmpTrapReceiverController` | SNMP trap ingestion |
| Widgets | `/api/widgets/*` | `WidgetController` | Dashboard widgets |
| Validation | `/api/validate/*` | `ValidationController` | Field validation |
| Webhooks | `/webhooks/payment/*` | `PaymentController` | Payment callbacks |

### API Authentication
All API routes (except webhooks) require `Authorization: Bearer {token}` header via Sanctum.

---

## 11. Background Processing

### Artisan Commands (40+)

**Billing:**
- `GenerateDailyInvoices` — Daily billing cycle
- `GenerateMonthlyInvoices` — Monthly billing cycle
- `GenerateStaticIpInvoices` — Static IP billing
- `GenerateSubscriptionBills` — Subscription billing
- `ProcessAutoDebitPayments` — Auto-debit execution
- `PayPendingCommissions` — Commission distribution

**Network:**
- `MikrotikConfigure` — Configure a router
- `MikrotikSyncAll` — Full MikroTik sync
- `MikrotikSyncSessions` — Session sync
- `MikrotikImportPools` — Import IP pools
- `MikrotikImportSecrets` — Import PPP secrets
- `MikrotikImportProfiles` — Import profiles
- `MikrotikHealthCheck` — Health check all routers
- `RouterFailoverCommand` — Trigger failover
- `RouterBackupCommand` — Backup configs
- `RouterMirrorUsersCommand` — Mirror users
- `MigrateRouterToRadiusCommand` — Router migration
- `RadiusSyncUser` / `RadiusSyncUsers` — RADIUS sync
- `RadiusInstall` — Install RADIUS schema
- `OltSyncOnus` — Sync ONUs
- `OltHealthCheck` — OLT health check
- `OltBackup` — OLT configuration backup
- `OltSnmpTest` — SNMP test

**Customer/Account:**
- `LockExpiredAccounts` — Lock overdue accounts
- `SuspendOverdueSubscriptions` — Suspend expired subs
- `DeactivateExpiredHotspotUsers` — Hotspot cleanup
- `CleanupExpiredTempCustomers` — Temp customer cleanup
- `SendPreExpirationNotifications` — Expiry warnings
- `SendOverdueNotifications` — Overdue alerts
- `SendSubscriptionRemindersCommand` — Subscription reminders
- `SendLeadFollowUpReminders` — CRM lead follow-up

**System:**
- `CacheWarmCommand` — Pre-warm caches
- `MonitoringCollect` / `MonitoringAggregateHourly` / `MonitoringAggregateDaily` / `MonitoringCleanup`
- `IpamCleanup` — Clean stale IP allocations
- `CheckSmsBalanceCommand` — SMS balance alerts
- `CheckVpnPoolCapacity` — VPN pool capacity alerts
- `MigrateNetworkUsers` — Data migration command

### Queue Jobs (17)

| Job | Purpose | Timeout |
|-----|---------|---------|
| `ImportIpPoolsJob` | Import IP pools | 600s |
| `ImportPppSecretsJob` | Import PPP secrets | 600s |
| `ImportPppCustomersJob` | Import PPP customers | 1800s |
| `MirrorUsersJob` | Mirror users to router | — |
| `ProvisionUserJob` | Provision user to RADIUS | — |
| `SyncMikrotikSessionJob` | Sync router sessions | — |
| `SyncOnusJob` | Sync ONUs from OLT | — |
| `BackupRouterJob` | Router config backup | — |
| `CheckRouterHealth` | Router health check | — |
| `CollectBandwidthDataJob` | Bandwidth collection | — |
| `GenerateBillingReportJob` | Billing report gen | — |
| `ProcessAutoDebitJob` | Auto-debit payment | — |
| `ProcessPaymentJob` | Payment processing | — |
| `ReAllocateIPv4ForProfileJob` | IP reallocation | — |
| `PPPoEProfilesIpAllocationModeChangeJob` | Profile IP mode | — |
| `SendBulkSmsJob` | Bulk SMS dispatch | — |
| `SendInvoiceEmailJob` | Invoice email | — |

---

## 12. Frontend & Views

### View Structure

```
resources/views/
├── auth/                    # Login, 2FA, password reset
├── components/              # Reusable Blade components
├── emails/                  # Email templates
├── errors/                  # HTTP error pages
├── exports/                 # Excel export templates
├── hotspot-login/           # Hotspot login portal
├── hotspot-signup/          # Hotspot self-signup portal
├── layouts/                 # Base layouts (app, panel, public)
├── pages/                   # Public-facing pages
├── panel/                   # Admin panel views (legacy grouping)
│   ├── backup-settings/
│   ├── customers/
│   ├── expenses/
│   ├── onboarding/
│   ├── sms/
│   └── vat/
├── panels/                  # Canonical panel views by role
│   ├── admin/               # Admin panel
│   ├── card-distributor/    # Card distributor panel
│   ├── customer/            # Customer self-service
│   ├── developer/           # Developer panel
│   ├── manager/             # Manager panel
│   ├── operator/            # Operator panel
│   ├── payment-methods/     # Payment method management
│   ├── sales-manager/       # Sales manager panel
│   ├── search/              # Search results
│   ├── shared/              # Shared cross-role views
│   ├── staff/               # Staff panel
│   ├── sub-operator/        # Sub-operator panel
│   └── super-admin/         # Super admin panel
├── partials/                # Blade partials
└── pdf/                     # PDF templates (invoices, reports)
```

> **Note:** Both `panel/` and `panels/` directories exist. New development should target `panels/` (canonical). The `panel/` directory contains older views for specific feature modules.

### UI Components
- Action dropdowns
- Paginated data tables
- Role-based navigation menus
- Responsive dashboard widgets
- Chart embeds (ApexCharts)
- Modal dialogs (Alpine.js)
- Inline form validation

---

## 13. Testing Infrastructure

### Test Structure

```
tests/
├── Feature/
│   ├── Panel/              # Panel controller tests
│   ├── Middleware/         # Middleware tests
│   ├── Migrations/         # Migration tests
│   ├── Security/           # Security-focused tests
│   ├── Validation/         # Validation tests
│   └── [60+ test files]
├── Integration/            # Full integration tests
├── Performance/            # Performance tests
├── Unit/
│   ├── Console/            # Artisan command tests
│   ├── Controllers/        # Unit-level controller tests
│   ├── Helpers/            # Helper function tests
│   ├── Migrations/         # Migration unit tests
│   ├── Models/             # Model unit tests
│   ├── Seeders/            # Seeder tests
│   └── [20+ test files]
└── mock-servers/           # Mock server configurations
```

### Running Tests

```bash
# Full suite
php artisan test

# Specific test
php artisan test tests/Feature/BillingServiceTest.php

# By group
php artisan test --group=billing

# With Docker
make test
```

### Key Test Files

| File | Coverage Area |
|------|---------------|
| `BillingServiceTest.php` | Billing calculations |
| `RouterConfigurationControllerTest.php` | Router config |
| `TenantIsolationSecurityTest.php` | Data isolation |
| `RoleHierarchyTest.php` | Role enforcement |
| `RadiusErrorHandlingTest.php` | RADIUS error cases |
| `MikrotikConfigureEndpointTest.php` | MikroTik API |
| `AutoDebitTest.php` | Auto-debit flow |
| `SmsPaymentTest.php` | SMS payment flow |
| `OltSyncOnusTest.php` | OLT/ONU sync |
| `PolicyEnforcementTest.php` | Authorization policies |

---

## 14. DevOps & Deployment

### Docker Setup

```yaml
# Services in docker-compose.yml
app:          PHP 8.2 application container (runs `php artisan serve` on port 8000)
db:           MySQL 8.0 application database (port 3306)
radius-db:    MySQL 8.0 RADIUS database (port 3307)
redis:        Cache and queues (port 6379)
mock-mikrotik: Mock MikroTik server for testing (port 8728)
```

### Makefile Commands

```bash
make up           # Start all containers
make down         # Stop all containers
make setup        # Initial project setup (copy .env, install deps, generate key)
make install-deps # Install Composer + NPM dependencies
make migrate      # Run migrations
make seed         # Seed demo data
make test         # Run test suite
make build        # Build frontend assets
make logs         # Tail application logs
```

### Automated Installation (Production)

```bash
# Full automated installation on Ubuntu 18.04+
wget https://raw.githubusercontent.com/i4edubd/ispsolution/main/install.sh
chmod +x install.sh
sudo bash install.sh
```

Installs: PHP 8.2, MySQL 8.0, Redis, Nginx, FreeRADIUS, Node.js, Composer.

### Scheduled Tasks (Cron)

Add to crontab:
```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Key scheduled tasks:
- Every 5 min: Collect device metrics, sync sessions
- Hourly: Aggregate bandwidth data
- Daily 01:00: Generate daily invoices, lock expired accounts
- Daily 01:00: Aggregate hourly → daily bandwidth
- Daily 03:00: Clean old monitoring data (90-day retention)
- Monthly 1st: Generate monthly invoices, subscription bills
- Weekly: Router health checks, SNMP checks

### Environment Variables (Key)

| Variable | Purpose |
|----------|---------|
| `DB_*` | Application database connection |
| `RADIUS_DB_*` | RADIUS database connection |
| `REDIS_*` | Redis connection |
| `MIKROTIK_HOST` | Default MikroTik router |
| `RADIUS_SERVER_IP` | FreeRADIUS server IP |
| `SMS_GATEWAY_*` | SMS gateway credentials |
| `BKASH_*` | bKash payment credentials |
| `TELEGRAM_BOT_TOKEN` | Telegram notifications |

---

## 15. Known Technical Debt

### High Priority

| Issue | Location | Status |
|-------|----------|--------|
| Dual view directories (`panel/` and `panels/`) | `resources/views/` | Outstanding |
| `RouterosAPI.php` legacy wrapper | `app/Services/` | Should be removed |
| PDF service duplication (`PdfService` vs `PdfExportService`) | `app/Services/` | Needs consolidation |
| MikroTik service layer (4 overlapping services) | `app/Services/` | Needs simplification |
| VPN services (3 overlapping services) | `app/Services/` | Needs consolidation |

### Medium Priority

| Issue | Location | Status |
|-------|----------|--------|
| Cache services (4 separate services) | `app/Services/` | Consolidation recommended |
| RADIUS services (3 related services) | `app/Services/` | Delegation pattern needed |
| Import jobs (3 with similar patterns) | `app/Jobs/` | Base class recommended |
| User provisioning jobs (3 similar) | `app/Jobs/` | Clarify responsibilities |
| Authorization middleware overlap | `app/Http/Middleware/` | Consider unifying |

### Resolved (Reference)

| Issue | Resolution |
|-------|-----------|
| Duplicate Radius models (`app/Models/Radius/`) | Deleted — root models used |
| Duplicate `NasNetWatchController` | Consolidated into `NasNetwatchController` |
| Duplicate dashboard route names (13 collisions) | All routes renamed uniquely |
| Backup view file `.blade.php.bak` | Deleted |
| `network_users` table terminology | Migrated to `customers` + `is_subscriber` flag |
| axios security vulnerabilities | Updated to 1.12.0 |
| Deprecated `NotificationService` methods | Removed |
| Deprecated `AdminController` legacy methods | Removed |

---

## 16. Key Documentation Map

| Document | Location | Purpose |
|----------|----------|---------|
| Architecture (Mikrotik/RADIUS) | `1. Mikrotik_Radius_architecture.md` | System design, onboarding, router config |
| Project Overview (this doc) | `OVERVIEW.md` | Comprehensive codebase overview |
| Final TODO | `FINAL_TODO.md` | Prioritized task list |
| Developer Walkthrough | `WALKTHROUGH.md` | Step-by-step guide |
| Decommissioning Checklist | `TODO.md` | Migration/decommissioning tasks |
| Deprecation Tracking | `DEPRECATED.md` | What's deprecated and why |
| Network Users Migration | `DEPRECATING_NETWORK_USERS_MIGRATION_GUIDE.md` | `network_users` → `customers` |
| B2B2B Implementation | `docs/B2B2B_DOCUMENTATION_INDEX.md` | B2B2B multi-tenancy |
| API Reference | `docs/API.md` | REST API documentation |
| OLT Service Guide | `docs/OLT_SERVICE_GUIDE.md` | OLT management |
| Monitoring System | `docs/MONITORING_SYSTEM.md` | Device monitoring |
| Tenancy Guide | `docs/tenancy.md` | Multi-tenancy implementation |
| Duplicate Code Audit | `DUPLICATE_CODE_AUDIT_REPORT.md` | Code quality audit |
| Configuration Guide | `CONFIGURATION.md` | Environment/feature configuration |
| Contributing | `CONTRIBUTING.md` | Contribution guidelines |
| Changelog | `CHANGELOG.md` | Version history |

---

*This overview was generated from a deep investigation of the repository on 2026-03-24. For the most current status, refer to `CHANGELOG.md` and individual feature documentation.*
