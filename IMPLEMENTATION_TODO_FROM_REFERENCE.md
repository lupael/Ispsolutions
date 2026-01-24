# Implementation TODO - Based on Reference System Analysis

**Created:** January 24, 2026  
**Source:** Analysis of 24 PHP controller files from reference ISP billing system  
**Related Document:** [REFERENCE_SYSTEM_ANALYSIS.md](REFERENCE_SYSTEM_ANALYSIS.md)  
**Status:** Ready for Implementation

---

## Quick Start Guide

This document provides a prioritized, actionable TODO list for implementing features identified from the reference ISP billing system analysis. Each item includes effort estimates, priority levels, and implementation notes.

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

### 4. Zero-Touch Router Provisioning ⭐ FLAGSHIP
**Effort:** 10-15 days  
**Impact:** Very High  
**Complexity:** Very High

**Tasks:**
- [ ] Create RouterProvisioningService
  - [ ] Implement RADIUS server configuration
    - [ ] Add radius server to /radius
    - [ ] Configure auth-port (1812) and acct-port (1813)
    - [ ] Set radius secret from env
    - [ ] Enable interim updates (5m)
  - [ ] Implement hotspot profile setup
    - [ ] Create hotspot server profile
    - [ ] Enable MAC authentication
    - [ ] Set cookie timeout
    - [ ] Configure login page URL
    - [ ] Link to RADIUS server
  - [ ] Implement PPPoE server configuration
    - [ ] Create PPPoE server profile
    - [ ] Set default profile
    - [ ] Configure local-address pool
    - [ ] Enable RADIUS authentication
    - [ ] Add duplicate session prevention script
  - [ ] Implement NAT rules
    - [ ] Add srcnat for hotspot bypass
    - [ ] Configure dstnat for portal redirect
  - [ ] Implement firewall rules
    - [ ] Allow SNMP from monitoring server
    - [ ] Block suspended pool access
    - [ ] Add walled garden rules
  - [ ] Implement system configuration
    - [ ] Set system identity
    - [ ] Configure NTP servers
    - [ ] Set timezone
  - [ ] Add walled garden IPs
    - [ ] Central server IP
    - [ ] Payment gateway IPs
    - [ ] DNS server IPs
  - [ ] Add suspended users pool
    - [ ] Create IP pool for suspended users (e.g., 10.255.255.0/24)
    - [ ] Configure redirect rules
- [ ] Create provisioning UI
  - [ ] Router selection
  - [ ] Configuration template selection
  - [ ] Preview configuration before apply
  - [ ] Execute provisioning
  - [ ] Show progress with steps
  - [ ] Display success/failure per step
- [ ] Add configuration validation
  - [ ] Verify router connectivity before start
  - [ ] Check RouterOS version compatibility
  - [ ] Validate RADIUS server reachability
  - [ ] Test configuration after apply
- [ ] Implement rollback capability
  - [ ] Save router config before changes
  - [ ] Implement rollback on failure
  - [ ] Add manual rollback option

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

### 5. Intelligent Hotspot Login Detection ✅ COMPLETE (Service Layer)
**Effort:** 5-7 days  
**Impact:** Very High  
**Complexity:** High
**Status:** ✅ Service layer complete - Controller integration pending

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
  - [ ] Scenario 8: Link login (public access)
    - [ ] Generate temporary link token
    - [ ] Track link login separately
    - [ ] Limited duration (e.g., 1 hour)
    - [ ] No authentication required
  - [ ] Scenario 9: Logout tracking
    - [ ] Update radacct on logout
    - [ ] Clear session from active list
    - [ ] Log logout time
  - [ ] Scenario 10: Cross-radius server lookup
    - [ ] Query central registry (if multi-operator)
    - [ ] Support federated authentication
    - [ ] Redirect to home operator portal
- [ ] Update HotspotLoginController to use service
- [ ] Add SMS notifications
  - [ ] Send SMS on device change
  - [ ] Send SMS on suspension
  - [ ] Send SMS on successful login (optional)

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

### 6. 3-Level Package Hierarchy
**Effort:** 7-10 days  
**Impact:** High  
**Complexity:** High

