# Router + RADIUS Implementation TODO List

**Based on:** IspBills ISP Billing System Study  
**Repository:** i4edubd/ispsolution  
**Status:** Planning Phase

---

## Priority Legend
- 🔴 **Critical** - Core functionality, blocks other features
- 🟡 **High** - Important for production readiness
- 🟢 **Medium** - Enhances user experience
- 🔵 **Low** - Nice to have, can be deferred

---

## Phase 1: Database & Models (Week 1)

### 1.1 NAS Table for RADIUS 🔴
- [ ] Create migration: `create_nas_table.php`
  ```php
  Schema::create('nas', function (Blueprint $table) {
      $table->id();
      $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
      $table->string('nasname', 128); // Router IP
      $table->string('shortname', 32);
      $table->string('type', 30)->default('other');
      $table->integer('ports')->nullable();
      $table->string('secret', 60); // RADIUS shared secret
      $table->string('server', 64)->nullable(); // RADIUS server IP
      $table->string('community', 50)->nullable();
      $table->string('description', 200)->nullable();
      $table->timestamps();
      $table->unique(['nasname', 'tenant_id']);
      $table->index('tenant_id');
  });
  ```

### 1.2 Enhance MikrotikRouter Table 🔴
- [ ] Create migration: `add_nas_fields_to_mikrotik_routers.php`
  ```php
  Schema::table('mikrotik_routers', function (Blueprint $table) {
      $table->foreignId('nas_id')->nullable()->after('id')
            ->constrained('nas')->nullOnDelete();
      $table->string('radius_secret', 255)->nullable()->after('password');
      $table->string('public_ip', 45)->nullable()->after('ip_address');
      $table->enum('primary_auth', ['radius', 'router', 'hybrid'])
            ->default('hybrid')->after('status');
  });
  ```

### 1.3 Create Nas Model 🔴
- [ ] Create `app/Models/Nas.php`
  - Add BelongsToTenant trait
  - Define relationships: belongsTo(Tenant), hasMany(MikrotikRouter)
  - Add encrypted casting for 'secret' field
  - Add fillable fields

### 1.4 Update MikrotikRouter Model 🔴
- [ ] Add new relationships to `app/Models/MikrotikRouter.php`
  - `belongsTo(Nas::class, 'nas_id')`
- [ ] Add new fillable fields: nas_id, radius_secret, public_ip, primary_auth
- [ ] Add encrypted casting for radius_secret

### 1.5 Create RouterConfigurationBackup Model 🟡
- [ ] Create migration: `create_router_configuration_backups_table.php`
  ```php
  Schema::create('router_configuration_backups', function (Blueprint $table) {
      $table->id();
      $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
      $table->foreignId('router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->enum('backup_type', ['manual', 'pre_change', 'scheduled', 'pre_import']);
      $table->string('backup_name');
      $table->text('backup_reason')->nullable();
      $table->text('backup_data')->nullable(); // Store export content if retrieved
      $table->timestamps();
      $table->index(['router_id', 'created_at']);
  });
  ```
- [ ] Create model `app/Models/RouterConfigurationBackup.php`

---

## Phase 2: Core Services (Week 1-2)

### 2.1 Enhance MikrotikApiService 🔴
- [ ] Add to `app/Services/MikrotikApiService.php` or create wrapper:
  - [ ] `getMktRows(string $menu, array $query = []): array`
  - [ ] `addMktRows(string $menu, array $rows): bool`
  - [ ] `editMktRow(string $menu, array $row, array $data): bool`
  - [ ] `removeMktRows(string $menu, array $rows): bool`
  - [ ] `ttyWrite(string $command, array $params = []): mixed`
  
  **Note:** These may need to wrap existing HTTP API calls or implement RouterOS API protocol

