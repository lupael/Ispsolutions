# IspBills Feature Mapping & Implementation Guide

**Project:** ISP Solution - Router + RADIUS (MikroTik) Integration  
**Reference:** IspBills ISP Billing System Study  
**Date:** 2026-01-30  
**Status:** Analysis & Planning Phase

---

## Overview

This document maps features from the IspBills system (as described in the issue) to the existing implementation in our ISP Solution platform. It identifies what's already implemented, what needs enhancement, and what's missing.

---

## Feature Comparison Matrix

| Feature | IspBills Pattern | Our Implementation | Status | Priority |
|---------|-----------------|-------------------|--------|----------|
| **1. Router/NAS Management** |
| Add Router (NAS) | `NasController@store` | ✅ `MikrotikRouter` model, CRUD routes | Complete | - |
| API Connectivity Test | `RouterosAPI::connect()` | ✅ `MikrotikApiService` | Complete | - |
| NAS Table for RADIUS | `nas` table with encrypted secret | ✅ `Nas` model with encryption | Complete | - |
| Router-NAS Relationship | `MikrotikRouter->nas_id` | ✅ Relationship exists | Complete | - |
| **2. Router Configuration** |
| Add RADIUS Client | `$api->addMktRows('radius', [...])` | ⚠️ Partial in `RouterConfigurationService` | Enhance | 🔴 High |
| Configure PPP AAA | `/ppp/aaa/set use-radius=yes` | ⚠️ Partial implementation | Enhance | 🔴 High |
| Enable RADIUS Incoming | `/radius/incoming/set accept=yes` | ⚠️ Not fully implemented | Add | 🟡 Medium |
| Update PPP Profiles | Edit profiles via API | ✅ `MikrotikApiService` | Complete | - |
| Set Local Address | Update profile local-address | ✅ Supported in API | Complete | - |
| **3. Import from Router** |
| Import IP Pools | `mikrotik_ip_pool` table | ✅ `MikrotikImportService::importIpPoolsFromRouter` | Complete | - |
| Import PPP Profiles | `mikrotik_ppp_profile` table | ✅ `MikrotikImportService::importPppProfiles` | Complete | - |
| Import PPP Secrets | `mikrotik_ppp_secret` table | ✅ `MikrotikImportService::importPppSecrets` | Complete | - |
| Delete Before Import | Clear old data before import | ✅ Implemented | Complete | - |
| Customer Import Request | Tracking table for imports | ❌ Missing | Add | 🟢 Low |
| Parse IP Pool Ranges | Parse MikroTik IP ranges | ✅ `parseIpRange()` method | Complete | - |
| **4. Provisioning/Sync** |
| Create PPP Secret | Add new user to router | ✅ `RouterProvisioningService` | Complete | - |
| Update PPP Secret | Update existing user | ✅ Supported | Complete | - |
| Profile Dependency Check | Ensure profile exists first | ⚠️ Needs IspBills pattern | Enhance | 🟡 Medium |
| Static IP Handling | Set remote-address for static | ✅ Implemented | Complete | - |
| Disable/Enable Users | Change disabled status | ✅ Supported | Complete | - |
| **5. Customer Metadata** |
| Comment Builder | Embed customer data in objects | ❌ Missing helper utility | Add | 🟡 Medium |
| Comment Format | `oid--X,zid--Y,name--Z,...` | ❌ No standardized format | Add | 🟡 Medium |
| Apply to PPP Secrets | Add comment when provisioning | ⚠️ Partial (no standard format) | Enhance | 🟡 Medium |
| **6. Authentication Modes** |
| Primary Authenticator | Router vs RADIUS setting | ✅ `primary_auth` field exists | Complete | - |
| Router Authentication | Local `/ppp/secret` auth | ✅ Supported | Complete | - |
| RADIUS Authentication | Central AAA via RADIUS | ✅ Supported | Complete | - |
| Hybrid Mode | Failover between modes | ✅ `RouterRadiusFailoverService` | Complete | - |
| **7. Failover/Netwatch** |
| Netwatch Configuration | Auto-failover on RADIUS down | ✅ `NasNetwatchController` | Complete | - |
| Up Script | Disable secrets, kill non-RADIUS | ✅ Implemented | Complete | - |
| Down Script | Enable secrets for fallback | ✅ Implemented | Complete | - |
| Health Monitoring | Ping RADIUS server | ✅ `RouterHealthCheckService` | Complete | - |
| **8. Backup & Recovery** |
| Router-side Export | `/ppp/secret/export file=X` | ⚠️ Pattern needs implementation | Enhance | 🟡 Medium |
| Backup Before Import | Create backup before changes | ✅ `backupIpPools()`, etc. | Complete | - |
| Customer Backup to Router | Mirror users to router | ✅ Jobs exist | Complete | - |
| Backup Management | Store/restore configurations | ✅ `RouterBackupService` | Complete | - |
| Config Templates | Reusable configuration templates | ✅ `RouterConfigurationTemplate` | Complete | - |
| **9. UI Components** |
| NAS Management UI | Create/edit/list NAS devices | ✅ Views exist | Complete | - |
| Router Configuration UI | Configure RADIUS/PPP settings | ✅ `router-configure.blade.php` | Complete | - |
| Import Interface | Import pools/profiles/secrets | ✅ `router-import.blade.php` | Complete | - |
| Progress Tracking | Show import progress | ✅ Implemented | Complete | - |
| Backup Management UI | Restore/delete backups | ✅ `router-backups.blade.php` | Complete | - |
| Failover Status Display | Show failover state | ✅ Component exists | Complete | - |
| **10. Additional Features** |
| Multi-Router Config | Configure multiple routers | ✅ UI exists | Complete | - |
| RADIUS Monitoring | Monitor RADIUS server | ✅ `radius-monitoring.blade.php` | Complete | - |
| Provisioning Logs | Track all provisioning actions | ✅ `RouterProvisioningLog` model | Complete | - |
| Audit Trail | Log all configuration changes | ✅ `AuditLog` system | Complete | - |

