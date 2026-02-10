# Duplicate Code Audit Report

Generated: 2026-01-31

## Executive Summary

This comprehensive audit identified duplicate and repeated code across the ISP Solution codebase in the following categories:
- Controllers
- Models
- Services
- Views
- Routes
- Jobs
- Middleware
- Helpers

## Critical Duplicates Requiring Immediate Action

### 1. Controllers

#### 🔴 **NasNetWatchController vs NasNetwatchController** (CRITICAL)
- **Files:**
  - `app/Http/Controllers/Panel/NasNetWatchController.php` (202 lines)
  - `app/Http/Controllers/Panel/NasNetwatchController.php` (452 lines)
- **Issue:** Case-sensitive naming issue causing two separate files for the same functionality
- **Impact:** Both are used in routes (lines 651-654 and 696-698 of web.php), causing confusion
- **Recommendation:** Consolidate into `NasNetwatchController.php` (the more complete version with 452 lines and more methods including `index()` and `test()`)
- **Status:** ⚠️ **REQUIRES IMMEDIATE FIX**

#### ⚠️ **AnalyticsController vs AnalyticsDashboardController**
- **Files:**
  - `app/Http/Controllers/Panel/AnalyticsController.php`
  - `app/Http/Controllers/Panel/AnalyticsDashboardController.php`
- **Issue:** Similar names but different purposes (API vs Views)
- **Recommendation:** Acceptable separation but consider renaming for clarity

#### ℹ️ **CardDistributorController** (Multiple Locations)
- **Files:**
  - `app/Http/Controllers/Panel/CardDistributorController.php`
  - `app/Http/Controllers/Api/V1/CardDistributorController.php`
- **Status:** Different purposes (Panel UI vs API) - acceptable separation

### 2. Models

#### 🔴 **Radius Models - Three Duplicate Pairs** (CRITICAL)

**RadAcct Duplication:**
- **Root:** `app/Models/RadAcct.php` (More complete - includes IPv6 fields)
- **Subdirectory:** `app/Models/Radius/Radacct.php` (Less complete)
- **Usage:** Root model is used in 11 files, subdirectory model is unused
- **Recommendation:** **DELETE** `app/Models/Radius/Radacct.php`

**RadCheck Duplication:**
- **Root:** `app/Models/RadCheck.php`
- **Subdirectory:** `app/Models/Radius/Radcheck.php`
- **Difference:** Different timestamp settings (`false` vs `true`)
- **Recommendation:** **DELETE** `app/Models/Radius/Radcheck.php`

**RadReply Duplication:**
- **Root:** `app/Models/RadReply.php`
- **Subdirectory:** `app/Models/Radius/Radreply.php`
- **Difference:** Different timestamp settings (`false` vs `true`)
- **Recommendation:** **DELETE** `app/Models/Radius/Radreply.php`

**Action:** Delete entire `app/Models/Radius/` directory as root models are more complete and widely used.

### 3. Services

#### 🟡 **PDF Generation Services** (MEDIUM PRIORITY)
- `PdfService.php` - Generic invoice/bill/receipt generation
- `PdfExportService.php` - Similar functionality with more formatting options
- **Issue:** Both have `generateInvoicePdf()` methods
- **Recommendation:** Consolidate into single `DocumentExportService` with format strategies

#### 🟡 **MikroTik API Services** (MEDIUM PRIORITY)
- `MikrotikService.php` - Main MikroTik interface (35 methods)
- `MikrotikApiService.php` - Unified API interface (15 methods)
- `MikrotikImportService.php` - Import operations using both above
- `RouterosAPI.php` - Legacy wrapper (IspBills pattern)
- **Recommendation:** Remove `RouterosAPI` and unnecessary layering

#### 🟡 **VPN Management Services** (MEDIUM PRIORITY)
- `VpnService.php` - Basic VPN account creation (9 methods)
- `VpnProvisioningService.php` - Advanced provisioning (14 methods)
- `VpnManagementService.php` - Dashboard stats (8 methods)
- **Issue:** Both VpnService and VpnProvisioningService have `createVpnAccount()` methods
- **Recommendation:** Use strategy pattern to handle creation/provisioning differences

#### 🟡 **Cache Services** (MEDIUM PRIORITY)
- `CacheService.php` - Generic tenant cache management (23 methods)
- `CustomerCacheService.php` - Customer-specific caching (7 methods)
- `BillingProfileCacheService.php` - Billing profile cache (5 methods)
- `WidgetCacheService.php` - Widget cache (8 methods)
- **Recommendation:** Consolidate into `CacheService` with specialized caching strategies

#### 🟡 **Billing Services** (MEDIUM PRIORITY)
- `BillingService.php` - Core invoice generation
- `SubscriptionBillingService.php` - Subscription-specific bills
- `CableTvBillingService.php` - CableTV-specific billing
- `StaticIpBillingService.php` - Static IP billing
- **Recommendation:** Use strategy pattern with pluggable billing type handlers

#### 🟡 **RADIUS Services** (MEDIUM PRIORITY)
- `RadiusService.php` - RADIUS user/account management (10 methods)
- `RadiusSyncService.php` - Sync wrapper (13 methods, mostly delegates)
- `RouterRadiusProvisioningService.php` - Router-specific RADIUS provisioning
- **Issue:** `RadiusSyncService` is a thin wrapper that delegates to `RadiusService`
- **Recommendation:** Consider consolidating or clarifying separation of concerns

#### 🟡 **Router Management Services** (MEDIUM PRIORITY)
- `RouterManager.php` - Facade/dispatcher
- `RouterProvisioningService.php` - Zero-touch provisioning
- `RouterConfigurationService.php` - Configuration management
- **Recommendation:** Clarify responsibilities and reduce overlapping concerns