### 2.2 Create RouterCommentHelper 🟡
- [ ] Create `app/Helpers/RouterCommentHelper.php`
  - [ ] `buildUserComment(NetworkUser $user): string`
  - [ ] `parseComment(string $comment): array`
  - [ ] `sanitize(string $value): string`
  - [ ] `updateRouterComment(NetworkUser $user, MikrotikRouter $router, $api): bool`

### 2.3 Create RouterConfigurationService 🔴
- [ ] Create `app/Services/RouterConfigurationService.php`
  - [ ] `configureRadius(MikrotikRouter $router): array`
  - [ ] `configureRadiusClient($api, MikrotikRouter $router): void`
  - [ ] `configurePppAaa($api): void`
  - [ ] `configureRadiusIncoming($api): void`
  - [ ] `updatePppProfiles($api, MikrotikRouter $router): void`

### 2.4 Enhance MikrotikImportService 🔴
- [ ] Add/enhance methods in `app/Services/MikrotikImportService.php`:
  - [ ] `importIpPools(MikrotikRouter $router, int $userId): array`
  - [ ] `importPppProfiles(MikrotikRouter $router, int $userId): array`
  - [ ] `importPppSecrets(MikrotikRouter $router, int $userId, bool $includeDisabled): array`
  - [ ] `parseIpPoolRanges(string $ranges): string`

### 2.5 Enhance RouterProvisioningService 🔴
- [ ] Add/enhance methods in `app/Services/RouterProvisioningService.php`:
  - [ ] `provisionUser(NetworkUser $user, MikrotikRouter $router): bool`
  - [ ] `ensureProfileExists(MikrotikRouter $router, MikrotikProfile $profile): void`
  - [ ] `createProfileOnRouter(MikrotikRouter $router, MikrotikProfile $profile): void`
  - [ ] `getProfileForPackage(Package $package, MikrotikRouter $router): ?MikrotikProfile`
  - [ ] `buildCustomerComment(NetworkUser $user): string` (or use helper)
  - [ ] `deprovisionUser(NetworkUser $user, MikrotikRouter $router, bool $delete): bool`

### 2.6 Create RouterRadiusFailoverService 🟡
- [ ] Create `app/Services/RouterRadiusFailoverService.php`
  - [ ] `configureFailover(MikrotikRouter $router): bool`
  - [ ] `switchToRadiusMode(MikrotikRouter $router): bool`
  - [ ] `switchToRouterMode(MikrotikRouter $router): bool`
  - [ ] `getRadiusStatus(MikrotikRouter $router): array`

### 2.7 Create RouterBackupService 🟡
- [ ] Create `app/Services/RouterBackupService.php`
  - [ ] `createPreChangeBackup(MikrotikRouter $router, string $reason): ?RouterConfigurationBackup`
  - [ ] `backupPppSecrets(MikrotikRouter $router): ?string`
  - [ ] `mirrorCustomersToRouter(MikrotikRouter $router): array`
  - [ ] `restoreFromBackup(MikrotikRouter $router, string $backupName): bool`
  - [ ] `listBackups(MikrotikRouter $router): Collection`

---

## Phase 3: Controllers & Routes (Week 2)

### 3.1 Create NasController 🔴
- [ ] Create `app/Http/Controllers/Panel/NasController.php`
  - [ ] `index()` - List all NAS devices
  - [ ] `create()` - Show create form
  - [ ] `store(Request $request)` - Create NAS with router connectivity test
  - [ ] `edit(Nas $nas)` - Show edit form
  - [ ] `update(Request $request, Nas $nas)` - Update NAS
  - [ ] `destroy(Nas $nas)` - Delete NAS
  - [ ] `testConnection(Request $request)` - AJAX connectivity test

### 3.2 Create/Enhance RouterConfigurationController 🔴
- [ ] Create `app/Http/Controllers/Panel/RouterConfigurationController.php`
  - [ ] `index(MikrotikRouter $router)` - Show configuration dashboard
  - [ ] `configureRadius(MikrotikRouter $router)` - Configure RADIUS
  - [ ] `configurePpp(MikrotikRouter $router)` - Configure PPP
  - [ ] `configureFirewall(MikrotikRouter $router)` - Configure firewall
  - [ ] `configureAll(MikrotikRouter $router)` - One-click full config
  - [ ] `showConfiguration(MikrotikRouter $router)` - View current config