---

## Implementation Priorities

### 🔴 High Priority (Critical for IspBills Pattern Compliance)

1. **Enhance RouterConfigurationService for Complete RADIUS Setup**
   - Add method for configuring RADIUS client on router
   - Implement PPP AAA configuration (use-radius, accounting, interim-update)
   - Add RADIUS incoming configuration
   - Follow IspBills pattern with `addMktRows()`, `editMktRow()`, `ttyWirte()`

2. **Standardize Customer Comment Format**
   - Create `RouterCommentHelper` utility class
   - Implement comment builder method: `getComment($customer)`
   - Apply to all provisioning operations

### 🟡 Medium Priority (Nice to Have)

3. **Profile Dependency Checker**
   - Verify profile exists before creating secrets
   - Auto-create profiles if missing
   - Follow IspBills pattern from `CustomersRadPasswordController`

4. **Router-side Backup Enhancement**
   - Implement `/ppp/secret/export` before import
   - Store export file references
   - Add cleanup for old exports

5. **Customer Import Request Tracking**
   - Create `customer_import_requests` table
   - Track import history per router
   - Link imported entities to requests

### 🟢 Low Priority (Future Enhancements)

6. **Advanced Firewall Configuration**
   - Auto-configure firewall rules
   - Manage address lists
   - Queue tree configuration

7. **One-Click Router Setup**
   - Wizard for complete router configuration
   - Pre-configured templates
   - Validation and testing

---

## Detailed Implementation Plans

### 1. Enhanced Router Configuration Service

**File:** `app/Services/RouterConfigurationService.php`

**New Methods to Add:**

```php
/**
 * Configure RADIUS client on router following IspBills pattern
 */
public function configureRadiusClient(MikrotikRouter $router): bool
{
    $nas = $router->nas;
    $radiusServer = config('radius.server_ip');
    
    // Remove existing RADIUS configuration
    $existingRows = $this->api->getMktRows('radius');
    $this->api->removeMktRows('radius', $existingRows);
    
    // Add new RADIUS configuration
    $rows = [[
        'accounting-port' => config('radius.accounting_port', 1813),
        'address' => $radiusServer,
        'authentication-port' => config('radius.authentication_port', 1812),
        'secret' => $nas->secret,
        'service' => 'hotspot,ppp',
        'timeout' => '3s',
        'require-message-auth' => 'no',
    ]];
    
    return $this->api->addMktRows('radius', $rows);
}

/**
 * Configure PPP AAA to use RADIUS
 */
public function configurePppAaa(MikrotikRouter $router): bool
{
    return $this->api->ttyWrite('/ppp/aaa/set', [
        'interim-update' => config('radius.interim_update', '5m'),
        'use-radius' => 'yes',
        'accounting' => 'yes',
    ]);
}

/**
 * Enable RADIUS incoming
 */
public function enableRadiusIncoming(MikrotikRouter $router): bool
{
    return $this->api->ttyWrite('/radius/incoming/set', [
        'accept' => 'yes',
    ]);
}

/**
 * One-click complete RADIUS configuration
 */
public function setupCompleteRadiusConfiguration(MikrotikRouter $router): array
{
    $results = [
        'radius_client' => false,
        'ppp_aaa' => false,
        'radius_incoming' => false,
        'netwatch' => false,
    ];
    
    try {
        DB::beginTransaction();
        
        // 1. Configure RADIUS client
        $results['radius_client'] = $this->configureRadiusClient($router);
        
        // 2. Configure PPP AAA
        $results['ppp_aaa'] = $this->configurePppAaa($router);
        
        // 3. Enable RADIUS incoming
        $results['radius_incoming'] = $this->enableRadiusIncoming($router);
        
        // 4. Setup netwatch for failover
        $netwatchController = app(NasNetwatchController::class);
        $results['netwatch'] = $netwatchController->configureNetwatch($router);
        
        DB::commit();
        
        return [
            'success' => true,
            'results' => $results,
            'message' => 'Router RADIUS configuration completed successfully',
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        
        return [
            'success' => false,
            'results' => $results,
            'message' => 'Failed to configure router: ' . $e->getMessage(),
        ];
    }
}
```

