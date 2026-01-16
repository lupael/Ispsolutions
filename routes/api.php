<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\IpamController;
use App\Http\Controllers\Api\V1\MikrotikController;
use App\Http\Controllers\Api\V1\NetworkUserController;
use App\Http\Controllers\Api\V1\RadiusController;
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

Route::prefix('v1')->group(function () {
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
    Route::prefix('mikrotik')->group(function () {
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
});