### 3.3 Enhance MikrotikImportController 🟡
- [ ] Add methods to existing controller or create new:
  - [ ] `importForm(MikrotikRouter $router)` - Show import form
  - [ ] `importPools(Request $request, MikrotikRouter $router)` - Import IP pools
  - [ ] `importProfiles(Request $request, MikrotikRouter $router)` - Import profiles
  - [ ] `importSecrets(Request $request, MikrotikRouter $router)` - Import secrets
  - [ ] `importAll(Request $request, MikrotikRouter $router)` - Import everything

### 3.4 Create RouterBackupController 🟡
- [ ] Create `app/Http/Controllers/Panel/RouterBackupController.php`
  - [ ] `index(MikrotikRouter $router)` - List backups
  - [ ] `create(Request $request, MikrotikRouter $router)` - Create backup
  - [ ] `restore(RouterConfigurationBackup $backup)` - Restore backup
  - [ ] `download(RouterConfigurationBackup $backup)` - Download backup file
  - [ ] `destroy(RouterConfigurationBackup $backup)` - Delete backup

### 3.5 Create RouterFailoverController 🟡
- [ ] Create `app/Http/Controllers/Panel/RouterFailoverController.php`
  - [ ] `configure(MikrotikRouter $router)` - Configure Netwatch
  - [ ] `switchMode(Request $request, MikrotikRouter $router)` - Switch auth mode
  - [ ] `status(MikrotikRouter $router)` - Get RADIUS/failover status

### 3.6 Add Routes 🔴
- [ ] Add to `routes/web.php` in admin panel group:
  ```php
  // NAS Management
  Route::resource('nas', NasController::class);
  Route::post('nas/test-connection', [NasController::class, 'testConnection']);
  
  // Router Configuration
  Route::prefix('routers/{router}')->group(function () {
      Route::get('configure', [RouterConfigurationController::class, 'index']);
      Route::post('configure/radius', [RouterConfigurationController::class, 'configureRadius']);
      Route::post('configure/ppp', [RouterConfigurationController::class, 'configurePpp']);
      Route::post('configure/all', [RouterConfigurationController::class, 'configureAll']);
      
      // Import
      Route::get('import', [MikrotikImportController::class, 'importForm']);
      Route::post('import/pools', [MikrotikImportController::class, 'importPools']);
      Route::post('import/profiles', [MikrotikImportController::class, 'importProfiles']);
      Route::post('import/secrets', [MikrotikImportController::class, 'importSecrets']);
      Route::post('import/all', [MikrotikImportController::class, 'importAll']);
      
      // Backup
      Route::get('backups', [RouterBackupController::class, 'index']);
      Route::post('backups', [RouterBackupController::class, 'create']);
      Route::post('backups/{backup}/restore', [RouterBackupController::class, 'restore']);
      
      // Failover
      Route::post('failover/configure', [RouterFailoverController::class, 'configure']);
      Route::post('failover/switch', [RouterFailoverController::class, 'switchMode']);
      Route::get('failover/status', [RouterFailoverController::class, 'status']);
  });
  ```

---

## Phase 4: Console Commands (Week 2)

### 4.1 Create RouterConfigureCommand 🟡
- [ ] Create `app/Console/Commands/RouterConfigureCommand.php`
  ```php
  php artisan router:configure {router} --radius --ppp --firewall --all
  ```

### 4.2 Enhance Import Commands 🟡
- [ ] Ensure existing commands work with new service methods:
  - `php artisan mikrotik:import-pools {router}`
  - `php artisan mikrotik:import-profiles {router}`
  - `php artisan mikrotik:import-secrets {router}`
  - `php artisan mikrotik:sync-all {router}`