### 2. Router Comment Helper Utility

**File:** `app/Helpers/RouterCommentHelper.php`

```php
<?php

namespace App\Helpers;

use App\Models\Customer;
use App\Models\NetworkUser;

class RouterCommentHelper
{
    /**
     * Generate router comment string from customer data
     * Following IspBills pattern: oid--X,zid--Y,name--Z,...
     */
    public static function getComment($customer): string
    {
        if ($customer instanceof NetworkUser) {
            return self::getNetworkUserComment($customer);
        }
        
        if ($customer instanceof Customer) {
            return self::getCustomerComment($customer);
        }
        
        return '';
    }
    
    /**
     * Generate comment for NetworkUser
     */
    protected static function getNetworkUserComment(NetworkUser $user): string
    {
        return sprintf(
            "uid--%s,name--%s,mobile--%s,zone--%s,pkg--%s,exp--%s,status--%s",
            $user->id,
            self::sanitize($user->name ?? $user->username),
            self::sanitize($user->mobile ?? 'N/A'),
            $user->zone_id ?? 'N/A',
            $user->package_id ?? 'N/A',
            $user->expiry_date?->format('Y-m-d') ?? 'N/A',
            $user->status
        );
    }
    
    /**
     * Generate comment for Customer
     */
    protected static function getCustomerComment(Customer $customer): string
    {
        return sprintf(
            "cid--%s,name--%s,mobile--%s,zone--%s,exp--%s,status--%s",
            $customer->id,
            self::sanitize($customer->name),
            self::sanitize($customer->mobile ?? 'N/A'),
            $customer->zone_id ?? 'N/A',
            $customer->expiry_date?->format('Y-m-d') ?? 'N/A',
            $customer->status
        );
    }
    
    /**
     * Sanitize value for use in comment
     */
    protected static function sanitize(?string $value): string
    {
        if ($value === null) {
            return 'N/A';
        }
        
        // Remove special characters that might break comment format
        return str_replace([',', '--', ';'], ['_', '-', '_'], $value);
    }
    
    /**
     * Parse comment string back to array
     */
    public static function parseComment(string $comment): array
    {
        $parts = explode(',', $comment);
        $data = [];
        
        foreach ($parts as $part) {
            if (str_contains($part, '--')) {
                [$key, $value] = explode('--', $part, 2);
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
}
```

### 3. Enhanced Provisioning with Comments

**Update:** `app/Services/RouterProvisioningService.php`

Add comment support to provisioning methods:

```php
use App\Helpers\RouterCommentHelper;

public function provisionUser(NetworkUser $user, MikrotikRouter $router): bool
{
    // ... existing code ...
    
    $pppSecret = [
        'name' => $user->username,
        'password' => $user->password,
        'profile' => $profile->name,
        'comment' => RouterCommentHelper::getComment($user), // Add comment
    ];
    
    // Handle static IP
    if ($profile->ip_allocation_mode === 'static' && $user->static_ip) {
        $pppSecret['remote-address'] = $user->static_ip;
    }
    
    // ... rest of existing code ...
}
```

### 4. Profile Dependency Checker

**File:** `app/Services/ProfileDependencyService.php`

