<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\V1\IpamController;
use App\Http\Controllers\Api\V1\MikrotikController;
use App\Http\Controllers\Api\V1\MonitoringController;
use App\Http\Controllers\Api\V1\NetworkUserController;
use App\Http\Controllers\Api\V1\OltController;
use App\Http\Controllers\Api\V1\RadiusController;
use App\Http\Controllers\Api\V1\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// AJAX Data API Routes (for frontend)
Route::middleware(['auth:sanctum', 'rate_limit:api'])->prefix('data')->group(function () {
    Route::get('/users', [DataController::class, 'getUsers'])->name('api.data.users');
    Route::get('/network-users', [DataController::class, 'getNetworkUsers'])->name('api.data.network-users');
    Route::get('/invoices', [DataController::class, 'getInvoices'])->name('api.data.invoices');
    Route::get('/payments', [DataController::class, 'getPayments'])->name('api.data.payments');
    Route::get('/packages', [DataController::class, 'getPackages'])->name('api.data.packages');
    Route::get('/dashboard-stats', [DataController::class, 'getDashboardStats'])->name('api.data.dashboard-stats');
    Route::get('/recent-activities', [DataController::class, 'getRecentActivities'])->name('api.data.recent-activities');
});

// Chart API Routes (for ApexCharts)
Route::middleware(['auth:sanctum', 'rate_limit:api'])->prefix('charts')->group(function () {
    Route::get('/revenue', [\App\Http\Controllers\Api\ChartController::class, 'getRevenueChart'])->name('api.charts.revenue');
    Route::get('/invoice-status', [\App\Http\Controllers\Api\ChartController::class, 'getInvoiceStatusChart'])->name('api.charts.invoice-status');
    Route::get('/user-growth', [\App\Http\Controllers\Api\ChartController::class, 'getUserGrowthChart'])->name('api.charts.user-growth');
    Route::get('/payment-methods', [\App\Http\Controllers\Api\ChartController::class, 'getPaymentMethodChart'])->name('api.charts.payment-methods');
    Route::get('/daily-revenue', [\App\Http\Controllers\Api\ChartController::class, 'getDailyRevenueChart'])->name('api.charts.daily-revenue');
    Route::get('/package-distribution', [\App\Http\Controllers\Api\ChartController::class, 'getPackageDistributionChart'])->name('api.charts.package-distribution');
    Route::get('/commission', [\App\Http\Controllers\Api\ChartController::class, 'getCommissionChart'])->name('api.charts.commission');
    Route::get('/dashboard', [\App\Http\Controllers\Api\ChartController::class, 'getDashboardCharts'])->name('api.charts.dashboard');
});

// Widget API Routes (for dashboard widgets)
Route::middleware(['auth:sanctum', 'rate_limit:api'])->prefix('v1/widgets')->group(function () {
    Route::post('/refresh', [WidgetController::class, 'refresh'])->name('api.v1.widgets.refresh');
    Route::get('/suspension-forecast', [WidgetController::class, 'suspensionForecast'])->name('api.v1.widgets.suspension-forecast');
    Route::get('/collection-target', [WidgetController::class, 'collectionTarget'])->name('api.v1.widgets.collection-target');
    Route::get('/sms-usage', [WidgetController::class, 'smsUsage'])->name('api.v1.widgets.sms-usage');
});