### 4.3 Create Backup Command 🟢
- [ ] Create `app/Console/Commands/RouterBackupCommand.php`
  ```php
  php artisan router:backup {router} --type=manual
  ```

### 4.4 Create Failover Command 🟢
- [ ] Create `app/Console/Commands/RouterFailoverCommand.php`
  ```php
  php artisan router:failover {router} --mode=radius|router
  php artisan router:failover {router} --configure
  ```

### 4.5 Create Mirror Command 🟢
- [ ] Create `app/Console/Commands/RouterMirrorUsersCommand.php`
  ```php
  php artisan router:mirror-users {router}
  ```

---

## Phase 5: Jobs & Queues (Week 3)

### 5.1 Create ProvisionUserJob 🟡
- [ ] Create `app/Jobs/ProvisionUserJob.php`
  - Handles provisioning user to router asynchronously
  - Used when user is created/updated

### 5.2 Create ImportRouterDataJob 🟡
- [ ] Create `app/Jobs/ImportRouterDataJob.php`
  - Handles bulk import in background
  - Reports progress via events

### 5.3 Create BackupRouterJob 🟢
- [ ] Create `app/Jobs/BackupRouterJob.php`
  - Scheduled backup creation
  - Can be run nightly via scheduler

### 5.4 Create MirrorUsersJob 🟢
- [ ] Create `app/Jobs/MirrorUsersJob.php`
  - Periodic sync of users to router
  - Run via scheduler for failover readiness

---

## Phase 6: UI Development (Week 3-4)

### 6.1 NAS Management UI 🔴

#### Create View
- [ ] Create `resources/views/panels/admin/nas/index.blade.php`
  - Table showing all NAS devices
  - Columns: Name, IP, Type, Secret (masked), Status, Actions
  - Add/Edit/Delete buttons
  
- [ ] Create `resources/views/panels/admin/nas/create.blade.php`
  - Form with fields:
    - NAS Name (shortname)
    - IP Address (nasname)
    - Type (dropdown)
    - RADIUS Shared Secret (generate button)
    - RADIUS Server IP (pre-filled from config)
    - Description
  - Test Connection button (AJAX)
  
- [ ] Create `resources/views/panels/admin/nas/edit.blade.php`
  - Same as create but pre-filled

### 6.2 Enhanced Router Creation Form 🔴
- [ ] Update `resources/views/panels/admin/network/routers-create.blade.php`
  - Add RADIUS Configuration section:
    - RADIUS Shared Secret (with generator)
    - Public IP Address
    - RADIUS Server IP (readonly from config)
    - Primary Authentication Mode (dropdown: radius/router/hybrid)
  - Add connectivity test before submit

### 6.3 Router Configuration Dashboard 🔴
- [ ] Create `resources/views/panels/admin/network/router-configure.blade.php`
  - Status cards:
    - Router Status (online/offline)
    - RADIUS Status (configured/not configured)
    - Failover Status (active/inactive)
    - Last Configuration (timestamp)
  - Action buttons:
    - Configure RADIUS (one-click)
    - Configure PPP
    - Configure Firewall
    - Configure All
  - Configuration log (last 10 actions)

### 6.4 Import Interface 🟡
- [ ] Create `resources/views/panels/admin/network/router-import.blade.php`
  - Import type selector (radio buttons):
    - IP Pools
    - PPP Profiles
    - PPP Secrets
    - All
  - Options:
    - Include disabled users (checkbox for secrets)
    - Create backup before import (checkbox, checked by default)
  - Progress bar (show during import)
  - Results summary:
    - Items imported
    - Items updated
    - Errors
    - Backup file name

### 6.5 Backup Management UI 🟡
- [ ] Create `resources/views/panels/admin/network/router-backups.blade.php`
  - Table of backups:
    - Backup Name
    - Type (badge)
    - Reason
    - Created At
    - Created By
    - Actions (Restore, Download, Delete)
  - Create Backup button
  - Filter by backup type

