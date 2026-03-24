# ISP Solution — Final TODO List

> **Generated:** 2026-03-24  
> **Purpose:** Consolidated, prioritized task list covering all outstanding work  
> **Legend:** 🔴 Critical · 🟠 High · 🟡 Medium · 🔵 Low · ✅ Done

---

## Table of Contents

1. [Critical / Blockers](#1-critical--blockers)
2. [Decommissioning & Migration](#2-decommissioning--migration)
3. [Implementation — Network & RADIUS](#3-implementation--network--radius)
4. [Implementation — Billing & Payments](#4-implementation--billing--payments)
5. [Implementation — Customer Management](#5-implementation--customer-management)
6. [Implementation — OLT/ONU](#6-implementation--oltonu)
7. [Code Quality & Refactoring](#7-code-quality--refactoring)
8. [Testing](#8-testing)
9. [Security](#9-security)
10. [DevOps & Infrastructure](#10-devops--infrastructure)
11. [Documentation](#11-documentation)
12. [Post-Migration Validation](#12-post-migration-validation)
13. [Completed Reference](#13-completed-reference)

---

## 1. Critical / Blockers

These items block production operation or cause correctness issues.

- [ ] 🔴 **[DECOMMISSION]** Stop FreeRADIUS service on legacy stack (`systemctl stop freeradius`) after new RADIUS is verified
- [ ] 🔴 **[DECOMMISSION]** Disable all legacy cron jobs: `sync:online_customers`, `rad:sql_relay_v2p`, `restart:freeradius`
- [ ] 🔴 **[DECOMMISSION]** Remove legacy router configs (PPPoE/Hotspot profiles, suspended pools on old stack)
- [ ] 🔴 **[DECOMMISSION]** Revoke legacy API credentials (`nas.php` → `api_username`, `api_password`) and old firewall rules
- [ ] 🔴 **[MIGRATION]** Migrate all NAS entries from legacy `nas.php` to new `Nas` model (ensure `tenant_id` + `operator_id`)
- [ ] 🔴 **[MIGRATION]** Complete customer data migration (verify `is_subscriber=true`, `operator_level=null` for all subscribers)
- [ ] 🔴 **[BLADE/VIEWS]** Confirm new Blade/Views integration is complete for all Panel controllers

---

## 2. Decommissioning & Migration

### 2.1 Preparation

- [ ] 🟠 Review `1. Mikrotik_Radius_architecture.md` — verify all controllers, models, and routes are accounted for
- [ ] 🟠 Notify stakeholders and schedule migration window
- [ ] ✅ Backup legacy DB tables (`radcheck`, `radreply`, `radacct`, `nas`)
- [ ] ✅ Archive configs (`resources/freeradius3x/radiusd.conf`, router secrets, firewall rules)

### 2.2 Data Migration (all must include `tenant_id` + `operator_id`)

- [ ] 🔴 Migrate **network** definitions: `routers`, `ipv4_pools`, `pppoe_profiles`
- [ ] 🔴 Migrate **packages** and `billing_profiles`
- [ ] 🔴 Migrate **NAS entries** from legacy `nas.php`
- [ ] 🟠 Migrate **OLT/ONU entries** (ensure `tenant_id` + `operator_id` on every row)
- [ ] 🟠 Migrate **MAC/IP bindings** (Hotspot + PPPoE)
- [ ] 🟠 Migrate **customers** (`all_customers`, `customer_change_logs`)
- [ ] 🟠 Migrate **IP pools** (`mikrotik_ip_pools`)
- [ ] 🟠 Migrate **PPP profiles** (`mikrotik_ppp_profiles`)
- [ ] 🟠 Migrate **prepaid cards** (`customer_payments`, recharge card tables)
- [ ] 🔵 Run `php artisan migrate:network-users` to finalize orphan migration from legacy `network_users`

### 2.3 B2B2B Phase Completion

- [ ] 🟠 **[Phase 4]** Remove `NetworkUser` shim model after 1–2 weeks monitoring without errors
- [ ] 🟠 **[Phase 4]** Drop `network_users` table (after confirming zero references via `grep -r "network_users" app/`)
- [ ] 🔵 Update all lingering `DB::table('network_users')` raw references to use `customers` table
- [ ] 🔵 Update all `operator_level = 100` checks to `is_subscriber = true`
- [ ] 🔵 Replace deprecated `Onu::networkUser()` calls with `Onu::customer()`
- [ ] 🔵 Replace deprecated `HotspotLoginLog::networkUser()` calls with `HotspotLoginLog::customer()`

---

## 3. Implementation — Network & RADIUS

### 3.1 Router Configuration

- [ ] 🟠 Verify `RouterConfigurationController.php` is deployed and all routes registered correctly
- [ ] 🟠 Test automated router configuration end-to-end (RADIUS, firewall, PPPoE, Hotspot, SNMP)
- [ ] 🟠 Confirm `RadreplyController.php` is functional for managing `radreply` entries
- [ ] 🟠 Test duplicate PPPoE session handling script (`/ppp profile on-up`) on new stack
- [ ] 🟡 Add input validation for router API credentials (test connectivity on save)
- [ ] 🟡 Add UI feedback when router configuration completes or fails
- [ ] 🔵 Move hardcoded IP ranges (suspended pool `100.65.96.0/20`) to config file

### 3.2 RADIUS Integration

- [ ] 🟠 Configure new RADIUS settings, firewall rules, and SNMP monitoring on all routers
- [ ] 🟠 Verify `radcheck`/`radreply` entries are created on customer provisioning
- [ ] 🟠 Test RADIUS CoA (Change of Authorization) for live rate limit updates
- [ ] 🟠 Confirm `RADIUS incoming` is enabled on all routers (`/radius incoming set accept=yes`)
- [ ] 🟡 Implement `RadiusService` quota enforcement (FUP volume limits)
- [ ] 🔵 Add RADIUS accounting verification (sessions in `radacct` match live router sessions)

### 3.3 IP Address Management (IPAM)

- [ ] 🟠 Verify IP pool assignments are working for PPPoE customers
- [ ] 🟡 Test `IpamCleanup` command removes stale allocations correctly
- [ ] 🟡 Add UI for IP subnet visualization
- [ ] 🔵 Add IPv6 pool support (schema exists, service layer incomplete)

### 3.4 VPN

- [ ] 🟡 Clarify responsibilities between `VpnService`, `VpnProvisioningService`, `VpnManagementService`
- [ ] 🟡 Consolidate duplicate `createVpnAccount()` methods using strategy pattern
- [ ] 🔵 Add `CheckVpnPoolCapacity` scheduled alert (capacity warning threshold configurable)

---

## 4. Implementation — Billing & Payments

### 4.1 Billing Cycles

- [ ] 🟠 Verify daily billing cycle (`GenerateDailyInvoices`) generates invoices correctly
- [ ] 🟠 Verify monthly billing cycle (`GenerateMonthlyInvoices`) runs on 1st of month
- [ ] 🟠 Test grace period enforcement and auto-suspend after grace period expires
- [ ] 🟡 Add billing preview UI (show what will be billed before generation)
- [ ] 🔵 Add billing report export (CSV/Excel) per billing period

### 4.2 Payments

- [ ] 🟠 Test bKash tokenization (one-click payment with saved method)
- [ ] 🟠 Verify auto-debit (`ProcessAutoDebitJob`) runs and processes pending payments
- [ ] 🟠 Test webhook handling for payment gateways (signature verification)
- [ ] 🟡 Add payment reconciliation report (`ReconciliationService`)
- [ ] 🟡 Test SMS payment credit purchase flow end-to-end
- [ ] 🔵 Add support for additional payment gateways (extend `PaymentGatewayService`)

### 4.3 Commission System

- [ ] 🟠 Verify commission calculation is correct across all hierarchy levels
- [ ] 🟡 Test `PayPendingCommissions` command processes all pending entries
- [ ] 🔵 Add commission statement PDF export

### 4.4 Cable TV Billing

- [ ] 🟡 Verify `CableTvBillingService` generates subscriptions correctly
- [ ] 🔵 Add Cable TV channel management UI

---

## 5. Implementation — Customer Management

### 5.1 Customer Operations

- [ ] 🟠 Test customer wizard (`CustomerWizardController`) end-to-end for PPPoE and Hotspot
- [ ] 🟠 Verify bulk operations (suspend, reactivate, delete) work correctly with new `is_subscriber` flag
- [ ] 🟡 Test customer CSV import (`CustomerImportController`) with validation
- [ ] 🟡 Add customer search by MAC address and IP address
- [ ] 🔵 Add customer activity timeline view

### 5.2 FUP (Fair Usage Policy)

- [ ] 🟠 Test volume limit enforcement via `CustomerVolumeLimit` + FreeRADIUS attributes
- [ ] 🟡 Test speed throttling after FUP trigger (`FupService`)
- [ ] 🟡 Test time-based limits (`CustomerTimeLimit`)
- [ ] 🔵 Add FUP usage dashboard widget

### 5.3 Hotspot

- [ ] 🟠 Test Hotspot self-signup flow: OTP → profile → payment → authentication
- [ ] 🟠 Verify `HotspotScenarioDetectionService` correctly identifies connection types
- [ ] 🟡 Test idle timeout and keepalive settings on Hotspot portal
- [ ] 🔵 Add Hotspot session dashboard (active users, bandwidth)

### 5.4 Onboarding

- [ ] 🟠 Test `MinimumConfigurationController` onboarding wizard end-to-end for Admin
- [ ] 🟡 Test onboarding wizard for Operator (reseller) setup
- [ ] 🟡 Add progress indicator for multi-step onboarding
- [ ] 🔵 Add onboarding skip option for experienced users

---

## 6. Implementation — OLT/ONU

- [ ] 🟠 Test OLT discovery and registration workflow
- [ ] 🟠 Verify ONU sync (`OltSyncOnus` command + `SyncOnusJob`) with vendor-specific parsing
- [ ] 🟠 Test SNMP trap reception and alert generation (`SnmpTrapReceiverController`)
- [ ] 🟡 Verify OLT backup (`OltBackup` command) stores configs correctly
- [ ] 🟡 Test OLT firmware update workflow
- [ ] 🟡 Test OLT performance metrics collection and graphing
- [ ] 🔵 Add automated OLT sync on schedule (currently manual as per `TODO.md`)
- [ ] 🔵 Test `OltSnmpTest` command for SNMP connectivity verification
- [ ] 🔵 Add multi-vendor OLT support testing (VSOL, others)

---

## 7. Code Quality & Refactoring

### 7.1 High Priority (1–2 sprints)

- [ ] 🟠 Remove `RouterosAPI.php` legacy wrapper — migrate all callers to `MikrotikApiService`
- [ ] 🟠 Consolidate PDF services: merge `PdfService` and `PdfExportService` into single `DocumentExportService`
- [ ] 🟠 Consolidate duplicate dashboard route names (verify all 13 renamed routes work correctly after fix)
- [ ] 🟡 Simplify RADIUS services: `RadiusService`, `RadiusSyncService` — establish clear delegation pattern
- [ ] 🟡 Fix view directory duplication: migrate remaining `panel/` views to canonical `panels/` structure

### 7.2 Medium Priority (2–3 sprints)

- [ ] 🟡 Consolidate MikroTik service layer (4 services: `MikrotikService`, `MikrotikApiService`, `RouterosAPI`, `RouterOSBinaryApiService`)
- [ ] 🟡 Consolidate VPN services (`VpnService`, `VpnProvisioningService`, `VpnManagementService`) using strategy pattern
- [ ] 🟡 Consolidate cache services (`CacheService`, `CustomerCacheService`, `BillingProfileCacheService`, `WidgetCacheService`)
- [ ] 🟡 Create base class or trait for import jobs (`ImportIpPoolsJob`, `ImportPppSecretsJob`, `ImportPppCustomersJob`)
- [ ] 🟡 Clarify user provisioning jobs: `ProvisionUserJob` vs `MirrorUsersJob` vs `SyncMikrotikSessionJob`
- [ ] 🟡 Consider merging `CheckPermission` and `CheckRole` middleware into unified `CheckAuthorization`

### 7.3 Low Priority (future sprints)

- [ ] 🔵 Implement strategy pattern for billing services (4 related services)
- [ ] 🔵 Consolidate router management services: `RouterManager`, `RouterMigrationService`, `RouterProvisioningService`
- [ ] 🔵 Consolidate health monitoring: `MonitoringService`, `RouterHealthCheckService`
- [ ] 🔵 Implement generic communication job pattern for `SendBulkSmsJob` and `SendInvoiceEmailJob`
- [ ] 🔵 Add pre-commit hooks to detect similar file names (prevent future duplicate controllers/models)
- [ ] 🔵 Set up quarterly dependency audit cadence (per `LEGACY_DEPRECATED_SUMMARY.md` recommendation)
- [ ] 🔵 Plan `User`/`NetworkUser` model unification for v4.0 major release

---

## 8. Testing

### 8.1 Authentication & RADIUS

- [ ] 🔴 Run PPPoE authentication tests against new RADIUS stack (`radcheck`, `radreply`)
- [ ] 🔴 Run Hotspot authentication tests against new RADIUS stack
- [ ] 🟠 Verify RADIUS session tracking (`radacct`) records correct start/stop/update
- [ ] 🟠 Test duplicate session handling (on-up script disconnects older session)

### 8.2 Billing

- [ ] 🟠 Verify daily billing generates invoices for all daily-cycle customers
- [ ] 🟠 Verify monthly billing generates invoices for all monthly-cycle customers
- [ ] 🟠 Test invoice PDF generation (formatting, tax calculation, correct totals)
- [ ] 🟡 Test commission calculation for multi-level hierarchy (Admin → Operator → Sub-operator)

### 8.3 Role-Based Access

- [ ] 🟠 Test all 9 role dashboards render correctly with correct data
- [ ] 🟠 Test that Operators cannot access Admin-only routes
- [ ] 🟠 Test that Subscribers cannot access any panel routes
- [ ] 🟡 Test special permissions grant/revoke works correctly

### 8.4 Scheduled Tasks

- [ ] 🟠 Validate `pull:radaccts` (or equivalent) runs and imports session data
- [ ] 🟠 Validate `delete:rad_stale_sessions` (or equivalent) cleans stale sessions
- [ ] 🟡 Test `MonitoringCollect` collects data for all device types
- [ ] 🟡 Test `SendPreExpirationNotifications` sends at correct intervals

### 8.5 Security

- [ ] 🟠 Run security checks: Laravel Policies, Sanctum tokens, HTTPS, CSRF
- [ ] 🟠 Verify tenant isolation: user A cannot access user B's data
- [ ] 🟡 Test rate limiting on API endpoints
- [ ] 🟡 Test webhook signature verification prevents replay attacks
- [ ] 🔵 Run PHPStan analysis: `vendor/bin/phpstan analyse` (target: 0 errors)
- [ ] 🔵 Run Laravel Pint: `vendor/bin/pint --test` (target: 0 violations)

### 8.6 OLT/ONU (Manual until automated)

- [ ] 🟠 Test OLT/ONU sync manually and verify ONU data is correctly parsed
- [ ] 🟡 Test OLT SNMP trap reception creates correct alert records

---

## 9. Security

- [ ] 🟠 Ensure all router API passwords are stored encrypted (not plaintext in DB)
- [ ] 🟠 Verify RADIUS shared secrets are at least 16 characters and unique per router
- [ ] 🟠 Confirm HTTPS is enforced in production (HSTS header, redirect HTTP → HTTPS)
- [ ] 🟠 Verify CSRF protection is active on all state-changing web routes
- [ ] 🟡 Add IP whitelisting option for router API access
- [ ] 🟡 Review `CommandExecutionController` for command injection risks
- [ ] 🟡 Audit `DeveloperController` endpoints — ensure dev routes are not accessible in production
- [ ] 🟡 Review webhook endpoints for SSRF attack surface
- [ ] 🔵 Schedule quarterly security audit (next: Q2 2026)
- [ ] 🔵 Monitor ApexCharts for v5 release breaking changes (per `LEGACY_DEPRECATED_SUMMARY.md`)

---

## 10. DevOps & Infrastructure

- [ ] 🟠 Set up production cron: `* * * * * php artisan schedule:run`
- [ ] 🟠 Set up Redis queue worker: `php artisan queue:work --daemon`
- [ ] 🟠 Configure production `.env` (DB credentials, RADIUS server IP, SMS/payment gateways)
- [ ] 🟠 Set correct file permissions: `storage/` and `bootstrap/cache/` writable by `www-data`
- [ ] 🟡 Configure log rotation for `storage/logs/laravel.log`
- [ ] 🟡 Set up application monitoring (Sentry or similar for error tracking)
- [ ] 🟡 Set up database backups (automated daily MySQL dumps)
- [ ] 🟡 Configure Redis persistence (AOF or RDB snapshots)
- [ ] 🔵 Set up staging environment mirroring production
- [ ] 🔵 Add Docker health checks for all services
- [ ] 🔵 Add CI badge for test coverage to README

---

## 11. Documentation

- [x] ✅ Update `README.md` to reference new `OVERVIEW.md` and `WALKTHROUGH.md`
- [ ] 🟡 Create/update `INSTALLATION.md` with complete production install steps
- [ ] 🟡 Create `POST_DEPLOYMENT_STEPS.md` with post-go-live checklist
- [ ] 🟡 Update `TODO.md` (decommissioning checklist) to mark completed items
- [ ] 🔵 Create role-specific user guides in `docs/guides/` (per README links)
- [ ] 🔵 Document all Artisan commands with usage examples
- [ ] 🔵 Add inline PHPDoc to all Service classes (most are undocumented)
- [ ] 🔵 Add API changelog tracking to `docs/API.md`
- [ ] 🔵 Create `OPERATIONS_RUNBOOK.md` for on-call engineers

---

## 12. Post-Migration Validation

> Run these checks after the production migration is complete.

- [ ] 🔴 Monitor live sessions (`radacct`) for accuracy — compare with router active sessions
- [ ] 🔴 Confirm all accounting logs are recording correctly (no dropped records)
- [ ] 🟠 Confirm SMS/email notifications trigger correctly for billing events
- [ ] 🟠 Audit firewall rules on all routers — verify suspended users are blocked
- [ ] 🟠 Audit router IP pools — verify suspended users are in the suspended pool
- [ ] 🟠 Generate and share migration report with stakeholders
- [ ] 🟡 Run `php artisan radius:sync-users` to verify all customers are in RADIUS DB
- [ ] 🟡 Verify OLT/ONU sync reflects current network state
- [ ] 🔵 Run load test on RADIUS server (simulate peak concurrent authentications)
- [ ] 🔵 Verify RRD bandwidth graphs are populating correctly
- [ ] 🔵 Confirm Telegram/WhatsApp bot notifications are working

---

## 13. Completed Reference

> These items have been completed. Listed for historical reference.

### Codebase Cleanup (Completed)
- ✅ Deleted duplicate Radius models: `app/Models/Radius/Radacct.php`, `Radcheck.php`, `Radreply.php`
- ✅ Consolidated `NasNetWatchController` into `NasNetwatchController`
- ✅ Fixed 13 duplicate `dashboard` route names
- ✅ Removed `.blade.php.bak` backup view file

### Migration (Completed)
- ✅ `network_users` table renamed to `customers`
- ✅ `network_user_sessions` renamed to `customer_sessions`
- ✅ `network_user_id` column renamed to `customer_id` in related tables
- ✅ `users.is_subscriber` column added, data migrated from `operator_level = 100`
- ✅ B2B2B fields added to `users` table (`subscription_plan_id`, `expires_at`)
- ✅ `CheckSubscription` middleware added for Super Admin subscription enforcement
- ✅ `MigrateNetworkUsers` Artisan command created for orphan migration

### Security (Completed)
- ✅ `axios` updated from 1.6.4 to 1.12.0 (3 CVEs fixed)
- ✅ `alpinejs` updated from 3.13.3 to 3.15.5
- ✅ Deprecated `NotificationService::sendInvoiceGenerated()` removed
- ✅ Deprecated `NotificationService::sendPaymentReceived()` removed
- ✅ Deprecated `AdminController::mikrotikRouters()` removed
- ✅ Deprecated `AdminController::oltDevices()` removed

### Documentation (Completed)
- ✅ `DUPLICATE_CODE_AUDIT_REPORT.md` created
- ✅ `DUPLICATE_CODE_REMEDIATION_SUMMARY.md` created
- ✅ `DEPRECATING_NETWORK_USERS_MIGRATION_GUIDE.md` created
- ✅ `LEGACY_DEPRECATED_SUMMARY.md` created
- ✅ `docs/B2B2B_DOCUMENTATION_INDEX.md` created
- ✅ `docs/MONITORING_SYSTEM.md` created
- ✅ `docs/tenancy.md` created
- ✅ `OVERVIEW.md` created (this session)
- ✅ `FINAL_TODO.md` created (this session)
- ✅ `WALKTHROUGH.md` created (this session)

---

*Last updated: 2026-03-24. For the active decommissioning checklist, see `TODO.md`.*
