# Implementation TODO - Based on Reference System Analysis

**Created:** January 24, 2026  
**Source:** Analysis of 24 PHP controller files from reference ISP billing system  
**Related Document:** [REFERENCE_SYSTEM_ANALYSIS.md](REFERENCE_SYSTEM_ANALYSIS.md)  
**Status:** Ready for Implementation

---

## Quick Start Guide

This document provides a prioritized, actionable TODO list for implementing features identified from the reference ISP billing system analysis. Each item includes effort estimates, priority levels, and implementation notes.

### Document Sections
- [Phase 1: High-Impact Quick Wins](#phase-1-high-impact-quick-wins-weeks-1-2)
- [Phase 2: Automation & Intelligence](#phase-2-automation--intelligence-weeks-3-5)
- [Phase 3: Advanced Features](#phase-3-advanced-features-weeks-6-9)
- [Phase 4: Nice-to-Have Enhancements](#phase-4-nice-to-have-enhancements-weeks-10-12)
- [Generate Views and Add to Panels](#generate-views-and-add-to-panels) ⭐ **NEW**
- [Implementation Guidelines](#implementation-guidelines)
- [Progress Tracking](#progress-tracking)
- [Success Metrics](#success-metrics)

---

## Phase 1: High-Impact Quick Wins (Weeks 1-2)

### 🎯 Priority: CRITICAL ⭐⭐⭐

---

### 1. Dashboard Widget System with Caching ✅ COMPLETE
**Effort:** 2-3 days  
**Impact:** High  
**Complexity:** Low
**Status:** ✅ Complete - All widgets implemented with caching

**Tasks:**
- [x] Add Redis caching to AnalyticsDashboardController
  - [x] Implement cache with 200s TTL for widget data
  - [x] Add cache refresh parameter (?refresh=1)
- [x] Create "Today's Suspension Forecast" widget
  - [x] Query customers with expiry_date = today
  - [x] Calculate total suspension risk amount
  - [x] Show count by package/zone
- [x] Create "Collection Target" widget
  - [x] Calculate bills due today
  - [x] Show collected vs target amounts
  - [x] Add percentage completion bar
- [x] Create "SMS Usage" widget
  - [x] Count SMS sent today
  - [x] Show remaining balance
  - [x] Add cost tracking
- [x] Add widget refresh API endpoints
  - [x] POST /api/v1/widgets/refresh
  - [x] Support selective widget refresh

**Files to Create/Modify:**
```
app/Http/Controllers/Panel/AnalyticsDashboardController.php (modify)
app/Services/WidgetCacheService.php (create)
resources/views/panels/shared/widgets/suspension-forecast.blade.php (create)
resources/views/panels/shared/widgets/collection-target.blade.php (create)
resources/views/panels/shared/widgets/sms-usage.blade.php (create)
routes/api.php (modify)
```

**Testing:**
```bash
php artisan test --filter=WidgetCacheTest
```

---

### 2. Advanced Customer Filtering with Caching ✅ COMPLETE
**Effort:** 2-3 days  
**Impact:** High  
**Complexity:** Medium
**Status:** ✅ Complete - All filtering and caching implemented

**Tasks:**
- [x] Add Redis caching to CustomerController
  - [x] Cache customer list with 300s TTL
  - [x] Implement cache key based on role/tenant
  - [x] Add cache refresh parameter
- [x] Implement online status detection
  - [x] Query radacct table for active sessions
  - [x] Add "online_status" virtual attribute
  - [x] Cache online status separately (60s TTL)
- [x] Add collection-based filtering
  - [x] Filter after cache retrieval for performance
  - [x] Support 15+ filter types:
    - [x] connection_type
    - [x] billing_type  
    - [x] status (active/suspended/expired)
    - [x] payment_status
    - [x] zone_id
    - [x] package_id
    - [x] device_type
    - [x] expiry_date range
    - [x] registration_date range
    - [x] last_payment_date range
    - [x] balance range
    - [x] online_status
    - [x] custom fields
- [x] Add configurable pagination
  - [x] Support per_page parameter (25, 50, 100, 200)
  - [x] Save user preference in session

**Files to Create/Modify:**
```
app/Http/Controllers/Panel/CustomerController.php (modify)
app/Services/CustomerFilterService.php (create)
app/Services/CustomerCacheService.php (create)
app/Traits/HasOnlineStatus.php (create)
```

**Testing:**
```bash
php artisan test --filter=CustomerFilterTest
```

---

### 3. Bulk MikroTik Resource Import ✅ COMPLETE
**Effort:** 4-6 days  
**Impact:** High  
**Complexity:** Medium
**Status:** ✅ Complete - All import functionality implemented

**Tasks:**
- [x] Create MikrotikImportService
  - [x] Implement IP pool bulk import
    - [x] Parse slash notation (192.168.1.0/24)
    - [x] Parse hyphen ranges (192.168.1.1-254)
    - [x] Parse comma-separated IPs
    - [x] Use Net_IPv4 library for CIDR calculation
  - [x] Implement PPP profile import
    - [x] Fetch profiles from router via RouterOS API
    - [x] Map to local profile structure
    - [x] Track local-address assignments
  - [x] Implement PPP secrets bulk import
    - [x] Fetch from router /ppp/secret
    - [x] Filter disabled users (optional)
    - [x] Import as customers
    - [x] Generate initial bills (optional)
  - [x] Add CSV backup before import
    - [x] Export existing data to CSV
    - [x] Store in storage/imports/backups/
    - [x] Add restoration capability
- [x] Create import UI
  - [x] Router selection dropdown
  - [x] Import type selection (pools/profiles/secrets)
  - [x] Options checkboxes (filter disabled, generate bills)
  - [x] Progress indicator
  - [x] Success/failure summary
- [x] Add import validation
  - [x] Check for duplicates
  - [x] Validate IP ranges
  - [x] Verify router connectivity
  - [x] Check available pool capacity

**Files to Create/Modify:**
```
app/Services/MikrotikImportService.php (create)
app/Http/Controllers/Panel/MikrotikImportController.php (create)
app/Jobs/ImportPppSecretsJob.php (create)
resources/views/panels/admin/mikrotik/import.blade.php (create)
routes/web.php (modify)
composer.json (add: pear/net_ipv4)
```

**Testing:**
```bash
php artisan test --filter=MikrotikImportTest
```

---

## Phase 2: Automation & Intelligence (Weeks 3-5)

### 🎯 Priority: HIGH ⭐⭐

---

### 4. Zero-Touch Router Provisioning ✅ COMPLETE ⭐ FLAGSHIP
**Effort:** 10-15 days  
**Impact:** Very High  
**Complexity:** Very High
**Status:** ✅ Complete - Full automation with backup/rollback

**Tasks:**
- [x] Create RouterProvisioningService
  - [x] Implement RADIUS server configuration
    - [x] Add radius server to /radius
    - [x] Configure auth-port (1812) and acct-port (1813)
    - [x] Set radius secret from env
    - [x] Enable interim updates (5m)
  - [x] Implement hotspot profile setup
    - [x] Create hotspot server profile
    - [x] Enable MAC authentication
    - [x] Set cookie timeout
    - [x] Configure login page URL
    - [x] Link to RADIUS server
  - [x] Implement PPPoE server configuration
    - [x] Create PPPoE server profile
    - [x] Set default profile
    - [x] Configure local-address pool
    - [x] Enable RADIUS authentication
    - [x] Add duplicate session prevention script
  - [x] Implement NAT rules
    - [x] Add srcnat for hotspot bypass
    - [x] Configure dstnat for portal redirect
  - [x] Implement firewall rules
    - [x] Allow SNMP from monitoring server
    - [x] Block suspended pool access
    - [x] Add walled garden rules
  - [x] Implement system configuration
    - [x] Set system identity
    - [x] Configure NTP servers
    - [x] Set timezone
  - [x] Add walled garden IPs
    - [x] Central server IP
    - [x] Payment gateway IPs
    - [x] DNS server IPs
  - [x] Add suspended users pool
    - [x] Create IP pool for suspended users (e.g., 10.255.255.0/24)
    - [x] Configure redirect rules
- [x] Create provisioning UI
  - [x] Router selection
  - [x] Configuration template selection
  - [x] Preview configuration before apply
  - [x] Execute provisioning
  - [x] Show progress with steps
  - [x] Display success/failure per step
- [x] Add configuration validation
  - [x] Verify router connectivity before start
  - [x] Check RouterOS version compatibility
  - [x] Validate RADIUS server reachability
  - [x] Test configuration after apply
- [x] Implement rollback capability
  - [x] Save router config before changes
  - [x] Implement rollback on failure
  - [x] Add manual rollback option

**Files to Create/Modify:**
```
app/Services/RouterProvisioningService.php (create)
app/Http/Controllers/Panel/RouterProvisioningController.php (create)
app/Models/RouterConfigurationTemplate.php (create)
database/migrations/xxxx_create_router_configuration_templates_table.php (create)
resources/views/panels/admin/routers/provision.blade.php (create)
routes/web.php (modify)
```

**Configuration Template Example:**
```php
// Store as JSON in database
{
  "radius": {
    "server": "{{ central_server_ip }}",
    "auth_port": 1812,
    "acct_port": 1813,
    "secret": "{{ radius_secret }}",
    "interim_update": "5m"
  },
  "hotspot": {
    "name": "{{ operator_name }}-hotspot",
    "login_page": "{{ central_server_url }}/hotspot/login",
    "cookie_timeout": "1d",
    "mac_auth_mode": true
  },
  "pppoe": {
    "name": "{{ operator_name }}-pppoe",
    "default_profile": "default",
    "local_address_pool": "local-pool",
    "duplicate_session_script": "..."
  }
}
```

**Testing:**
```bash
php artisan test --filter=RouterProvisioningTest
```

---

### 5. Intelligent Hotspot Login Detection ✅ COMPLETE
**Effort:** 5-7 days  
**Impact:** Very High  
**Complexity:** High
**Status:** ✅ Complete - All 10 scenarios implemented with controller integration

**Tasks:**
- [x] Create HotspotScenarioDetectionService with 10-scenario detection
  - [x] Scenario 1: Registered customer (normal login)
    - [x] Verify mobile + MAC in radcheck
    - [x] Check account status (active/suspended/expired)
    - [x] Allow login if all checks pass
  - [x] Scenario 2: New device/MAC change
    - [x] Mobile found but different MAC
    - [x] Show "Device changed?" confirmation
    - [x] Option to replace MAC or add as secondary
  - [x] Scenario 3: Multiple customers on same device
    - [x] Same MAC, different mobiles in radcheck
    - [x] Show customer selection list
    - [x] Login with selected customer
  - [x] Scenario 4: Suspended account (volume limit)
    - [x] Check CustomerVolumeLimit model
    - [x] Show "Volume limit exceeded" message
    - [x] Display usage stats
    - [x] Option to recharge
  - [x] Scenario 5: Suspended account (time limit)
    - [x] Check CustomerTimeLimit model
    - [x] Show "Time limit exceeded" message
    - [x] Display remaining time
    - [x] Option to recharge
  - [x] Scenario 6: Unregistered mobile
    - [x] Mobile not in radcheck
    - [x] Show "Not registered" message
    - [x] Option to self-signup (if enabled)
  - [x] Scenario 7: Device change for registered customer
    - [x] Implement automatic MAC replacement
    - [x] Update radcheck.value (Calling-Station-Id)
    - [x] Log MAC change in audit log
    - [x] Send SMS notification
  - [x] Scenario 8: Link login (public access)
    - [x] Generate temporary link token
    - [x] Track link login separately
    - [x] Limited duration (e.g., 1 hour)
    - [x] No authentication required
  - [x] Scenario 9: Logout tracking
    - [x] Update radacct on logout
    - [x] Clear session from active list
    - [x] Log logout time
  - [x] Scenario 10: Cross-radius server lookup
    - [x] Query central registry (if multi-operator)
    - [x] Support federated authentication
    - [x] Redirect to home operator portal
- [x] Update HotspotLoginController to use service
- [x] Add SMS notifications
  - [x] Send SMS on device change
  - [x] Send SMS on suspension
  - [x] Send SMS on successful login (optional)

**Files to Create/Modify:**
```
app/Http/Controllers/HotspotLoginController.php (modify)
app/Services/HotspotScenarioDetectionService.php (create)
app/Models/HotspotLoginLog.php (create)
database/migrations/xxxx_create_hotspot_login_logs_table.php (create)
resources/views/hotspot/scenarios/* (create multiple views)
```

**Testing:**
```bash
php artisan test --filter=HotspotScenarioTest
```

---

## Phase 3: Advanced Features (Weeks 6-9)

### 🎯 Priority: MEDIUM ⭐

---

### 6. 3-Level Package Hierarchy ✅ COMPLETE
**Effort:** 7-10 days  
**Impact:** High  
**Complexity:** High
**Status:** ✅ Complete - Full 3-tier system with validation

**Tasks:**
- [x] Create MasterPackage model
  - [x] Fields: name, description, speed, volume, validity, base_price
  - [x] Belongs to developer/super-admin
  - [x] Visibility: public/private
  - [x] Trial package flag
- [x] Create OperatorPackageRate model
  - [x] Links: master_package_id, operator_id
  - [x] Fields: operator_price, status, assigned_by
  - [x] Validation: operator_price <= master_package.base_price
- [x] Update Package model
  - [x] Add master_package_id foreign key
  - [x] Add operator_package_rate_id foreign key
  - [x] Inherit settings from master package
  - [x] Allow price customization
- [x] Create MasterPackageController
  - [x] CRUD operations for master packages
  - [x] Assign to operators
  - [x] Track usage statistics
- [x] Create OperatorPackageController (already exists, modify)
  - [x] Show available master packages
  - [x] Create operator-specific pricing
  - [x] Assign to sub-operators
- [x] Add pricing validation
  - [x] Prevent operator from pricing above master price
  - [x] Warn if margin too low
  - [x] Calculate suggested retail price
- [x] Add trial package protection
  - [x] Cannot delete master packages with trial flag
  - [x] Cannot modify pricing on trial packages
  - [x] Auto-expire trial packages after period
- [x] Add customer count validation
  - [x] Prevent deletion if customers exist
  - [x] Show migration path before deletion

**Files to Create/Modify:**
```
app/Models/MasterPackage.php (create)
app/Models/OperatorPackageRate.php (create)
app/Models/Package.php (modify)
database/migrations/xxxx_create_master_packages_table.php (create)
database/migrations/xxxx_create_operator_package_rates_table.php (create)
app/Http/Controllers/Panel/MasterPackageController.php (create)
app/Http/Controllers/Panel/OperatorController.php (modify)
resources/views/panels/developer/master-packages/* (create)
resources/views/panels/admin/operator-packages/* (create)
```

**Testing:**
```bash
php artisan test --filter=PackageHierarchyTest
```

---

### 7. RRD Graph System for Performance Monitoring ✅ COMPLETE
**Effort:** 8-10 days  
**Impact:** Medium  
**Complexity:** High
**Status:** ✅ Complete - Full bandwidth monitoring with graphs

**Tasks:**
- [x] Install RRDtool
  - [x] Add to Docker containers
  - [x] Install PHP RRD extension
  - [x] Test RRD functionality
- [x] Create RrdGraphService
  - [x] Implement RRD database creation
    - [x] Step: 300s (5 minutes)
    - [x] Data sources: upload (COUNTER), download (COUNTER)
    - [x] RRAs: AVERAGE, MAX for 1h, 24h, 1w, 1m
  - [x] Implement data collection
    - [x] Query radacct for customer usage
    - [x] Calculate rates (bytes per second)
    - [x] Update RRD database
  - [x] Implement graph generation
    - [x] Hourly graph (last 60 data points)
    - [x] Daily graph (last 24 hours)
    - [x] Weekly graph (last 7 days)
    - [x] Monthly graph (last 30 days)
    - [x] PNG format with Base64 encoding
  - [x] Implement graph caching
    - [x] Cache graphs for 5 minutes
    - [x] Regenerate on cache miss
- [x] Create scheduled job for data collection
  - [x] Run every 5 minutes
  - [x] Collect data for all active customers
  - [x] Update RRD databases
- [x] Create API endpoints
  - [x] GET /api/v1/customers/{id}/graphs/hourly
  - [x] GET /api/v1/customers/{id}/graphs/daily
  - [x] GET /api/v1/customers/{id}/graphs/weekly
  - [x] GET /api/v1/customers/{id}/graphs/monthly
- [x] Create UI components
  - [x] Add graphs to customer detail page
  - [x] Add timeframe selector
  - [x] Add zoom/pan functionality
  - [x] Show upload/download separately

**Files to Create/Modify:**
```
app/Services/RrdGraphService.php (create)
app/Jobs/CollectBandwidthDataJob.php (create)
app/Http/Controllers/Api/V1/GraphController.php (create)
resources/views/panels/shared/components/bandwidth-graph.blade.php (create)
routes/api.php (modify)
docker/Dockerfile (modify - add rrdtool)
composer.json (add: leth/php-rrd)
```

**Testing:**
```bash
php artisan test --filter=RrdGraphTest
```

---

### 8. VPN Account with Automatic Port Forwarding ✅ COMPLETE
**Effort:** 3-5 days  
**Impact:** High  
**Complexity:** Medium
**Status:** ✅ Complete - Automatic provisioning implemented

**Tasks:**
- [x] Create VpnProvisioningService
  - [x] Implement automatic credential generation
    - [x] Generate random username (8 chars)
    - [x] Generate random password (12 chars)
  - [x] Implement IP/port pool scanning
    - [x] Scan VpnPool for available IPs
    - [x] Allocate next available IP
    - [x] Track used IPs in vpn_pool_allocations
  - [x] Implement port allocation
    - [x] Range: 5001-5500 (500 ports)
    - [x] Find next available port
    - [x] Associate with VPN account
  - [x] Add RADIUS attribute creation
    - [x] Cleartext-Password
    - [x] Mikrotik-Rate-Limit (5M default)
    - [x] Framed-IP-Address
    - [x] Add to radcheck/radreply tables
  - [x] Add automatic MikroTik NAT rule creation
    - [x] Create dstnat rule for Winbox port forwarding
    - [x] Map external port to internal IP:8291
    - [x] Add to /ip firewall nat on router
  - [x] Implement account cleanup
    - [x] Delete RADIUS attributes
    - [x] Remove NAT rules from router
    - [x] Release IP/port back to pool
    - [x] Log deletion in audit log

**Files to Create/Modify:**
```
app/Http/Controllers/Panel/VpnController.php (modify)
app/Services/VpnProvisioningService.php (create)
app/Models/VpnPoolAllocation.php (create)
database/migrations/xxxx_create_vpn_pool_allocations_table.php (create)
resources/views/panels/admin/vpn/create.blade.php (modify)
```

**Testing:**
```bash
php artisan test --filter=VpnProvisioningTest
```

---

### 9. Event-Driven Bulk Customer Import ✅ COMPLETE
**Effort:** 4-5 days  
**Impact:** Medium  
**Complexity:** Medium
**Status:** ✅ Complete - Event-driven architecture implemented

**Tasks:**
- [x] Create ImportPppCustomersRequested event
  - [x] Properties: operator_id, nas_id, options
  - [x] Options: filter_disabled, generate_bills
- [x] Create ImportPppCustomersListener
  - [x] Dispatch bulk import job
  - [x] Track import status
  - [x] Send notification on completion
- [x] Create ImportPppCustomersJob
  - [x] Fetch PPP secrets from router
  - [x] Filter disabled users (if option enabled)
  - [x] Create customers in batch
  - [x] Generate bills (if option enabled)
  - [x] Track success/failure count
- [x] Add duplicate detection
  - [x] Check same operator + nas + date
  - [x] Prevent duplicate imports
  - [x] Show warning if duplicate detected
- [x] Add import status tracking
  - [x] Create customer_imports table
  - [x] Track: total, success, failed, in_progress
  - [x] Store error details for failed imports
- [x] Create import UI
  - [x] Router selection
  - [x] Options checkboxes
  - [x] Start import button
  - [x] Progress indicator
  - [x] Results summary

**Files to Create/Modify:**
```
app/Events/ImportPppCustomersRequested.php (create)
app/Listeners/ImportPppCustomersListener.php (create)
app/Jobs/ImportPppCustomersJob.php (create)
app/Models/CustomerImport.php (create)
database/migrations/xxxx_create_customer_imports_table.php (create)
app/Http/Controllers/Panel/CustomerImportController.php (create)
resources/views/panels/admin/customers/import.blade.php (create)
```

**Testing:**
```bash
php artisan test --filter=CustomerImportTest
```

---

## Phase 4: Nice-to-Have Enhancements (Weeks 10-12)

### 🎯 Priority: LOW

---

### 10. Multi-Step Customer Creation with Wizard
**Effort:** 3-4 days  
**Impact:** Medium  
**Complexity:** Medium

**Tasks:**
- [ ] Create temp_customers table
  - [ ] Store partial customer data
  - [ ] Session-based or user-based
  - [ ] Auto-expire after 24 hours
- [ ] Implement wizard workflow
  - [ ] Step 1: Basic Information (name, mobile, email)
  - [ ] Step 2: Connection Type (PPPoE, Hotspot, Static IP, Other)
  - [ ] Step 3: Package Selection
  - [ ] Step 4: Address & Zone
  - [ ] Step 5: Custom Fields (if any)
  - [ ] Step 6: Initial Payment
  - [ ] Step 7: Account Activation & Confirmation
- [ ] Add connection type-specific logic
  - [ ] PPPoE: Username generation, profile selection
  - [ ] Hotspot: MAC address, device type
  - [ ] Static IP: IP allocation, subnet selection
  - [ ] Other: Custom configuration
- [ ] Add automatic initial billing
  - [ ] Generate first invoice
  - [ ] Apply payment to invoice
  - [ ] Set expiry date
- [ ] Add wizard navigation
  - [ ] Next/Previous buttons
  - [ ] Progress indicator
  - [ ] Save draft functionality
  - [ ] Resume from saved draft

**Files to Create/Modify:**
```
database/migrations/xxxx_create_temp_customers_table.php (create)
app/Models/TempCustomer.php (create)
app/Http/Controllers/Panel/CustomerWizardController.php (create)
resources/views/panels/shared/customers/wizard/* (create)
```

**Testing:**
```bash
php artisan test --filter=CustomerWizardTest
```

---

### 11. Custom Field Support for Customers
**Effort:** 3-4 days  
**Impact:** Medium  
**Complexity:** Medium

**Tasks:**
- [ ] Create CustomerCustomField model
  - [ ] Fields: name, type, required, options, order
  - [ ] Types: text, number, date, select, checkbox, textarea
  - [ ] Tenant-scoped (belongs to operator)
- [ ] Create CustomerCustomAttribute model
  - [ ] Links: customer_id, field_id
  - [ ] Field: value (JSON for complex types)
- [ ] Add custom field management UI
  - [ ] Create/edit/delete custom fields
  - [ ] Reorder fields
  - [ ] Set field visibility per role
- [ ] Add custom field rendering in forms
  - [ ] Dynamic form field generation
  - [ ] Validation based on field type
  - [ ] Conditional field display
- [ ] Add custom field display in customer view
  - [ ] Show all custom fields
  - [ ] Group by category
  - [ ] Edit inline

**Files to Create/Modify:**
```
app/Models/CustomerCustomField.php (create)
app/Models/CustomerCustomAttribute.php (create)
database/migrations/xxxx_create_customer_custom_fields_table.php (create)
database/migrations/xxxx_create_customer_custom_attributes_table.php (create)
app/Http/Controllers/Panel/CustomerCustomFieldController.php (create)
resources/views/panels/admin/custom-fields/* (create)
```

**Testing:**
```bash
php artisan test --filter=CustomFieldTest
```

---

### 12. Asynchronous IP Pool Migration
**Effort:** 3-5 days  
**Impact:** Medium  
**Complexity:** Medium

**Tasks:**
- [ ] Create ReAllocateIPv4ForProfileJob
  - [ ] Move all customers from old pool to new pool
  - [ ] Update radreply Framed-IP-Address
  - [ ] Track progress
  - [ ] Handle failures gracefully
- [ ] Create PPPoEProfilesIpAllocationModeChangeJob
  - [ ] Switch between static and dynamic allocation
  - [ ] Update profile configuration on router
  - [ ] Notify affected customers
- [ ] Add pool capacity validation
  - [ ] Check new pool has enough IPs
  - [ ] Warn if capacity insufficient
  - [ ] Prevent migration if space unavailable
- [ ] Add progress tracking
  - [ ] Store progress in Redis
  - [ ] API endpoint for progress polling
  - [ ] Show progress bar in UI
- [ ] Add rollback capability
  - [ ] Save state before migration
  - [ ] Rollback on failure
  - [ ] Manual rollback option

**Files to Create/Modify:**
```
app/Jobs/ReAllocateIPv4ForProfileJob.php (create)
app/Jobs/PPPoEProfilesIpAllocationModeChangeJob.php (create)
app/Services/IpPoolMigrationService.php (create)
app/Http/Controllers/Panel/IpPoolMigrationController.php (create)
resources/views/panels/admin/ip-pools/migrate.blade.php (create)
```

**Testing:**
```bash
php artisan test --filter=IpPoolMigrationTest
```

---

### 13. Router-to-RADIUS Migration Tool
**Effort:** 2-3 days  
**Impact:** Medium  
**Complexity:** Low

**Tasks:**
- [ ] Create migration command
  - [ ] php artisan mikrotik:migrate-to-radius {router_id}
  - [ ] Interactive prompts for confirmation
- [ ] Implement migration steps
  - [ ] Step 1: Verify RADIUS server connectivity
  - [ ] Step 2: Backup current PPP secrets
  - [ ] Step 3: Disable local PPP secrets on router
  - [ ] Step 4: Force disconnect active sessions
  - [ ] Step 5: Enable RADIUS authentication
  - [ ] Step 6: Test with sample login
  - [ ] Step 7: Monitor for issues
- [ ] Add rollback capability
  - [ ] Restore PPP secrets from backup
  - [ ] Re-enable local authentication
  - [ ] Disable RADIUS
- [ ] Add safety checks
  - [ ] Require --force flag for production
  - [ ] Create backup automatically
  - [ ] Validate each step before proceeding

**Files to Create/Modify:**
```
app/Console/Commands/MigrateRouterToRadiusCommand.php (create)
app/Services/RouterMigrationService.php (create)
```

**Testing:**
```bash
php artisan test --filter=RouterMigrationTest
```

---

### 14. Card Distributor Mobile API
**Effort:** 1-2 days  
**Impact:** Low  
**Complexity:** Low

**Tasks:**
- [ ] Create distributor API endpoints
  - [ ] GET /api/v1/distributor/mobiles
  - [ ] GET /api/v1/distributor/cards
  - [ ] GET /api/v1/distributor/sales
  - [ ] POST /api/v1/distributor/sales
- [ ] Add country code validation
  - [ ] Support BD (+880), IN (+91), PK (+92), etc.
  - [ ] Normalize mobile numbers
- [ ] Add API caching
  - [ ] Cache mobile list (600s TTL)
  - [ ] Cache card inventory (300s TTL)
- [ ] Add rate limiting
  - [ ] 60 requests per minute per distributor
- [ ] Create API documentation
  - [ ] OpenAPI/Swagger spec
  - [ ] Example requests/responses

**Files to Create/Modify:**
```
app/Http/Controllers/Api/V1/CardDistributorController.php (create)
routes/api.php (modify)
documentation/api/distributor-api.yaml (create)
```

**Testing:**
```bash
php artisan test --filter=DistributorApiTest
```

---

## Implementation Guidelines

### Before Starting Any Feature

1. ✅ Read full feature specification in REFERENCE_SYSTEM_ANALYSIS.md
2. ✅ Review existing similar features in codebase
3. ✅ Create feature branch: `feature/[feature-name]`
4. ✅ Write tests first (TDD approach)
5. ✅ Implement feature with minimal changes
6. ✅ Test across different role levels
7. ✅ Update documentation
8. ✅ Create pull request for review

### Code Quality Standards

- ✅ Follow PSR-12 coding standards
- ✅ Use type hints for all methods
- ✅ Add PHPDoc blocks for complex logic
- ✅ Write tests for new features (PHPUnit)
- ✅ Use Laravel best practices
- ✅ Keep methods under 50 lines
- ✅ Use service classes for business logic
- ✅ Add database transactions for multi-step operations

### Testing Requirements

For each feature:
- ✅ Unit tests for service classes
- ✅ Feature tests for controllers/API endpoints
- ✅ Integration tests for complex workflows
- ✅ Test with different role levels
- ✅ Test tenant isolation
- ✅ Test edge cases and error handling

### Security Checklist

- ✅ Apply tenant isolation to all queries
- ✅ Use policies for authorization
- ✅ Validate all user inputs
- ✅ Escape all outputs
- ✅ Use parameterized queries (Eloquent ORM)
- ✅ No hardcoded secrets
- ✅ Rate limit API endpoints
- ✅ Log all state changes (audit log)

---

## Progress Tracking

### Phase 1 Progress: 3/3 (100%) ✅ COMPLETE
- [x] Dashboard Widget System ✅
- [x] Advanced Customer Filtering ✅
- [x] Bulk MikroTik Resource Import ✅

### Phase 2 Progress: 2/2 (100%) ✅ COMPLETE
- [x] Zero-Touch Router Provisioning ✅ COMPLETE
- [x] Intelligent Hotspot Login Detection ✅ COMPLETE

### Phase 3 Progress: 4/4 (100%) ✅ COMPLETE
- [x] 3-Level Package Hierarchy ✅ COMPLETE
- [x] RRD Graph System ✅ COMPLETE
- [x] VPN Account Automation ✅
- [x] Event-Driven Bulk Import ✅

### Phase 4 Progress: 0/5 (0%)
- [ ] Multi-Step Customer Creation
- [ ] Custom Field Support
- [ ] Async IP Pool Migration
- [ ] Router-to-RADIUS Migration Tool
- [ ] Card Distributor Mobile API

### Overall Progress: 6/14 (43%) 

**Last Updated:** January 24, 2026 14:45 UTC
**Status:** Phase 1 Complete, Phase 2-3 In Progress
**Production Ready Features:** 6/14

---

## Success Metrics

Track these metrics as features are implemented:

| Metric | Baseline | Target | Current |
|--------|----------|--------|---------|
| Router setup time | 2-4 hours | 15 minutes | - |
| Customer list load time | 2-5 seconds | < 1 second | - |
| Hotspot support tickets | 100/month | 20/month | - |
| Dashboard load time | 3 seconds | < 500ms | - |
| Import time (1000 customers) | 30 minutes | 5 minutes | - |
| User satisfaction score | 3.5/5 | 4.5/5 | - |

---

## Generate Views and Add to Panels

This section provides comprehensive guidance on creating and adding views to the role-based panel system.

### Current View Implementation Status

**Total Views:** 251 Blade templates  
**Last Updated:** January 24, 2026

#### Views by Role Panel

| Role Panel | View Count | Status |
|------------|------------|--------|
| Admin | 119 | ✅ Complete |
| Developer | 25 | ✅ Complete |
| Super Admin | 15 | ✅ Complete |
| Sales Manager | 11 | ✅ Complete |
| Accountant | 9 | ✅ Complete |
| Operator | 9 | ✅ Complete |
| Manager | 7 | ✅ Complete |
| Staff | 7 | ✅ Complete |
| Customer | 6 | ✅ Complete |
| Card Distributor | 5 | ✅ Complete |
| Sub-Operator | 5 | ✅ Complete |
| Shared Components | 27 | ✅ Complete |
| Partials | 5 | ✅ Complete |
| Layouts | 1 | ✅ Complete |

### View Directory Structure

```
resources/views/panels/
├── layouts/
│   └── app.blade.php              # Main layout template
├── partials/
│   ├── sidebar.blade.php          # Dynamic sidebar menu
│   ├── navigation.blade.php       # Top navigation bar
│   ├── pagination.blade.php       # Pagination component
│   └── [other partials]
├── shared/
│   ├── components/                # Reusable UI components
│   ├── widgets/                   # Dashboard widgets
│   ├── customers/                 # Shared customer views
│   ├── analytics/                 # Analytics views
│   ├── tickets/                   # Ticket system views
│   └── [other shared views]
├── [role-name]/                   # Role-specific panels
│   ├── dashboard.blade.php        # Role dashboard
│   ├── [module]/                  # Module-specific views
│   │   ├── index.blade.php        # List view
│   │   ├── create.blade.php       # Create form
│   │   ├── edit.blade.php         # Edit form
│   │   └── show.blade.php         # Detail view
│   └── [other role views]
```

### Step-by-Step: Creating New Panel Views

#### 1. Determine the Appropriate Location

**For role-specific features:**
```
resources/views/panels/{role-name}/{module}/{view-name}.blade.php
```

**For shared components across roles:**
```
resources/views/panels/shared/{component-type}/{view-name}.blade.php
```

**Examples:**
- Admin customer list: `resources/views/panels/admin/customers/index.blade.php`
- Operator billing: `resources/views/panels/operator/bills/index.blade.php`
- Shared widget: `resources/views/panels/shared/widgets/sales-chart.blade.php`

#### 2. Create the Blade Template

All panel views follow this standard structure:

```blade
@extends('panels.layouts.app')

@section('title', 'Page Title')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Page Title</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Page description</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Action Buttons -->
                    <a href="{{ route('route.name') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Your content here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Optional: Page-specific JavaScript -->
@endpush
```

#### 3. Common View Patterns

##### Dashboard View Pattern
```blade
<!-- Stats Grid -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Icon SVG path -->
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Metric Name</dt>
                        <dd class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['value'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
```

##### List/Index View Pattern
```blade
<!-- Search and Filter -->
<div class="mb-4 flex flex-col sm:flex-row gap-4">
    <input type="text" placeholder="Search..." 
           class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
    <!-- Filter dropdowns -->
</div>

<!-- Data Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Column Name
                </th>
                <!-- More columns -->
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($items as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ $item->field }}
                    </td>
                    <!-- More cells -->
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No items found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $items->links() }}
</div>
```

##### Form View Pattern (Create/Edit)
```blade
<form method="POST" action="{{ route('route.name', $item ?? null) }}" class="space-y-6">
    @csrf
    @if(isset($item))
        @method('PUT')
    @endif

    <!-- Form Field -->
    <div>
        <label for="field_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Field Label
        </label>
        <input type="text" name="field_name" id="field_name" 
               value="{{ old('field_name', $item->field_name ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        @error('field_name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end">
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            {{ isset($item) ? 'Update' : 'Create' }}
        </button>
    </div>
</form>
```

#### 4. Add Route for the View

In `routes/web.php`, add the route within the appropriate role group:

```php
Route::prefix('panel/{role-name}')->name('panel.{role-name}.')
    ->middleware(['auth', 'role:{role-name}'])
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [RoleController::class, 'dashboard'])->name('dashboard');
        
        // Resource routes
        Route::resource('module-name', ModuleController::class);
        
        // Custom routes
        Route::get('/custom-action', [RoleController::class, 'customAction'])->name('custom-action');
    });
```

#### 5. Implement Controller Method

Create or update the controller to pass data to the view:

```php
<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function viewMethod(): View
    {
        // Fetch data with proper tenant isolation
        $data = Model::where('tenant_id', auth()->user()->tenant_id)
            ->paginate(20);
        
        $stats = [
            'metric1' => 0,
            'metric2' => 0,
        ];

        return view('panels.role-name.module.view-name', compact('data', 'stats'));
    }
}
```

#### 6. Update Sidebar Navigation (Optional)

If the new view needs a menu item, update `resources/views/panels/partials/sidebar.blade.php`:

```php
@php
$menus = [
    [
        'label' => 'Module Name',
        'icon' => 'icon-name',
        'route' => 'panel.role.module.index',
        'active' => request()->routeIs('panel.role.module.*'),
    ],
    // Or with sub-menu
    [
        'label' => 'Management',
        'icon' => 'users',
        'children' => [
            ['label' => 'Customers', 'route' => 'panel.role.customers.index'],
            ['label' => 'Packages', 'route' => 'panel.role.packages.index'],
        ]
    ],
];
@endphp
```

### Design Standards

#### Tailwind CSS Classes
- **Container**: `space-y-6` for vertical spacing
- **Cards**: `bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg`
- **Text**: `text-gray-900 dark:text-gray-100` for primary text
- **Buttons**: `bg-indigo-600 hover:bg-indigo-700` for primary actions
- **Forms**: `rounded-md border-gray-300 dark:border-gray-700`

#### Dark Mode Support
Always include dark mode variants:
```blade
class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
```

#### Responsive Design
Use responsive prefixes:
```blade
class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-4"
```

### Testing Your Views

1. **Access Control Test:**
   ```bash
   # Login as different roles and verify access
   ```

2. **Data Display Test:**
   ```bash
   # Verify data is displayed correctly with proper tenant isolation
   ```

3. **Responsive Test:**
   ```bash
   # Test on mobile, tablet, and desktop viewports
   ```

4. **Dark Mode Test:**
   ```bash
   # Toggle dark mode and verify all elements are visible
   ```

### Related Documentation

- **Panel Implementation Guide**: [PANEL_IMPLEMENTATION_GUIDE.md](PANEL_IMPLEMENTATION_GUIDE.md)
- **Panel Specification**: [PANELS_SPECIFICATION.md](PANELS_SPECIFICATION.md)
- **Panel Development Summary**: [PANEL_README.md](PANEL_README.md)
- **Routing Guide**: [ROUTING_TROUBLESHOOTING_GUIDE.md](ROUTING_TROUBLESHOOTING_GUIDE.md)
- **Role System**: [docs/technical/ROLE_SYSTEM.md](docs/technical/ROLE_SYSTEM.md)

### Quick Reference Commands

```bash
# Create a new view directory
mkdir -p resources/views/panels/{role-name}/{module}

# Create a new view file
touch resources/views/panels/{role-name}/{module}/{view-name}.blade.php

# Find all views for a specific role
find resources/views/panels/{role-name} -name "*.blade.php"

# Count views per role
for dir in resources/views/panels/*/; do 
    echo "$(basename $dir): $(find $dir -name '*.blade.php' 2>/dev/null | wc -l)"; 
done
```

---

## Notes

- This TODO list is derived from analyzing 24 controller files from a reference ISP billing system
- All features should maintain compatibility with our 12-level role hierarchy
- Do not break existing functionality while implementing new features
- Prioritize features based on business needs and available resources
- Update this document as features are completed
- View generation should follow established patterns for consistency
- Always implement tenant isolation in views and controllers
- Maintain dark mode support across all new views

---

**Last Updated:** January 24, 2026  
**Next Review:** After Phase 1 completion  
**Maintainer:** Development Team