### 6.6 Provisioning Status Component 🟢
- [ ] Create `resources/views/panels/admin/customers/components/provisioning-status.blade.php`
  - Display in user detail page
  - Show:
    - Provisioned: Yes/No (badge)
    - Router: Name and IP
    - Profile: Profile name
    - IP Address: Static or pool
    - Last Provisioned: Timestamp
    - Router Comment: Parsed metadata
  - Actions:
    - Provision Now (button)
    - Update on Router (button)
    - Remove from Router (button)

### 6.7 Failover Status Display 🟢
- [ ] Create `resources/views/panels/admin/network/components/failover-status.blade.php`
  - Show in router dashboard
  - Display:
    - Current Mode (RADIUS/Router/Hybrid) with icon
    - RADIUS Health (Up/Down with timestamp)
    - Netwatch Status (Configured/Not configured)
    - Last Failover Event (if any)
  - Actions:
    - Configure Failover (button)
    - Switch to RADIUS Mode (button)
    - Switch to Router Mode (button)
    - Test RADIUS Connection (button)

---

## Phase 7: Configuration Files (Week 2)

### 7.1 Update config/radius.php 🔴
- [ ] Add to `config/radius.php`:
  ```php
  'server_ip' => env('RADIUS_SERVER_IP', '127.0.0.1'),
  'authentication_port' => env('RADIUS_AUTH_PORT', 1812),
  'accounting_port' => env('RADIUS_ACCT_PORT', 1813),
  'interim_update' => env('RADIUS_INTERIM_UPDATE', '5m'),
  'primary_authenticator' => env('RADIUS_PRIMARY_AUTH', 'hybrid'),
  'netwatch' => [
      'enabled' => env('RADIUS_NETWATCH_ENABLED', true),
      'interval' => env('RADIUS_NETWATCH_INTERVAL', '1m'),
      'timeout' => env('RADIUS_NETWATCH_TIMEOUT', '1s'),
  ],
  ```

### 7.2 Update config/mikrotik.php 🟡
- [ ] Add to `config/mikrotik.php`:
  ```php
  'ppp_local_address' => env('MIKROTIK_PPP_LOCAL_ADDRESS', '10.0.0.1'),
  'backup' => [
      'auto_backup_before_change' => env('MIKROTIK_AUTO_BACKUP', true),
      'retention_days' => env('MIKROTIK_BACKUP_RETENTION_DAYS', 30),
  ],
  'provisioning' => [
      'auto_provision_on_create' => env('MIKROTIK_AUTO_PROVISION', true),
      'update_on_password_change' => env('MIKROTIK_UPDATE_ON_PASSWORD_CHANGE', true),
  ],
  ```

### 7.3 Update .env.example 🔴
- [ ] Add to `.env.example`:
  ```
  # RADIUS Configuration
  RADIUS_SERVER_IP=127.0.0.1
  RADIUS_AUTH_PORT=1812
  RADIUS_ACCT_PORT=1813
  RADIUS_INTERIM_UPDATE=5m
  RADIUS_PRIMARY_AUTH=hybrid
  RADIUS_NETWATCH_ENABLED=true
  
  # MikroTik Configuration
  MIKROTIK_PPP_LOCAL_ADDRESS=10.0.0.1
  MIKROTIK_AUTO_BACKUP=true
  MIKROTIK_AUTO_PROVISION=true
  ```

---

## Phase 8: Policies & Permissions (Week 3)

### 8.1 Create NasPolicy 🟡
- [ ] Create `app/Policies/NasPolicy.php`
  - viewAny, view, create, update, delete
  - Admin and manager can manage
  - Tenant isolation

### 8.2 Update RouterPolicy 🟡
- [ ] Add methods to `app/Policies/MikrotikRouterPolicy.php`:
  - configure (can configure router)
  - backup (can create backups)
  - restore (can restore backups)
  - provision (can provision users)

### 8.3 Register Policies 🟡
- [ ] Update `app/Providers/AuthServiceProvider.php`
  - Register NasPolicy

