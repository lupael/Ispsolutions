# Router + RADIUS (MikroTik) — Developer Notes

**Study Reference:** IspBills ISP Billing System  
**Purpose:** Implementation guide for Router + RADIUS integration features  
**Repository:** i4edubd/ispsolution

---

## Table of Contents

1. [Overview](#overview)
2. [Router vs RADIUS Responsibilities](#router-vs-radius-responsibilities)
3. [Admin Adds Router (NAS)](#admin-adds-router-nas)
4. [Configure Router (Push Config to MikroTik)](#configure-router-push-config-to-mikrotik)
5. [Import from MikroTik to MySQL](#import-from-mikrotik-to-mysql)
6. [Sync/Provisioning to Router](#syncprovisioning-to-router)
7. [Customer Metadata Comments](#customer-metadata-comments)
8. [Authentication Choice: Router vs RADIUS](#authentication-choice-router-vs-radius)
9. [Backup Strategies](#backup-strategies)
10. [Implementation Roadmap](#implementation-roadmap)
11. [Code Examples](#code-examples)
12. [UI Development Tasks](#ui-development-tasks)

---

## Overview

This document provides comprehensive developer notes for implementing advanced Router + RADIUS (MikroTik) features in the ISP Solution platform. The implementation is based on patterns observed in the IspBills billing system, adapted to our existing architecture.

### Key Features to Implement

1. **Router Configuration Automation**: Push complete RADIUS/PPP/Firewall configs to routers
2. **Bidirectional Sync**: Import profiles, pools, and secrets from routers; push updates back
3. **Flexible Authentication**: Support both Router-based and RADIUS-based authentication with automatic fallback
4. **Customer Metadata**: Embed customer information into router objects for troubleshooting
5. **Backup & Recovery**: Automated backups before changes, with rollback capability
6. **Zero-Touch Provisioning**: One-click router setup with all necessary configurations

---

## Router vs RADIUS Responsibilities

### MikroTik Router
- **Primary Role**: PPP endpoint (PPPoE), session enforcement
- **Capabilities**:
  - Manages PPPoE/PPTP/L2TP sessions
  - Applies profiles (speed limits, IP pools, firewall rules)
  - Can authenticate locally via `/ppp/secret`
  - Can proxy authentication to RADIUS server
  - Enforces bandwidth queues and firewall policies

### FreeRADIUS Server
- **Primary Role**: Central AAA (Authentication, Authorization, Accounting)
- **Capabilities**:
  - Centralized authentication database
  - Returns authorization attributes (e.g., `Framed-IP-Address`, `Mikrotik-Rate-Limit`)
  - Collects accounting data (session start/stop, data usage)
  - Supports multiple NAS (routers) with shared secret authentication
  - Enables centralized user management across multiple routers

### Integration Pattern

In our system, the router is configured to use RADIUS (`/ppp/aaa use-radius=yes`) but the application can maintain local secrets for:
- Fallback authentication when RADIUS is unavailable
- Development/testing environments
- Specific scenarios requiring router-only authentication

---

## Admin Adds Router (NAS)

### Current Implementation Status

**Existing:**
- ✅ MikrotikRouter model with encrypted credentials
- ✅ Basic connectivity testing
- ✅ Router CRUD operations
- ✅ API port configuration

**To Add:**
- 🔲 NAS (Network Access Server) table for RADIUS
- 🔲 RADIUS shared secret management
- 🔲 Public IP configuration for RADIUS callback
- 🔲 Enhanced connectivity validation

### Database Schema Enhancement

**New Table: `nas` (for FreeRADIUS)**
```sql
CREATE TABLE nas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    nasname VARCHAR(128) NOT NULL,  -- Router IP
    shortname VARCHAR(32) NOT NULL,  -- Router name
    type VARCHAR(30) DEFAULT 'other',
    ports INT,
    secret VARCHAR(60) NOT NULL,  -- RADIUS shared secret
    server VARCHAR(64),  -- RADIUS server IP
    community VARCHAR(50),
    description VARCHAR(200),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY nasname (nasname, tenant_id)
);
```

**Enhanced: `mikrotik_routers` table**
```sql
ALTER TABLE mikrotik_routers 
ADD COLUMN nas_id INT AFTER id,
ADD COLUMN radius_secret VARCHAR(255),
ADD COLUMN public_ip VARCHAR(45),
ADD FOREIGN KEY (nas_id) REFERENCES nas(id);
```

### Implementation Pattern (from IspBills)

```php
// File: app/Http/Controllers/Freeradius/NasController.php

public function store(Request $request)
{
    // Validate router connectivity first
    $api = new RouterosAPI([
        'host' => $request->nasname,
        'user' => $request->api_username,
        'pass' => $request->api_password,
        'port' => $request->api_port,
        'attempts' => 1,
        'debug' => false,
    ]);

    if (!$api->connect($request->nasname, $request->api_username, $request->api_password)) {
        return back()->with('error', 'Cannot connect to router! Check API credentials and network connectivity.');
    }

    DB::beginTransaction();
    try {
        // Create NAS entry for RADIUS
        $nas = Nas::create([
            'tenant_id' => auth()->user()->tenant_id,
            'nasname' => $request->nasname,
            'shortname' => $request->shortname,
            'type' => $request->type ?? 'other',
            'secret' => $request->secret,  // RADIUS shared secret
            'server' => config('radius.server_ip'),
            'description' => $request->description,
        ]);

        // Create MikroTik router entry
        $router = MikrotikRouter::create([
            'tenant_id' => auth()->user()->tenant_id,
            'nas_id' => $nas->id,
            'name' => $request->shortname,
            'ip_address' => $request->nasname,
            'host' => $request->nasname,
            'api_port' => $request->api_port,
            'username' => $request->api_username,
            'password' => $request->api_password,
            'radius_secret' => $request->secret,
            'public_ip' => $request->public_ip,
            'status' => 'active',
        ]);

        DB::commit();

        return redirect()->route('admin.routers.index')
            ->with('success', 'Router added successfully. You can now configure it.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create router: ' . $e->getMessage());
        return back()->with('error', 'Failed to create router: ' . $e->getMessage());
    }
}
```

### UI Components

**View: `resources/views/panels/admin/network/routers-create.blade.php`**

Add fields:
- RADIUS Secret (shared secret between router and RADIUS server)
- Public IP (for RADIUS server to identify this NAS)
- RADIUS Server IP (configurable, defaults from config)

---

## Configure Router (Push Config to MikroTik)

### Configuration Areas

1. **RADIUS Client Configuration**: Configure router to authenticate via RADIUS
2. **PPP AAA Settings**: Enable RADIUS usage and accounting
3. **RADIUS Incoming**: Allow RADIUS Change of Authorization (CoA)
4. **PPP Profiles**: Set local address and on-up/on-down scripts
5. **Netwatch**: Monitor RADIUS health and automatic fallback

### Implementation Pattern

**File: `app/Http/Controllers/RouterConfigurationController.php`**

```php
use App\Services\MikrotikApiService;

public function configureRadius(MikrotikRouter $router)
{
    $api = app(MikrotikApiService::class);
    
    if (!$api->connect($router)) {
        throw new \Exception('Cannot connect to router');
    }

    try {
        DB::beginTransaction();

        // 1. Configure RADIUS client
        $this->configureRadiusClient($api, $router);

        // 2. Enable PPP AAA to use RADIUS
        $this->configurePppAaa($api);

        // 3. Enable RADIUS incoming (CoA)
        $this->configureRadiusIncoming($api);

        // 4. Update PPP profiles
        $this->updatePppProfiles($api, $router);

        // 5. Configure Netwatch for automatic fallback
        $this->configureNetwatch($api, $router);

        // Log configuration
        RouterConfiguration::create([
            'router_id' => $router->id,
            'tenant_id' => $router->tenant_id,
            'configuration_type' => 'radius_setup',
            'configuration_data' => json_encode([
                'radius_server' => config('radius.server_ip'),
                'configured_at' => now(),
            ]),
            'status' => 'success',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Router configured for RADIUS successfully',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('RADIUS configuration failed', [
            'router_id' => $router->id,
            'error' => $e->getMessage(),
        ]);
        throw $e;
    }
}

private function configureRadiusClient($api, $router)
{
    $radiusServer = config('radius.server_ip');
    
    // Define RADIUS client configuration
    $rows = [[
        'accounting-port' => config('radius.accounting_port', 1813),
        'address' => $radiusServer,
        'authentication-port' => config('radius.authentication_port', 1812),
        'secret' => $router->radius_secret,
        'service' => 'hotspot,ppp',
        'timeout' => '3s',
        'require-message-auth' => 'no',
    ]];

    // Remove existing RADIUS configurations to avoid duplicates
    $existingRows = $api->getMktRows('radius');
    if (!empty($existingRows)) {
        $api->removeMktRows('radius', $existingRows);
    }

    // Add new RADIUS client configuration
    $api->addMktRows('radius', $rows);

    Log::info('RADIUS client configured', [
        'router_id' => $router->id,
        'radius_server' => $radiusServer,
    ]);
}

private function configurePppAaa($api)
{
    // Enable RADIUS authentication and accounting for PPP
    $api->ttyWrite('/ppp/aaa/set', [
        'interim-update' => '5m',  // Send accounting updates every 5 minutes
        'use-radius' => 'yes',     // Use RADIUS for authentication
        'accounting' => 'yes',      // Enable RADIUS accounting
    ]);

    Log::info('PPP AAA configured for RADIUS');
}

private function configureRadiusIncoming($api)
{
    // Enable RADIUS Change of Authorization (CoA)
    $api->ttyWrite('/radius/incoming/set', [
        'accept' => 'yes',  // Accept RADIUS disconnect/CoA messages
    ]);

    Log::info('RADIUS incoming (CoA) enabled');
}

private function updatePppProfiles($api, $router)
{
    // Set local address for all PPP profiles
    $localAddress = config('mikrotik.ppp_local_address', '10.0.0.1');
    
    $profiles = $api->getMktRows('ppp_profile', ['default' => 'yes']);
    
    foreach ($profiles as $profile) {
        $api->editMktRow('ppp_profile', $profile, [
            'local-address' => $localAddress,
            // Add on-up/on-down scripts if needed
        ]);
    }

    Log::info('PPP profiles updated', [
        'router_id' => $router->id,
        'local_address' => $localAddress,
    ]);
}

private function configureNetwatch($api, $router)
{
    $radiusServer = config('radius.server_ip');
    
    // Configure Netwatch for RADIUS health monitoring
    $rows = [[
        'host' => $radiusServer,
        'interval' => '1m',
        'timeout' => '1s',
        // When RADIUS is UP: disable local secrets, drop non-RADIUS sessions
        'up-script' => '/ppp secret disable [find disabled=no];/ppp active remove [find radius=no];',
        // When RADIUS is DOWN: enable local secrets (fallback)
        'down-script' => '/ppp secret enable [find disabled=yes];',
        'comment' => 'radius-health-check',
    ]];

    // Remove existing netwatch for this host
    $existingRows = $api->getMktRows('tool_netwatch', ['host' => $radiusServer]);
    if (!empty($existingRows)) {
        $api->removeMktRows('tool_netwatch', $existingRows);
    }

    $api->addMktRows('tool_netwatch', $rows);

    Log::info('Netwatch configured for RADIUS failover', [
        'router_id' => $router->id,
        'radius_server' => $radiusServer,
    ]);
}
```

### Configuration Command

**File: `app/Console/Commands/RouterConfigureCommand.php`**

```php
namespace App\Console\Commands;

use App\Models\MikrotikRouter;
use App\Http\Controllers\RouterConfigurationController;
use Illuminate\Console\Command;

class RouterConfigureCommand extends Command
{
    protected $signature = 'router:configure {router : Router ID or name} 
                          {--radius : Configure RADIUS}
                          {--ppp : Configure PPP}
                          {--firewall : Configure firewall}
                          {--all : Configure all}';

    protected $description = 'Configure MikroTik router settings';

    public function handle()
    {
        $routerIdentifier = $this->argument('router');
        
        $router = is_numeric($routerIdentifier)
            ? MikrotikRouter::find($routerIdentifier)
            : MikrotikRouter::where('name', $routerIdentifier)->first();

        if (!$router) {
            $this->error('Router not found');
            return 1;
        }

        $this->info("Configuring router: {$router->name}");

        $controller = app(RouterConfigurationController::class);

        if ($this->option('radius') || $this->option('all')) {
            $this->info('Configuring RADIUS...');
            $controller->configureRadius($router);
            $this->info('✓ RADIUS configured');
        }

        // Add other configuration options...

        $this->info('Configuration completed successfully!');
        return 0;
    }
}
```

---

## Import from MikroTik to MySQL

### Import Features

1. **IP Pools**: Import all IP pools from router to database
2. **PPP Profiles**: Import PPPoE profiles with speed limits
3. **PPP Secrets**: Import existing customers with backup

### Implementation Pattern

**File: `app/Services/MikrotikImportService.php`**

```php
namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\MikrotikIpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikPppoeUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MikrotikImportService
{
    protected $apiService;

    public function __construct(MikrotikApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Import IP pools from router to database
     */
    public function importIpPools(MikrotikRouter $router, int $userId): array
    {
        if (!$this->apiService->connect($router)) {
            throw new \Exception('Cannot connect to router');
        }

        DB::beginTransaction();
        try {
            // Delete old imported pools for this router
            MikrotikIpPool::where('router_id', $router->id)
                ->where('tenant_id', $router->tenant_id)
                ->delete();

            $imported = 0;
            $ip4pools = $this->apiService->getMktRows('ip_pool');

            foreach ($ip4pools as $ip4pool) {
                $ranges = $this->parseIpPoolRanges($ip4pool['ranges'] ?? '');
                
                if (empty($ranges)) {
                    continue;
                }

                MikrotikIpPool::create([
                    'tenant_id' => $router->tenant_id,
                    'router_id' => $router->id,
                    'name' => $ip4pool['name'] ?? 'unnamed',
                    'ranges' => $ranges,
                    'imported_by' => $userId,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('IP pools imported', [
                'router_id' => $router->id,
                'count' => $imported,
            ]);

            return [
                'success' => true,
                'imported' => $imported,
                'message' => "Successfully imported {$imported} IP pools",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP pool import failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Import PPP profiles from router to database
     */
    public function importPppProfiles(MikrotikRouter $router, int $userId): array
    {
        if (!$this->apiService->connect($router)) {
            throw new \Exception('Cannot connect to router');
        }

        DB::beginTransaction();
        try {
            // Delete old imported profiles for this router
            MikrotikProfile::where('router_id', $router->id)
                ->where('tenant_id', $router->tenant_id)
                ->delete();

            $imported = 0;
            // Skip default profiles
            $pppProfiles = $this->apiService->getMktRows('ppp_profile', ['default' => 'no']);

            foreach ($pppProfiles as $pppProfile) {
                MikrotikProfile::create([
                    'tenant_id' => $router->tenant_id,
                    'router_id' => $router->id,
                    'name' => $pppProfile['name'] ?? 'Not Found',
                    'local_address' => $pppProfile['local-address'] ?? '',
                    'remote_address' => $pppProfile['remote-address'] ?? '',
                    'rate_limit' => $pppProfile['rate-limit'] ?? '',
                    'session_timeout' => $pppProfile['session-timeout'] ?? null,
                    'idle_timeout' => $pppProfile['idle-timeout'] ?? null,
                    'imported_by' => $userId,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('PPP profiles imported', [
                'router_id' => $router->id,
                'count' => $imported,
            ]);

            return [
                'success' => true,
                'imported' => $imported,
                'message' => "Successfully imported {$imported} PPP profiles",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PPP profile import failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Import PPP secrets from router with automatic backup
     */
    public function importPppSecrets(
        MikrotikRouter $router, 
        int $userId, 
        bool $includeDisabled = false
    ): array {
        if (!$this->apiService->connect($router)) {
            throw new \Exception('Cannot connect to router');
        }

        DB::beginTransaction();
        try {
            // Create router-side backup BEFORE importing
            $backupFile = 'ppp-secret-backup-by-billing-' . Carbon::now()->timestamp;
            $this->apiService->ttyWrite('/ppp/secret/export', ['file' => $backupFile]);

            Log::info('PPP secrets backup created on router', [
                'router_id' => $router->id,
                'backup_file' => $backupFile,
            ]);

            // Delete old imported secrets for this router
            MikrotikPppoeUser::where('router_id', $router->id)
                ->where('tenant_id', $router->tenant_id)
                ->delete();

            $imported = 0;
            $query = $includeDisabled ? [] : ['disabled' => 'no'];
            $secrets = $this->apiService->getMktRows('ppp_secret', $query);

            foreach ($secrets as $secret) {
                MikrotikPppoeUser::create([
                    'tenant_id' => $router->tenant_id,
                    'router_id' => $router->id,
                    'username' => $secret['name'] ?? '',
                    'password' => $secret['password'] ?? '',
                    'service' => $secret['service'] ?? 'pppoe',
                    'profile' => $secret['profile'] ?? 'default',
                    'local_address' => $secret['local-address'] ?? null,
                    'remote_address' => $secret['remote-address'] ?? null,
                    'disabled' => ($secret['disabled'] ?? 'no') === 'yes',
                    'comment' => $secret['comment'] ?? null,
                    'imported_by' => $userId,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('PPP secrets imported', [
                'router_id' => $router->id,
                'count' => $imported,
                'backup_file' => $backupFile,
            ]);

            return [
                'success' => true,
                'imported' => $imported,
                'backup_file' => $backupFile,
                'message' => "Successfully imported {$imported} PPP secrets. Backup: {$backupFile}",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PPP secrets import failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Parse IP pool ranges from MikroTik format
     */
    private function parseIpPoolRanges(string $ranges): string
    {
        if (empty($ranges)) {
            return '';
        }

        // MikroTik format: "10.0.0.10-10.0.0.100,10.0.1.10-10.0.1.100"
        // Validate and clean up
        $parsed = [];
        $parts = explode(',', $ranges);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^\d+\.\d+\.\d+\.\d+-\d+\.\d+\.\d+\.\d+$/', $part)) {
                $parsed[] = $part;
            }
        }

        return implode(',', $parsed);
    }
}
```

---

## Sync/Provisioning to Router

### Sync Direction: Database → Router

When customer packages change, profiles are created, or passwords are updated, we need to push these changes to the router.

### Implementation Pattern

**File: `app/Services/RouterProvisioningService.php` (Enhanced)**

```php
namespace App\Services;

use App\Models\NetworkUser;
use App\Models\MikrotikRouter;
use App\Models\MikrotikProfile;
use App\Models\Package;
use Illuminate\Support\Facades\Log;

class RouterProvisioningService
{
    protected $apiService;

    public function __construct(MikrotikApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Provision a network user to router
     * This creates or updates PPP secret on the router
     */
    public function provisionUser(NetworkUser $user, MikrotikRouter $router): bool
    {
        if (!$this->apiService->connect($router)) {
            Log::error('Cannot connect to router for provisioning', [
                'router_id' => $router->id,
                'user_id' => $user->id,
            ]);
            return false;
        }

        try {
            // Get user's package and associated profile
            $package = $user->package;
            if (!$package) {
                throw new \Exception('User has no package assigned');
            }

            $profile = $this->getProfileForPackage($package, $router);
            if (!$profile) {
                throw new \Exception('No profile mapped for this package on this router');
            }

            // Ensure profile exists on router before creating secret
            $this->ensureProfileExists($router, $profile);

            // Check if user already exists on router
            $existingRows = $this->apiService->getMktRows('ppp_secret', ['name' => $user->username]);

            $secretData = [
                'name' => $user->username,
                'password' => $user->password,
                'profile' => $profile->name,
                'service' => 'pppoe',
                'disabled' => $user->status === 'active' ? 'no' : 'yes',
                'comment' => $this->buildCustomerComment($user),
            ];

            // Handle static IP allocation if configured
            if ($profile->ip_allocation_mode === 'static' && $user->static_ip) {
                $secretData['remote-address'] = $user->static_ip;
            }

            if (!empty($existingRows)) {
                // Update existing secret
                $existingRow = array_shift($existingRows);
                $this->apiService->editMktRow('ppp_secret', $existingRow, $secretData);
                
                Log::info('PPP secret updated on router', [
                    'router_id' => $router->id,
                    'username' => $user->username,
                ]);
            } else {
                // Create new secret
                $this->apiService->addMktRows('ppp_secret', [$secretData]);
                
                Log::info('PPP secret created on router', [
                    'router_id' => $router->id,
                    'username' => $user->username,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to provision user to router', [
                'router_id' => $router->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Ensure PPP profile exists on router before creating secrets
     * This prevents errors when users reference non-existent profiles
     */
    private function ensureProfileExists(MikrotikRouter $router, MikrotikProfile $profile): void
    {
        $routerRows = $this->apiService->getMktRows('ppp_profile', ['name' => $profile->name]);

        if (empty($routerRows)) {
            // Profile doesn't exist on router, create it
            $this->createProfileOnRouter($router, $profile);
            
            Log::info('Profile created on router as dependency', [
                'router_id' => $router->id,
                'profile_name' => $profile->name,
            ]);
        }
    }

    /**
     * Create PPP profile on router
     */
    private function createProfileOnRouter(MikrotikRouter $router, MikrotikProfile $profile): void
    {
        $profileData = [
            'name' => $profile->name,
            'local-address' => $profile->local_address ?? config('mikrotik.ppp_local_address', '10.0.0.1'),
            'remote-address' => $profile->remote_address ?? 'pool-clients',
        ];

        if ($profile->rate_limit) {
            $profileData['rate-limit'] = $profile->rate_limit;
        }

        if ($profile->session_timeout) {
            $profileData['session-timeout'] = $profile->session_timeout;
        }

        if ($profile->idle_timeout) {
            $profileData['idle-timeout'] = $profile->idle_timeout;
        }

        $this->apiService->addMktRows('ppp_profile', [$profileData]);
    }

    /**
     * Get profile for package
     */
    private function getProfileForPackage(Package $package, MikrotikRouter $router): ?MikrotikProfile
    {
        // Look up profile mapping for this package and router
        $mapping = $package->profileMappings()
            ->where('router_id', $router->id)
            ->first();

        if (!$mapping) {
            return null;
        }

        return MikrotikProfile::where('router_id', $router->id)
            ->where('name', $mapping->profile_name)
            ->first();
    }

    /**
     * Build customer comment for embedding metadata
     */
    private function buildCustomerComment(NetworkUser $user): string
    {
        $parts = [
            "uid:{$user->id}",
            "name:" . str_replace([',', ';'], '', $user->name ?? 'Unknown'),
            "mobile:{$user->mobile}",
            "pkg:{$user->package_id}",
            "status:{$user->status}",
        ];

        if ($user->billing_date) {
            $parts[] = "bill:" . $user->billing_date->format('Y-m-d');
        }

        return implode(',', $parts);
    }

    /**
     * Remove user from router (disable or delete)
     */
    public function deprovisionUser(NetworkUser $user, MikrotikRouter $router, bool $delete = false): bool
    {
        if (!$this->apiService->connect($router)) {
            return false;
        }

        try {
            $existingRows = $this->apiService->getMktRows('ppp_secret', ['name' => $user->username]);

            if (empty($existingRows)) {
                Log::info('User not found on router for deprovisioning', [
                    'router_id' => $router->id,
                    'username' => $user->username,
                ]);
                return true;
            }

            $existingRow = array_shift($existingRows);

            if ($delete) {
                // Completely remove from router
                $this->apiService->removeMktRows('ppp_secret', [$existingRow]);
                Log::info('PPP secret deleted from router', [
                    'router_id' => $router->id,
                    'username' => $user->username,
                ]);
            } else {
                // Just disable
                $this->apiService->editMktRow('ppp_secret', $existingRow, ['disabled' => 'yes']);
                Log::info('PPP secret disabled on router', [
                    'router_id' => $router->id,
                    'username' => $user->username,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deprovision user from router', [
                'router_id' => $router->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
```

---

## Customer Metadata Comments

### Purpose
Embed customer information directly into router objects (PPP secrets, Hotspot users) for:
- Quick identification in router logs
- Troubleshooting without accessing billing system
- Customer service efficiency

### Implementation Pattern

**Comment Format:**
```
uid:123,name:John Doe,mobile:+8801712345678,pkg:5,status:active,bill:2026-02-01
```

**Helper Class: `app/Helpers/RouterCommentHelper.php`**

```php
namespace App\Helpers;

use App\Models\NetworkUser;

class RouterCommentHelper
{
    /**
     * Build router comment for network user
     */
    public static function buildUserComment(NetworkUser $user): string
    {
        $parts = [
            "uid:{$user->id}",
            "tenant:" . ($user->tenant_id ?? 0),
            "name:" . self::sanitize($user->name ?? 'Unknown'),
            "mobile:" . ($user->mobile ?? 'N/A'),
            "pkg:{$user->package_id}",
            "status:{$user->status}",
        ];

        if ($user->zone_id) {
            $parts[] = "zone:{$user->zone_id}";
        }

        if ($user->billing_date) {
            $parts[] = "bill:" . $user->billing_date->format('Y-m-d');
        }

        if ($user->package && $user->package->expired_at) {
            $parts[] = "exp:" . $user->package->expired_at->format('Y-m-d');
        }

        return implode(',', $parts);
    }

    /**
     * Parse comment back into array
     */
    public static function parseComment(string $comment): array
    {
        $data = [];
        $parts = explode(',', $comment);

        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                [$key, $value] = explode(':', $part, 2);
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Sanitize value for comment (remove special characters)
     */
    private static function sanitize(string $value): string
    {
        return str_replace([',', ';', ':', "\n", "\r"], '', $value);
    }

    /**
     * Update comment on router
     */
    public static function updateRouterComment(
        NetworkUser $user,
        MikrotikRouter $router,
        MikrotikApiService $api
    ): bool {
        try {
            if (!$api->connect($router)) {
                return false;
            }

            $existingRows = $api->getMktRows('ppp_secret', ['name' => $user->username]);

            if (empty($existingRows)) {
                return false;
            }

            $existingRow = array_shift($existingRows);
            $newComment = self::buildUserComment($user);

            $api->editMktRow('ppp_secret', $existingRow, ['comment' => $newComment]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update router comment', [
                'user_id' => $user->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
```

---

## Authentication Choice: Router vs RADIUS

### Authentication Modes

1. **RADIUS Mode** (Recommended for production)
   - Router authenticates users via RADIUS server
   - Centralized user management
   - Easy to manage multiple routers
   - Local secrets kept as backup (disabled)

2. **Router Mode** (Development/Fallback)
   - Router authenticates locally via `/ppp/secret`
   - No dependency on RADIUS server
   - Useful for testing and development

3. **Hybrid Mode** (Best Practice)
   - Primary: RADIUS authentication
   - Fallback: Local secrets (automatically enabled when RADIUS fails)
   - Managed via Netwatch automation

### Configuration

**File: `config/radius.php` (Enhanced)**

```php
return [
    'server_ip' => env('RADIUS_SERVER_IP', '127.0.0.1'),
    'authentication_port' => env('RADIUS_AUTH_PORT', 1812),
    'accounting_port' => env('RADIUS_ACCT_PORT', 1813),
    'interim_update' => env('RADIUS_INTERIM_UPDATE', '5m'),
    
    // Authentication mode: 'radius', 'router', 'hybrid'
    'primary_authenticator' => env('RADIUS_PRIMARY_AUTH', 'hybrid'),
    
    // Netwatch settings for automatic fallback
    'netwatch' => [
        'enabled' => env('RADIUS_NETWATCH_ENABLED', true),
        'interval' => env('RADIUS_NETWATCH_INTERVAL', '1m'),
        'timeout' => env('RADIUS_NETWATCH_TIMEOUT', '1s'),
    ],
];
```

### Netwatch Automatic Fallback

**Implementation: `app/Services/RouterRadiusFailoverService.php`**

```php
namespace App\Services;

use App\Models\MikrotikRouter;
use Illuminate\Support\Facades\Log;

class RouterRadiusFailoverService
{
    protected $apiService;

    public function __construct(MikrotikApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Configure automatic RADIUS failover using Netwatch
     */
    public function configureFailover(MikrotikRouter $router): bool
    {
        if (!$this->apiService->connect($router)) {
            return false;
        }

        try {
            $radiusServer = config('radius.server_ip');
            $menu = 'tool_netwatch';

            // Define Netwatch rule
            $rows = [[
                'host' => $radiusServer,
                'interval' => config('radius.netwatch.interval', '1m'),
                'timeout' => config('radius.netwatch.timeout', '1s'),
                
                // When RADIUS comes back UP:
                // 1. Disable all local PPP secrets (force RADIUS auth)
                // 2. Remove active non-RADIUS sessions (clean slate)
                'up-script' => implode(';', [
                    '/ppp secret disable [find disabled=no]',
                    '/ppp active remove [find radius=no]',
                    ':log info "RADIUS server recovered - switched to RADIUS authentication"',
                ]),
                
                // When RADIUS goes DOWN:
                // 1. Enable all local PPP secrets (fallback to local auth)
                'down-script' => implode(';', [
                    '/ppp secret enable [find disabled=yes]',
                    ':log warning "RADIUS server down - switched to local authentication"',
                ]),
                
                'comment' => 'radius-failover-automation',
            ]];

            // Remove existing netwatch for this host to avoid duplicates
            $existingRows = $this->apiService->getMktRows($menu, ['host' => $radiusServer]);
            if (!empty($existingRows)) {
                $this->apiService->removeMktRows($menu, $existingRows);
            }

            // Add new netwatch rule
            $this->apiService->addMktRows($menu, $rows);

            Log::info('RADIUS failover configured', [
                'router_id' => $router->id,
                'radius_server' => $radiusServer,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to configure RADIUS failover', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Manually switch to RADIUS mode
     */
    public function switchToRadiusMode(MikrotikRouter $router): bool
    {
        if (!$this->apiService->connect($router)) {
            return false;
        }

        try {
            // Disable all local secrets
            $secrets = $this->apiService->getMktRows('ppp_secret');
            foreach ($secrets as $secret) {
                $this->apiService->editMktRow('ppp_secret', $secret, ['disabled' => 'yes']);
            }

            // Remove non-RADIUS sessions
            $activeSessions = $this->apiService->getMktRows('ppp_active', ['radius' => 'no']);
            if (!empty($activeSessions)) {
                $this->apiService->removeMktRows('ppp_active', $activeSessions);
            }

            Log::info('Switched to RADIUS mode', ['router_id' => $router->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to switch to RADIUS mode', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Manually switch to Router mode (local authentication)
     */
    public function switchToRouterMode(MikrotikRouter $router): bool
    {
        if (!$this->apiService->connect($router)) {
            return false;
        }

        try {
            // Enable all local secrets
            $secrets = $this->apiService->getMktRows('ppp_secret');
            foreach ($secrets as $secret) {
                $this->apiService->editMktRow('ppp_secret', $secret, ['disabled' => 'no']);
            }

            Log::info('Switched to Router mode', ['router_id' => $router->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to switch to Router mode', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
```

---

## Backup Strategies

### Backup Types

1. **Router-side Exports**: MikroTik native `.rsc` export files
2. **Database Snapshots**: Before/after sync operations
3. **Configuration History**: Track all changes via `router_configurations`
4. **User Backups**: Copy users to router for failover

### Implementation Pattern

**File: `app/Services/RouterBackupService.php`**

```php
namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RouterBackupService
{
    protected $apiService;

    public function __construct(MikrotikApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create full router backup before making changes
     */
    public function createPreChangeBackup(MikrotikRouter $router, string $changeReason): ?RouterConfigurationBackup
    {
        if (!$this->apiService->connect($router)) {
            Log::error('Cannot connect to router for backup', ['router_id' => $router->id]);
            return null;
        }

        try {
            $timestamp = Carbon::now()->timestamp;
            $backupName = "pre-change-backup-{$timestamp}";

            // Export full configuration to router's local storage
            $this->apiService->ttyWrite('/export', ['file' => $backupName]);

            // Also create database record
            $backup = RouterConfigurationBackup::create([
                'router_id' => $router->id,
                'tenant_id' => $router->tenant_id,
                'backup_type' => 'pre_change',
                'backup_name' => $backupName,
                'backup_reason' => $changeReason,
                'created_by' => auth()->id(),
            ]);

            Log::info('Pre-change backup created', [
                'router_id' => $router->id,
                'backup_name' => $backupName,
                'reason' => $changeReason,
            ]);

            return $backup;
        } catch (\Exception $e) {
            Log::error('Failed to create pre-change backup', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create PPP secrets backup
     */
    public function backupPppSecrets(MikrotikRouter $router): ?string
    {
        if (!$this->apiService->connect($router)) {
            return null;
        }

        try {
            $timestamp = Carbon::now()->timestamp;
            $backupFile = "ppp-secret-backup-{$timestamp}";

            $this->apiService->ttyWrite('/ppp/secret/export', ['file' => $backupFile]);

            Log::info('PPP secrets backup created', [
                'router_id' => $router->id,
                'file' => $backupFile,
            ]);

            return $backupFile;
        } catch (\Exception $e) {
            Log::error('Failed to backup PPP secrets', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mirror active customers to router (for failover)
     * This creates/updates local PPP secrets on router
     */
    public function mirrorCustomersToRouter(MikrotikRouter $router): array
    {
        if (!$this->apiService->connect($router)) {
            return ['success' => false, 'message' => 'Cannot connect to router'];
        }

        try {
            // Get all active network users for this router's tenant
            $users = \App\Models\NetworkUser::where('tenant_id', $router->tenant_id)
                ->where('status', 'active')
                ->with('package')
                ->get();

            $created = 0;
            $updated = 0;
            $errors = 0;

            foreach ($users as $user) {
                try {
                    $profile = $this->getProfileForUser($user, $router);
                    if (!$profile) {
                        continue;
                    }

                    $secretData = [
                        'name' => $user->username,
                        'password' => $user->password,
                        'profile' => $profile->name,
                        'service' => 'pppoe',
                        'disabled' => 'yes',  // Keep disabled in RADIUS mode
                        'comment' => \App\Helpers\RouterCommentHelper::buildUserComment($user),
                    ];

                    if ($profile->ip_allocation_mode === 'static' && $user->static_ip) {
                        $secretData['remote-address'] = $user->static_ip;
                    }

                    $existingRows = $this->apiService->getMktRows('ppp_secret', ['name' => $user->username]);

                    if (!empty($existingRows)) {
                        $existingRow = array_shift($existingRows);
                        $this->apiService->editMktRow('ppp_secret', $existingRow, $secretData);
                        $updated++;
                    } else {
                        $this->apiService->addMktRows('ppp_secret', [$secretData]);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::warning('Failed to mirror user to router', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Customers mirrored to router', [
                'router_id' => $router->id,
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
            ]);

            return [
                'success' => true,
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'message' => "Mirrored {$created} new and {$updated} existing users",
            ];
        } catch (\Exception $e) {
            Log::error('Failed to mirror customers to router', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Restore configuration from backup
     */
    public function restoreFromBackup(MikrotikRouter $router, string $backupName): bool
    {
        if (!$this->apiService->connect($router)) {
            return false;
        }

        try {
            // Import the backup file
            $this->apiService->ttyWrite('/import', ['file' => $backupName]);

            Log::info('Configuration restored from backup', [
                'router_id' => $router->id,
                'backup_name' => $backupName,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore from backup', [
                'router_id' => $router->id,
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function getProfileForUser($user, $router)
    {
        if (!$user->package) {
            return null;
        }

        $mapping = $user->package->profileMappings()
            ->where('router_id', $router->id)
            ->first();

        if (!$mapping) {
            return null;
        }

        return \App\Models\MikrotikProfile::where('router_id', $router->id)
            ->where('name', $mapping->profile_name)
            ->first();
    }
}
```

---

## Implementation Roadmap

### Phase 1: Foundation (Week 1-2)
- [ ] Create NAS table and model
- [ ] Add RADIUS secret fields to routers
- [ ] Enhance MikrotikApiService with new methods:
  - `getMktRows()`, `addMktRows()`, `editMktRow()`, `removeMktRows()`, `ttyWrite()`
- [ ] Create RouterCommentHelper
- [ ] Update router creation form with RADIUS fields

### Phase 2: Configuration Push (Week 2-3)
- [ ] Create RouterConfigurationController
- [ ] Implement `configureRadius()` method
- [ ] Implement `configurePppAaa()` method
- [ ] Implement `updatePppProfiles()` method
- [ ] Create RouterConfigureCommand
- [ ] Add configuration UI

### Phase 3: Import Features (Week 3-4)
- [ ] Enhance MikrotikImportService:
  - `importIpPools()` with validation
  - `importPppProfiles()` with metadata
  - `importPppSecrets()` with backup
- [ ] Add import UI with progress tracking
- [ ] Create import jobs for background processing

### Phase 4: Provisioning (Week 4-5)
- [ ] Enhance RouterProvisioningService:
  - `provisionUser()` with dependency checking
  - `ensureProfileExists()`
  - `deprovisionUser()`
- [ ] Add user lifecycle hooks:
  - On user creation → provision to router
  - On package change → update router profile
  - On password change → update router secret
- [ ] Create provisioning dashboard

### Phase 5: Authentication & Failover (Week 5-6)
- [ ] Create RouterRadiusFailoverService
- [ ] Implement `configureFailover()` with Netwatch
- [ ] Add manual mode switching UI
- [ ] Create RADIUS health monitoring
- [ ] Add failover status display

### Phase 6: Backup & Recovery (Week 6-7)
- [ ] Create RouterBackupService
- [ ] Implement `createPreChangeBackup()`
- [ ] Implement `mirrorCustomersToRouter()`
- [ ] Add backup scheduling
- [ ] Create backup/restore UI
- [ ] Add backup history view

### Phase 7: Testing & Polish (Week 7-8)
- [ ] Write unit tests for all services
- [ ] Write integration tests
- [ ] Add API tests
- [ ] Performance testing
- [ ] Security audit
- [ ] Documentation completion
- [ ] User guide creation

---

## Code Examples

### Example 1: Complete Router Setup

```php
// Configure a new router with RADIUS
$router = MikrotikRouter::find(1);

// Step 1: Test connectivity
if (!$router->testConnectivity()) {
    throw new \Exception('Cannot connect to router');
}

// Step 2: Create backup
$backupService = app(RouterBackupService::class);
$backup = $backupService->createPreChangeBackup($router, 'Initial RADIUS setup');

// Step 3: Configure RADIUS
$configController = app(RouterConfigurationController::class);
$configController->configureRadius($router);

// Step 4: Import existing configuration
$importService = app(MikrotikImportService::class);
$importService->importIpPools($router, auth()->id());
$importService->importPppProfiles($router, auth()->id());
$importService->importPppSecrets($router, auth()->id());

// Step 5: Configure failover
$failoverService = app(RouterRadiusFailoverService::class);
$failoverService->configureFailover($router);

// Step 6: Mirror customers to router
$backupService->mirrorCustomersToRouter($router);
```

### Example 2: Provision User to Router

```php
// When a new user is created or updated
$user = NetworkUser::find(123);
$router = MikrotikRouter::find(1);

$provisioningService = app(RouterProvisioningService::class);

if ($provisioningService->provisionUser($user, $router)) {
    // Success - user can now connect via PPPoE
    Log::info("User {$user->username} provisioned successfully");
} else {
    // Failed - check logs
    Log::error("Failed to provision user {$user->username}");
}
```

### Example 3: Switch Authentication Mode

```php
// Switch to RADIUS mode
$router = MikrotikRouter::find(1);
$failoverService = app(RouterRadiusFailoverService::class);

if ($failoverService->switchToRadiusMode($router)) {
    // All users now authenticate via RADIUS
    // Local secrets are disabled
}

// Or switch to Router mode (for maintenance)
if ($failoverService->switchToRouterMode($router)) {
    // Users authenticate locally
    // Useful when RADIUS server is down
}
```

---

## UI Development Tasks

### 1. Enhanced Router Creation Form

**Location:** `resources/views/panels/admin/network/routers-create.blade.php`

**Add Fields:**
```html
<!-- RADIUS Configuration Section -->
<div class="card mb-4">
    <div class="card-header">
        <h4>RADIUS Configuration</h4>
    </div>
    <div class="card-body">
        <!-- RADIUS Shared Secret -->
        <div class="form-group">
            <label>RADIUS Shared Secret*</label>
            <input type="text" name="radius_secret" class="form-control" 
                   value="{{ old('radius_secret', Str::random(32)) }}" required>
            <small class="text-muted">Shared secret between router and RADIUS server</small>
        </div>

        <!-- Public IP -->
        <div class="form-group">
            <label>Public IP Address</label>
            <input type="text" name="public_ip" class="form-control" 
                   value="{{ old('public_ip') }}" placeholder="203.0.113.1">
            <small class="text-muted">Router's public IP (for RADIUS NAS identification)</small>
        </div>

        <!-- RADIUS Server -->
        <div class="form-group">
            <label>RADIUS Server IP</label>
            <input type="text" name="radius_server" class="form-control" 
                   value="{{ old('radius_server', config('radius.server_ip')) }}" readonly>
            <small class="text-muted">Configured in .env (RADIUS_SERVER_IP)</small>
        </div>

        <!-- Authentication Mode -->
        <div class="form-group">
            <label>Primary Authentication</label>
            <select name="primary_auth" class="form-control">
                <option value="radius">RADIUS (Recommended)</option>
                <option value="router">Router (Local)</option>
                <option value="hybrid">Hybrid (RADIUS with fallback)</option>
            </select>
        </div>
    </div>
</div>
```

### 2. Router Configuration Dashboard

**Location:** `resources/views/panels/admin/network/router-configure.blade.php`

**Features:**
- One-click RADIUS configuration
- Import profiles/pools/secrets buttons
- Configuration status indicators
- Failover status display
- Backup/restore interface

### 3. Import Progress Interface

**Location:** `resources/views/panels/admin/network/router-import.blade.php`

**Features:**
- Select import type (profiles, pools, secrets)
- Real-time progress bar
- Import summary (created, updated, errors)
- Option to include/exclude disabled users
- Backup confirmation before import

### 4. Provisioning Status Display

**Location:** Component in user detail page

**Features:**
- Show if user is provisioned to router
- Display current profile and IP
- Manual provision/deprovision buttons
- View router comment
- Provisioning history

### 5. Backup Management Interface

**Location:** `resources/views/panels/admin/network/router-backups.blade.php`

**Features:**
- List all backups with timestamps
- Backup type indicators (manual, pre-change, scheduled)
- Restore button with confirmation
- Download backup file
- Auto-backup scheduler

---

## Best Practices

### Security
1. Always encrypt router credentials (✓ already implemented)
2. Use HTTPS for production router API (add to config)
3. Rotate RADIUS secrets regularly
4. Limit API access by IP whitelist
5. Log all configuration changes

### Performance
1. Use background jobs for bulk operations (import, provisioning)
2. Implement queue-based provisioning for user creation
3. Cache router connection objects
4. Batch API calls when possible
5. Use database transactions for consistency

### Reliability
1. Always create backups before changes
2. Implement automatic rollback on failures
3. Use health checks before critical operations
4. Log all operations for troubleshooting
5. Test failover scenarios regularly

### Maintainability
1. Follow repository patterns (services, controllers, models)
2. Document all methods with PHPDoc
3. Write unit tests for services
4. Use dependency injection
5. Keep configuration in .env and config files

---

## Related Documentation

- [RADIUS_SETUP_GUIDE.md](RADIUS_SETUP_GUIDE.md) - RADIUS database setup
- [ROUTER_PROVISIONING_GUIDE.md](ROUTER_PROVISIONING_GUIDE.md) - Zero-touch provisioning
- [MIKROTIK_QUICKSTART.md](MIKROTIK_QUICKSTART.md) - Quick start guide
- [MIKROTIK_ADVANCED_FEATURES.md](MIKROTIK_ADVANCED_FEATURES.md) - Advanced features

---

## References

**IspBills Repository:**
- https://github.com/sohag1426/IspBills/search?q=RouterosAPI&type=code
- https://github.com/sohag1426/IspBills/search?q=%2Fppp%2Faaa%2Fset&type=code
- https://github.com/sohag1426/IspBills/search?q=primary_authenticator&type=code

**Key Files Studied:**
- `app/Http/Controllers/Freeradius/NasController.php` - NAS management
- `app/Http/Controllers/RouterConfigurationController.php` - Router config push
- `app/Http/Controllers/Mikrotik/MikrotikDbSyncController.php` - Import/sync
- `app/Http/Controllers/Customer/CustomerBackupController.php` - User backups
- `app/Http/Controllers/NasNetWatchController.php` - Failover automation

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-26  
**Author:** Development Team  
**Status:** Implementation Planning