### 4. Views

#### 🔴 **Backup View File** (CRITICAL - EASY FIX)
- **File:** `resources/views/components/action-dropdown.blade.php.bak`
- **Issue:** Identical backup copy of the original file
- **Recommendation:** **DELETE** immediately

#### ⚠️ **Directory Structure Confusion**
- `resources/views/panel/` - Contains general panel views
- `resources/views/panels/` - Contains role-based views
- **Recommendation:** Consider consolidating to avoid confusion

#### ℹ️ **Role-Specific Views** (NOT DUPLICATES)
Multiple dashboards exist for different roles - these are intentional, not duplicates:
- `panels/admin/analytics/dashboard.blade.php`
- `panels/customer/dashboard.blade.php`
- `panels/operator/dashboard.blade.php`
- etc.

### 5. Routes

#### 🔴 **Duplicate Dashboard Route Names** (CRITICAL)
- **File:** `routes/web.php`
- **Issue:** 13 routes all with path `/dashboard` and the same route name `dashboard`
- **Lines:** 76, 96, 139, 151, 156, 161, 164, 168, 171, 173, 176, 177, 1958 (approx)
- **Problem:** Route collision - Laravel uses the first matching route, making later ones unreachable
- **Recommendation:** Use unique route names:
  - `dashboard.super-admin`
  - `dashboard.admin`
  - `dashboard.sales-manager`
  - `dashboard.manager`
  - `dashboard.operator`
  - `dashboard.sub-operator`
  - `dashboard.accountant`
  - `dashboard.staff`
  - `dashboard.card-distributor`
  - `dashboard.customer`
  - `dashboard.developer`
  - `dashboard.analytics`

#### 🟡 **Overlapping NasNetwatch Routes**
- Both `NasNetwatchController` and `NasNetWatchController` are used in routes
- Lines 651-654 and 696-698 in web.php
- **Recommendation:** Consolidate to single controller

### 6. Jobs

#### 🟡 **Import-Related Jobs** (SIMILAR LOGIC)
- `ImportIpPoolsJob` (600s timeout, 1 try)
- `ImportPppSecretsJob` (600s timeout, 1 try)
- `ImportPppCustomersJob` (1800s timeout, 1 try)
- **Issue:** Similar patterns, potential overlap
- **Recommendation:** Review for consolidation opportunity

#### 🟡 **User Provisioning/Syncing Jobs**
- `ProvisionUserJob` - Provisions users to routers
- `MirrorUsersJob` - Syncs users to routers
- `SyncMikrotikSessionJob` - Syncs router sessions
- **Recommendation:** Clarify different purposes or consolidate

#### 🟡 **Communication Jobs**
- `SendBulkSmsJob` - Sends SMS
- `SendInvoiceEmailJob` - Sends emails
- **Recommendation:** Consider generic messaging job pattern

### 7. Middleware

#### 🟡 **Authorization Middleware Overlap**
- `CheckPermission.php` - Permission-based access control
- `CheckRole.php` - Role-based access control
- **Issue:** Both check authentication first (identical lines), similar abort logic
- **Recommendation:** Consider consolidating into unified `CheckAuthorization` middleware

#### ℹ️ **Multiple Authentication Layers** (BY DESIGN)
- `EnsureHotspotAuth.php` - Hotspot sessions
- `ValidateDistributorApiKey.php` - API key validation
- `TwoFactorAuthentication.php` - 2FA verification
- **Status:** Different purposes, acceptable separation

### 8. Helpers

#### ✅ **No Duplicates Found**
All helper files are well-organized with clear separation of concerns:
- `DateHelper.php` - Date/time formatting utilities
- `RouterCommentHelper.php` - Router metadata encoding/parsing
- `menu_helpers.php` - Menu and utility functions

The wrapper pattern in `menu_helpers.php` is intentional and not a duplicate.

## Priority Action Items

### Immediate (Critical - Must Fix)
1. ✅ Delete `app/Models/Radius/Radacct.php`
2. ✅ Delete `app/Models/Radius/Radcheck.php`
3. ✅ Delete `app/Models/Radius/Radreply.php`
4. ✅ Delete `resources/views/components/action-dropdown.blade.php.bak`
5. ✅ Consolidate `NasNetWatchController` and `NasNetwatchController`
6. ✅ Fix duplicate dashboard route names in `routes/web.php`

### Short-term (Within Sprint)
1. Review and consolidate PDF services
2. Simplify MikroTik service layer
3. Merge VPN services using strategy pattern
4. Consolidate cache services
5. Review authorization middleware for potential merge

### Long-term (Future Refactoring)
1. Billing services - implement strategy pattern
2. Import jobs - consolidate similar logic
3. Communication jobs - generic messaging pattern
4. Directory structure - consolidate `panel/` and `panels/`

## Testing Recommendations

After fixing duplicates:
1. Run all existing tests
2. Verify all routes are accessible
3. Check that RADIUS functionality still works
4. Validate NAS netwatch monitoring
5. Test cache operations
6. Verify PDF generation

## Maintenance Guidelines

To prevent future duplicates:
1. Use consistent naming conventions (case-sensitive awareness)
2. Check for existing implementations before creating new ones
3. Document service responsibilities clearly
4. Use abstract base classes or traits for shared logic
5. Regular code reviews focusing on duplication
6. Implement pre-commit hooks to detect similar file names

## Conclusion

The audit identified 3 critical duplicate issues and several areas for consolidation. The immediate priority is to:
1. Remove duplicate Radius models (3 files)
2. Remove backup view file (1 file)
3. Consolidate NasNetwatch controllers (2 files → 1)
4. Fix duplicate dashboard route names (13 routes)

These changes will improve code maintainability, reduce confusion, and prevent potential bugs from duplicate/conflicting implementations.