---

## Phase 9: Events & Listeners (Week 3)

### 9.1 Create Events 🟢
- [ ] Create `app/Events/UserProvisioned.php`
- [ ] Create `app/Events/RouterConfigured.php`
- [ ] Create `app/Events/BackupCreated.php`
- [ ] Create `app/Events/FailoverTriggered.php`

### 9.2 Create Listeners 🟢
- [ ] Create `app/Listeners/ProvisionUserAfterCreation.php`
  - Listen to UserCreated event
  - Dispatch ProvisionUserJob
  
- [ ] Create `app/Listeners/UpdateRouterOnPasswordChange.php`
  - Listen to PasswordChanged event
  - Update PPP secret on router

### 9.3 Register Event Listeners 🟢
- [ ] Update `app/Providers/EventServiceProvider.php`

---

## Phase 10: Testing (Week 4)

### 10.1 Unit Tests 🟡
- [ ] Test `RouterConfigurationService`
  - `test_configure_radius_success()`
  - `test_configure_radius_connection_failure()`
  - `test_configure_ppp_aaa()`
  
- [ ] Test `MikrotikImportService`
  - `test_import_ip_pools()`
  - `test_import_ppp_profiles()`
  - `test_import_ppp_secrets_with_backup()`
  
- [ ] Test `RouterProvisioningService`
  - `test_provision_user_creates_secret()`
  - `test_provision_user_updates_existing()`
  - `test_ensure_profile_exists_creates_profile()`
  - `test_deprovision_user()`
  
- [ ] Test `RouterBackupService`
  - `test_create_pre_change_backup()`
  - `test_mirror_customers_to_router()`
  - `test_restore_from_backup()`

### 10.2 Feature Tests 🟡
- [ ] Test `NasController`
  - `test_admin_can_create_nas()`
  - `test_nas_requires_connectivity()`
  - `test_tenant_isolation()`
  
- [ ] Test `RouterConfigurationController`
  - `test_configure_radius_flow()`
  - `test_configuration_creates_backup()`
  
- [ ] Test `MikrotikImportController`
  - `test_import_creates_backup()`
  - `test_import_replaces_existing_data()`

### 10.3 Integration Tests 🟢
- [ ] Test complete provisioning flow:
  - Create router → Configure RADIUS → Import data → Provision user
  
- [ ] Test failover flow:
  - Configure Netwatch → Simulate RADIUS down → Verify fallback

---

## Phase 11: Documentation (Week 4)

### 11.1 Update Existing Docs 🟡
- [ ] Update `ROUTER_PROVISIONING_GUIDE.md`
  - Add RADIUS configuration steps
  - Add failover setup
  
- [ ] Update `RADIUS_SETUP_GUIDE.md`
  - Add NAS configuration
  - Add router-side setup
  
- [ ] Update `MIKROTIK_QUICKSTART.md`
  - Add import/sync examples
  - Add provisioning workflow

### 11.2 Create New Docs 🟡
- [ ] Create `ROUTER_RADIUS_FAILOVER.md`
  - Explain hybrid authentication
  - Netwatch configuration
  - Manual mode switching
  
- [ ] Create `ROUTER_BACKUP_RESTORE.md`
  - Backup strategies
  - Restore procedures
  - Scheduled backups

### 11.3 API Documentation 🟢
- [ ] Update `docs/API.md`
  - Add NAS endpoints
  - Add router configuration endpoints
  - Add import/export endpoints

### 11.4 User Guide 🟢
- [ ] Create video/screenshots for:
  - Adding a router with RADIUS
  - Importing configuration
  - Provisioning users
  - Managing backups

---

## Phase 12: Security & Performance (Week 4)

### 12.1 Security Audit 🟡
- [ ] Review all password/secret handling
- [ ] Ensure encrypted storage for sensitive data
- [ ] Implement CSRF protection on all forms
- [ ] Add rate limiting to configuration endpoints
- [ ] Implement audit logging for all changes
- [ ] Review authorization checks in all controllers