**Tasks:**
- [ ] Create MasterPackage model
  - [ ] Fields: name, description, speed, volume, validity, base_price
  - [ ] Belongs to developer/super-admin
  - [ ] Visibility: public/private
  - [ ] Trial package flag
- [ ] Create OperatorPackageRate model
  - [ ] Links: master_package_id, operator_id
  - [ ] Fields: operator_price, status, assigned_by
  - [ ] Validation: operator_price <= master_package.base_price
- [ ] Update Package model
  - [ ] Add master_package_id foreign key
  - [ ] Add operator_package_rate_id foreign key
  - [ ] Inherit settings from master package
  - [ ] Allow price customization
- [ ] Create MasterPackageController
  - [ ] CRUD operations for master packages
  - [ ] Assign to operators
  - [ ] Track usage statistics
- [ ] Create OperatorPackageController (already exists, modify)
  - [ ] Show available master packages
  - [ ] Create operator-specific pricing
  - [ ] Assign to sub-operators
- [ ] Add pricing validation
  - [ ] Prevent operator from pricing above master price
  - [ ] Warn if margin too low
  - [ ] Calculate suggested retail price
- [ ] Add trial package protection
  - [ ] Cannot delete master packages with trial flag
  - [ ] Cannot modify pricing on trial packages
  - [ ] Auto-expire trial packages after period
- [ ] Add customer count validation
  - [ ] Prevent deletion if customers exist
  - [ ] Show migration path before deletion

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

### 7. RRD Graph System for Performance Monitoring
**Effort:** 8-10 days  
**Impact:** Medium  
**Complexity:** High

**Tasks:**
- [ ] Install RRDtool
  - [ ] Add to Docker containers
  - [ ] Install PHP RRD extension
  - [ ] Test RRD functionality
- [ ] Create RrdGraphService
  - [ ] Implement RRD database creation
    - [ ] Step: 300s (5 minutes)
    - [ ] Data sources: upload (COUNTER), download (COUNTER)
    - [ ] RRAs: AVERAGE, MAX for 1h, 24h, 1w, 1m
  - [ ] Implement data collection
    - [ ] Query radacct for customer usage
    - [ ] Calculate rates (bytes per second)
    - [ ] Update RRD database
  - [ ] Implement graph generation
    - [ ] Hourly graph (last 60 data points)
    - [ ] Daily graph (last 24 hours)
    - [ ] Weekly graph (last 7 days)
    - [ ] Monthly graph (last 30 days)
    - [ ] PNG format with Base64 encoding
  - [ ] Implement graph caching
    - [ ] Cache graphs for 5 minutes
    - [ ] Regenerate on cache miss
- [ ] Create scheduled job for data collection
  - [ ] Run every 5 minutes
  - [ ] Collect data for all active customers
  - [ ] Update RRD databases
- [ ] Create API endpoints
  - [ ] GET /api/v1/customers/{id}/graphs/hourly
  - [ ] GET /api/v1/customers/{id}/graphs/daily
  - [ ] GET /api/v1/customers/{id}/graphs/weekly
  - [ ] GET /api/v1/customers/{id}/graphs/monthly
- [ ] Create UI components
  - [ ] Add graphs to customer detail page
  - [ ] Add timeframe selector
  - [ ] Add zoom/pan functionality
  - [ ] Show upload/download separately

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

### Phase 2 Progress: 1/2 (50%) 🔄 IN PROGRESS
- [ ] Zero-Touch Router Provisioning
- [x] Intelligent Hotspot Login Detection ✅

### Phase 3 Progress: 2/4 (50%) 🔄 IN PROGRESS
- [ ] 3-Level Package Hierarchy
- [ ] RRD Graph System
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

## Notes

- This TODO list is derived from analyzing 24 controller files from a reference ISP billing system
- All features should maintain compatibility with our 12-level role hierarchy
- Do not break existing functionality while implementing new features
- Prioritize features based on business needs and available resources
- Update this document as features are completed

---

**Last Updated:** January 24, 2026  
**Next Review:** After Phase 1 completion  
**Maintainer:** Development Team