Route::prefix('v1')->middleware('rate_limit:public_api')->group(function () {
    // IPAM Routes
    Route::prefix('ipam')->group(function () {
        // IP Pools
        Route::get('/pools', [IpamController::class, 'listPools'])->name('api.ipam.pools.index');
        Route::post('/pools', [IpamController::class, 'createPool'])->name('api.ipam.pools.store');
        Route::get('/pools/{id}', [IpamController::class, 'getPool'])->name('api.ipam.pools.show');
        Route::put('/pools/{id}', [IpamController::class, 'updatePool'])->name('api.ipam.pools.update');
        Route::delete('/pools/{id}', [IpamController::class, 'deletePool'])->name('api.ipam.pools.destroy');

        // IP Subnets
        Route::get('/subnets', [IpamController::class, 'listSubnets'])->name('api.ipam.subnets.index');
        Route::post('/subnets', [IpamController::class, 'createSubnet'])->name('api.ipam.subnets.store');
        Route::get('/subnets/{id}', [IpamController::class, 'getSubnet'])->name('api.ipam.subnets.show');
        Route::put('/subnets/{id}', [IpamController::class, 'updateSubnet'])->name('api.ipam.subnets.update');
        Route::delete('/subnets/{id}', [IpamController::class, 'deleteSubnet'])->name('api.ipam.subnets.destroy');

        // IP Allocations
        Route::get('/allocations', [IpamController::class, 'listAllocations'])->name('api.ipam.allocations.index');
        Route::post('/allocations', [IpamController::class, 'allocateIP'])->name('api.ipam.allocations.store');
        Route::delete('/allocations/{id}', [IpamController::class, 'releaseIP'])->name('api.ipam.allocations.destroy');

        // Utilization Stats
        Route::get('/pools/{id}/utilization', [IpamController::class, 'getPoolUtilization'])->name('api.ipam.pools.utilization');
        Route::get('/subnets/{id}/available-ips', [IpamController::class, 'getAvailableIPs'])->name('api.ipam.subnets.available-ips');
    });

    // RADIUS Routes
    Route::prefix('radius')->group(function () {
        // Authentication
        Route::post('/authenticate', [RadiusController::class, 'authenticate'])->name('api.radius.authenticate');

        // Accounting
        Route::post('/accounting/start', [RadiusController::class, 'accountingStart'])->name('api.radius.accounting.start');
        Route::post('/accounting/update', [RadiusController::class, 'accountingUpdate'])->name('api.radius.accounting.update');
        Route::post('/accounting/stop', [RadiusController::class, 'accountingStop'])->name('api.radius.accounting.stop');

        // User Management
        Route::post('/users', [RadiusController::class, 'createUser'])->name('api.radius.users.store');
        Route::put('/users/{username}', [RadiusController::class, 'updateUser'])->name('api.radius.users.update');
        Route::delete('/users/{username}', [RadiusController::class, 'deleteUser'])->name('api.radius.users.destroy');
        Route::post('/users/{username}/sync', [RadiusController::class, 'syncUser'])->name('api.radius.users.sync');

        // Statistics
        Route::get('/users/{username}/stats', [RadiusController::class, 'getUserStats'])->name('api.radius.users.stats');
    });

    // MikroTik Routes
    Route::prefix('mikrotik')->middleware(['auth', 'rate_limit:api'])->group(function () {
        // Router Management
        Route::get('/routers', [MikrotikController::class, 'listRouters'])->name('api.mikrotik.routers.index');
        Route::post('/routers/{id}/connect', [MikrotikController::class, 'connectRouter'])->name('api.mikrotik.routers.connect');
        Route::get('/routers/{id}/health', [MikrotikController::class, 'healthCheck'])->name('api.mikrotik.routers.health');

        // PPPoE Users
        Route::get('/pppoe-users', [MikrotikController::class, 'listPppoeUsers'])->name('api.mikrotik.pppoe.index');
        Route::post('/pppoe-users', [MikrotikController::class, 'createPppoeUser'])->name('api.mikrotik.pppoe.store');
        Route::put('/pppoe-users/{username}', [MikrotikController::class, 'updatePppoeUser'])->name('api.mikrotik.pppoe.update');
        Route::delete('/pppoe-users/{username}', [MikrotikController::class, 'deletePppoeUser'])->name('api.mikrotik.pppoe.destroy');

        // Sessions
        Route::get('/sessions', [MikrotikController::class, 'listActiveSessions'])->name('api.mikrotik.sessions.index');
        Route::delete('/sessions/{id}', [MikrotikController::class, 'disconnectSession'])->name('api.mikrotik.sessions.destroy');

        // Profiles
        Route::get('/profiles', [MikrotikController::class, 'listProfiles'])->name('api.mikrotik.profiles.index');
        Route::post('/profiles', [MikrotikController::class, 'createProfile'])->name('api.mikrotik.profiles.store');
        Route::get('/profiles/{id}', [MikrotikController::class, 'viewProfile'])->name('api.mikrotik.profiles.show');
        Route::put('/profiles/{id}', [MikrotikController::class, 'updateProfile'])->name('api.mikrotik.profiles.update');
        Route::delete('/profiles/{id}', [MikrotikController::class, 'deleteProfile'])->name('api.mikrotik.profiles.destroy');
        Route::post('/routers/{routerId}/import-profiles', [MikrotikController::class, 'importProfiles'])->name('api.mikrotik.profiles.import');

        // IP Pools
        Route::get('/ip-pools', [MikrotikController::class, 'listIpPools'])->name('api.mikrotik.ip-pools.index');
        Route::post('/ip-pools', [MikrotikController::class, 'createIpPool'])->name('api.mikrotik.ip-pools.store');
        Route::get('/ip-pools/{id}', [MikrotikController::class, 'viewIpPool'])->name('api.mikrotik.ip-pools.show');
        Route::put('/ip-pools/{id}', [MikrotikController::class, 'updateIpPool'])->name('api.mikrotik.ip-pools.update');
        Route::delete('/ip-pools/{id}', [MikrotikController::class, 'deleteIpPool'])->name('api.mikrotik.ip-pools.destroy');
        Route::post('/routers/{routerId}/import-pools', [MikrotikController::class, 'importIpPools'])->name('api.mikrotik.ip-pools.import');

        // Secrets
        Route::post('/routers/{routerId}/import-secrets', [MikrotikController::class, 'importSecrets'])->name('api.mikrotik.secrets.import');

        // Router Configuration
        Route::post('/routers/{routerId}/configure', [MikrotikController::class, 'configureRouter'])->name('api.mikrotik.routers.configure');
        Route::get('/routers/{routerId}/configurations', [MikrotikController::class, 'listConfigurations'])->name('api.mikrotik.routers.configurations');

        // VPN Management
        Route::get('/vpn-accounts', [MikrotikController::class, 'listVpnAccounts'])->name('api.mikrotik.vpn.index');
        Route::post('/vpn-accounts', [MikrotikController::class, 'createVpnAccount'])->name('api.mikrotik.vpn.store');
        Route::get('/vpn-accounts/{id}', [MikrotikController::class, 'viewVpnAccount'])->name('api.mikrotik.vpn.show');
        Route::put('/vpn-accounts/{id}', [MikrotikController::class, 'updateVpnAccount'])->name('api.mikrotik.vpn.update');
        Route::delete('/vpn-accounts/{id}', [MikrotikController::class, 'deleteVpnAccount'])->name('api.mikrotik.vpn.destroy');
        Route::get('/routers/{routerId}/vpn-status', [MikrotikController::class, 'getVpnStatus'])->name('api.mikrotik.vpn.status');

        // Queue Management
        Route::get('/queues', [MikrotikController::class, 'listQueues'])->name('api.mikrotik.queues.index');
        Route::post('/queues', [MikrotikController::class, 'createQueue'])->name('api.mikrotik.queues.store');
        Route::get('/queues/{id}', [MikrotikController::class, 'viewQueue'])->name('api.mikrotik.queues.show');
        Route::put('/queues/{id}', [MikrotikController::class, 'updateQueue'])->name('api.mikrotik.queues.update');
        Route::delete('/queues/{id}', [MikrotikController::class, 'deleteQueue'])->name('api.mikrotik.queues.destroy');

        // Firewall Management
        Route::get('/routers/{routerId}/firewall-rules', [MikrotikController::class, 'listFirewallRules'])->name('api.mikrotik.firewall.index');
        Route::post('/firewall-rules', [MikrotikController::class, 'addFirewallRule'])->name('api.mikrotik.firewall.store');

        // Package Speed Mapping
        Route::get('/package-mappings', [MikrotikController::class, 'listPackageMappings'])->name('api.mikrotik.package-mappings.index');
        Route::post('/package-mappings', [MikrotikController::class, 'mapPackageToProfile'])->name('api.mikrotik.package-mappings.store');
        Route::get('/package-mappings/{id}', [MikrotikController::class, 'viewPackageMapping'])->name('api.mikrotik.package-mappings.show');
        Route::put('/package-mappings/{id}', [MikrotikController::class, 'updatePackageMapping'])->name('api.mikrotik.package-mappings.update');
        Route::delete('/package-mappings/{id}', [MikrotikController::class, 'deletePackageMapping'])->name('api.mikrotik.package-mappings.destroy');
        Route::post('/users/{userId}/apply-speed', [MikrotikController::class, 'applySpeedToUser'])->name('api.mikrotik.users.apply-speed');
    });

    // Network Users Routes
    Route::prefix('network-users')->group(function () {
        Route::get('/', [NetworkUserController::class, 'index'])->name('api.network-users.index');
        Route::post('/', [NetworkUserController::class, 'store'])->name('api.network-users.store');
        Route::get('/{id}', [NetworkUserController::class, 'show'])->name('api.network-users.show');
        Route::put('/{id}', [NetworkUserController::class, 'update'])->name('api.network-users.update');
        Route::delete('/{id}', [NetworkUserController::class, 'destroy'])->name('api.network-users.destroy');
        Route::post('/{id}/sync-radius', [NetworkUserController::class, 'syncToRadius'])->name('api.network-users.sync-radius');
    });

    // Monitoring Routes
    Route::prefix('monitoring')->group(function () {
        // Device Status
        Route::get('/devices', [MonitoringController::class, 'getAllStatuses'])->name('api.monitoring.devices.index');
        Route::get('/devices/{type}/{id}/status', [MonitoringController::class, 'getDeviceStatus'])->name('api.monitoring.devices.status');
        Route::post('/devices/{type}/{id}/monitor', [MonitoringController::class, 'monitorDevice'])->name('api.monitoring.devices.monitor');

        // Bandwidth Usage
        Route::post('/devices/{type}/{id}/bandwidth', [MonitoringController::class, 'recordBandwidth'])->name('api.monitoring.bandwidth.record');
        Route::get('/devices/{type}/{id}/bandwidth', [MonitoringController::class, 'getBandwidthUsage'])->name('api.monitoring.bandwidth.usage');
        Route::get('/devices/{type}/{id}/bandwidth/graph', [MonitoringController::class, 'getBandwidthGraph'])->name('api.monitoring.bandwidth.graph');
    });

    // OLT Routes
    Route::prefix('olt')->group(function () {
        // OLT Management
        Route::get('/', [OltController::class, 'index'])->name('api.olt.index');
        Route::get('/{id}', [OltController::class, 'show'])->name('api.olt.show');
        Route::post('/{id}/test-connection', [OltController::class, 'testConnection'])->name('api.olt.test-connection');
        Route::post('/{id}/sync-onus', [OltController::class, 'syncOnus'])->name('api.olt.sync-onus');
        Route::get('/{id}/statistics', [OltController::class, 'statistics'])->name('api.olt.statistics');
        Route::post('/{id}/backup', [OltController::class, 'createBackup'])->name('api.olt.backup');
        Route::get('/{id}/backups', [OltController::class, 'backups'])->name('api.olt.backups');
        Route::get('/{id}/port-utilization', [OltController::class, 'portUtilization'])->name('api.olt.port-utilization');
        Route::get('/{id}/bandwidth-usage', [OltController::class, 'bandwidthUsage'])->name('api.olt.bandwidth-usage');
        Route::get('/{id}/monitor-onus', [OltController::class, 'monitorOnus'])->name('api.olt.monitor-onus');

        // ONU Operations
        Route::get('/onu/{onuId}', [OltController::class, 'onuDetails'])->name('api.olt.onu.details');
        Route::post('/onu/{onuId}/refresh', [OltController::class, 'refreshOnuStatus'])->name('api.olt.onu.refresh');
        Route::post('/onu/{onuId}/authorize', [OltController::class, 'authorizeOnu'])->name('api.olt.onu.authorize');
        Route::post('/onu/{onuId}/unauthorize', [OltController::class, 'unauthorizeOnu'])->name('api.olt.onu.unauthorize');
        Route::post('/onu/{onuId}/reboot', [OltController::class, 'rebootOnu'])->name('api.olt.onu.reboot');

        // Bulk Operations
        Route::post('/onu/bulk-operations', [OltController::class, 'bulkOnuOperations'])->name('api.olt.onu.bulk-operations');
    });
});