### 12.2 Performance Optimization 🟢
- [ ] Add caching for router connection objects
- [ ] Optimize bulk operations (batch API calls)
- [ ] Add database indexes for new tables
- [ ] Implement queue-based provisioning
- [ ] Add progress tracking for long operations

### 12.3 Error Handling 🟡
- [ ] Add comprehensive try-catch blocks
- [ ] Implement automatic rollback on failures
- [ ] Add user-friendly error messages
- [ ] Implement retry logic for network operations
- [ ] Add health checks before critical operations

---

## Phase 13: Additional Features (Future)

### 13.1 Advanced Monitoring 🔵
- [ ] Real-time RADIUS status monitoring
- [ ] Router health dashboard
- [ ] Failover event history
- [ ] Configuration change history with diff view

### 13.2 Bulk Operations 🔵
- [ ] Bulk user provisioning
- [ ] Multi-router configuration
- [ ] Scheduled configuration templates

### 13.3 Automation 🔵
- [ ] Auto-provision on user creation (with toggle)
- [ ] Auto-update on package change
- [ ] Scheduled backups
- [ ] Automatic failover testing

---

## Dependencies & Prerequisites

### Required Packages
- ✅ Laravel 11.x
- ✅ PHP 8.2+
- ✅ MySQL/MariaDB
- ⚠️ RouterOS API library (may need to implement or use package)

### Configuration Required
- [ ] RADIUS server must be set up (see RADIUS_SETUP_GUIDE.md)
- [ ] MikroTik routers must have API enabled
- [ ] Network connectivity between app and routers
- [ ] Network connectivity between routers and RADIUS server

---

## Testing Checklist

### Manual Testing
- [ ] Create router with RADIUS configuration
- [ ] Test connectivity to router
- [ ] Import IP pools, profiles, and secrets
- [ ] Provision a test user
- [ ] Verify user can connect via PPPoE
- [ ] Test RADIUS authentication
- [ ] Simulate RADIUS failure (failover test)
- [ ] Create backup and restore
- [ ] Switch between authentication modes
- [ ] Test with multiple tenants (isolation)

### Automated Testing
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Integration tests pass
- [ ] Security tests pass

---

## Rollout Plan

### Stage 1: Development Environment
- [ ] Set up dev router
- [ ] Set up dev RADIUS server
- [ ] Implement core features
- [ ] Test with sample data

### Stage 2: Staging Environment
- [ ] Deploy to staging
- [ ] Import production-like data
- [ ] Performance testing
- [ ] Security testing
- [ ] User acceptance testing

### Stage 3: Production Rollout
- [ ] Deploy database migrations
- [ ] Deploy code changes
- [ ] Configure existing routers (one by one)
- [ ] Monitor for issues
- [ ] Gather user feedback

---

## Success Criteria

### Functional
- ✓ Admins can add routers with RADIUS configuration
- ✓ System can import existing router data
- ✓ Users are automatically provisioned to routers
- ✓ Hybrid authentication works with automatic failover
- ✓ Backups are created before changes
- ✓ Configuration can be restored from backups

### Non-Functional
- ✓ Provisioning completes in <5 seconds
- ✓ Import operations handle 1000+ items
- ✓ UI is responsive and user-friendly
- ✓ All operations are logged for audit
- ✓ System handles router connectivity issues gracefully
- ✓ Multi-tenant isolation is enforced

---

## Notes

### Breaking Changes
- None expected - this is additive functionality

### Migration Strategy
- Existing routers can continue working without RADIUS
- RADIUS features are opt-in
- Existing provisioning flow remains unchanged

### Rollback Plan
- Keep backups of all router configurations
- Document rollback procedures
- Test rollback before production deployment

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-26  
**Status:** Planning Complete, Ready for Implementation  
**Estimated Timeline:** 4-8 weeks for full implementation