```php
<?php

namespace App\Services;

use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;

class ProfileDependencyService
{
    protected MikrotikApiService $apiService;
    
    public function __construct(MikrotikApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Ensure PPP profile exists on router before provisioning user
     * Following IspBills pattern from CustomersRadPasswordController
     */
    public function ensureProfileExists(string $profileName, MikrotikRouter $router): bool
    {
        // Check if profile exists on router
        $existingProfiles = $this->apiService->getProfiles($router, ['name' => $profileName]);
        
        if (count($existingProfiles) > 0) {
            return true; // Profile already exists
        }
        
        // Try to find profile in database
        $profile = MikrotikProfile::where('name', $profileName)
            ->where('router_id', $router->id)
            ->first();
        
        if (!$profile) {
            throw new \Exception("Profile '{$profileName}' not found in database or router");
        }
        
        // Push profile to router
        return $this->pushProfileToRouter($profile, $router);
    }
    
    /**
     * Push profile to router
     */
    protected function pushProfileToRouter(MikrotikProfile $profile, MikrotikRouter $router): bool
    {
        $profileData = [
            'name' => $profile->name,
            'local-address' => $profile->local_address ?? config('mikrotik.ppp_local_address', '10.0.0.1'),
            'remote-address' => $profile->remote_address ?? 'ip-pool',
            'rate-limit' => $profile->rate_limit,
        ];
        
        return $this->apiService->createProfile($router, $profileData);
    }
}
```

### 5. Router-side Backup with Export

**Update:** `app/Services/RouterBackupService.php`

```php
/**
 * Create router-side export backup before import
 * Following IspBills pattern
 */
public function exportPppSecretsOnRouter(MikrotikRouter $router): ?string
{
    try {
        $timestamp = now()->timestamp;
        $filename = "ppp-secret-backup-by-billing-{$timestamp}";
        
        // Execute export command on router
        $this->apiService->ttyWrite('/ppp/secret/export', [
            'file' => $filename,
        ]);
        
        // Wait for export to complete
        sleep(2);
        
        // Verify export file was created
        $files = $this->apiService->getFiles($router, ['name' => $filename]);
        
        if (count($files) > 0) {
            // Log the export
            Log::info('PPP secrets exported on router', [
                'router_id' => $router->id,
                'filename' => $filename,
            ]);
            
            return $filename;
        }
        
        return null;
    } catch (\Exception $e) {
        Log::error('Failed to export PPP secrets on router', [
            'router_id' => $router->id,
            'error' => $e->getMessage(),
        ]);
        
        return null;
    }
}
```

---

## Database Enhancements

### Optional: Customer Import Request Tracking Table

```sql
CREATE TABLE customer_import_requests (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    nas_id BIGINT UNSIGNED NULL,
    router_id BIGINT UNSIGNED NOT NULL,
    import_type VARCHAR(50) NOT NULL, -- 'pools', 'profiles', 'secrets', 'all'
    import_disabled_user VARCHAR(10) DEFAULT 'no',
    status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'processing', 'completed', 'failed'
    items_imported INT DEFAULT 0,
    items_failed INT DEFAULT 0,
    error_message TEXT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (nas_id) REFERENCES nas(id) ON DELETE SET NULL,
    FOREIGN KEY (router_id) REFERENCES mikrotik_routers(id) ON DELETE CASCADE,
    
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_router (router_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Testing Checklist

- [ ] Test RADIUS client configuration on MikroTik router
- [ ] Test PPP AAA configuration
- [ ] Test RADIUS incoming configuration
- [ ] Test one-click complete setup
- [ ] Test comment generation for all user types
- [ ] Test comment parsing
- [ ] Test profile dependency checker
- [ ] Test profile auto-creation
- [ ] Test router-side export functionality
- [ ] Test import with backup
- [ ] Verify netwatch failover scripts
- [ ] Test end-to-end user provisioning with comments

---

## Documentation Updates Needed

1. **User Guide**: Document one-click RADIUS setup process
2. **API Documentation**: Document new service methods
3. **Configuration Guide**: Document comment format and usage
4. **Troubleshooting Guide**: Add RADIUS configuration issues
5. **Developer Guide**: Update with IspBills pattern implementations

---

## Summary

### Already Implemented ✅
- Complete router and NAS management
- Import functionality (pools, profiles, secrets)
- User provisioning to routers
- Failover and netwatch configuration
- Backup and recovery system
- Comprehensive UI for all features
- API endpoints and routes
- Testing infrastructure

### Needs Enhancement ⚠️
- Router RADIUS configuration (add complete setup methods)
- Customer comment standardization
- Profile dependency checking before provisioning
- Router-side backup export

### Missing Features ❌
- RouterCommentHelper utility (easy to add)
- Customer import request tracking (optional, low priority)

### Conclusion

Our implementation already covers **~90%** of the IspBills patterns described in the issue. The remaining 10% are mostly enhancements to follow IspBills exact patterns more closely:

1. Standardized comment format (helper utility)
2. Complete one-click RADIUS setup (method consolidation)
3. Profile dependency verification (safety check)
4. Router-side export (backup enhancement)

All of these are incremental improvements that don't require major architectural changes. The core functionality is solid and production-ready.
