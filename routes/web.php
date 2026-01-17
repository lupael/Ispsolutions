<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel\SuperAdminController;
use App\Http\Controllers\Panel\AdminController;
use App\Http\Controllers\Panel\ManagerController;
use App\Http\Controllers\Panel\StaffController;
use App\Http\Controllers\Panel\ResellerController;
use App\Http\Controllers\Panel\SubResellerController;
use App\Http\Controllers\Panel\CardDistributorController;
use App\Http\Controllers\Panel\CustomerController;
use App\Http\Controllers\Panel\DeveloperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Demo routes
Route::get('/demo1', function () {
    return view('pages.demo1.index');
});

Route::get('/demo2', function () {
    return view('pages.demo2.index');
});

Route::get('/demo3', function () {
    return view('pages.demo3.index');
});

Route::get('/demo4', function () {
    return view('pages.demo4.index');
});

Route::get('/demo5', function () {
    return view('pages.demo5.index');
});

Route::get('/demo6', function () {
    return view('pages.demo6.index');
});

Route::get('/demo7', function () {
    return view('pages.demo7.index');
});

Route::get('/demo8', function () {
    return view('pages.demo8.index');
});

Route::get('/demo9', function () {
    return view('pages.demo9.index');
})->name('demo9.index');

Route::get('/demo9/profile', function () {
    return view('pages.demo9.profile');
})->name('demo9.profile');

Route::get('/demo10', function () {
    return view('pages.demo10.index');
});

/*
|--------------------------------------------------------------------------
| Role-Based Panel Routes
|--------------------------------------------------------------------------
*/

// Super Admin Panel
Route::prefix('panel/super-admin')->name('panel.super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::get('/roles', [SuperAdminController::class, 'roles'])->name('roles');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// Admin Panel
Route::prefix('panel/admin')->name('panel.admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/network-users', [AdminController::class, 'networkUsers'])->name('network-users');
    Route::get('/packages', [AdminController::class, 'packages'])->name('packages');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    
    // Network Device Management
    Route::get('/mikrotik', [AdminController::class, 'mikrotikRouters'])->name('mikrotik');
    Route::get('/nas', [AdminController::class, 'nasDevices'])->name('nas');
    Route::get('/cisco', [AdminController::class, 'ciscoDevices'])->name('cisco');
    Route::get('/olt', [AdminController::class, 'oltDevices'])->name('olt');
});

// Manager Panel
Route::prefix('panel/manager')->name('panel.manager.')->middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/network-users', [ManagerController::class, 'networkUsers'])->name('network-users');
    Route::get('/sessions', [ManagerController::class, 'sessions'])->name('sessions');
    Route::get('/reports', [ManagerController::class, 'reports'])->name('reports');
});

// Staff Panel
Route::prefix('panel/staff')->name('panel.staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/network-users', [StaffController::class, 'networkUsers'])->name('network-users');
    Route::get('/tickets', [StaffController::class, 'tickets'])->name('tickets');
    
    // Network Device Management (permission-based)
    Route::get('/mikrotik', [StaffController::class, 'mikrotikRouters'])->name('mikrotik');
    Route::get('/nas', [StaffController::class, 'nasDevices'])->name('nas');
    Route::get('/cisco', [StaffController::class, 'ciscoDevices'])->name('cisco');
    Route::get('/olt', [StaffController::class, 'oltDevices'])->name('olt');
});

// Reseller Panel
Route::prefix('panel/reseller')->name('panel.reseller.')->middleware(['auth', 'role:reseller'])->group(function () {
    Route::get('/dashboard', [ResellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [ResellerController::class, 'customers'])->name('customers');
    Route::get('/packages', [ResellerController::class, 'packages'])->name('packages');
    Route::get('/commission', [ResellerController::class, 'commission'])->name('commission');
});

// Sub-Reseller Panel
Route::prefix('panel/sub-reseller')->name('panel.sub-reseller.')->middleware(['auth', 'role:sub-reseller'])->group(function () {
    Route::get('/dashboard', [SubResellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [SubResellerController::class, 'customers'])->name('customers');
    Route::get('/packages', [SubResellerController::class, 'packages'])->name('packages');
    Route::get('/commission', [SubResellerController::class, 'commission'])->name('commission');
});

// Card Distributor Panel
Route::prefix('panel/card-distributor')->name('panel.card-distributor.')->middleware(['auth', 'role:card-distributor'])->group(function () {
    Route::get('/dashboard', [CardDistributorController::class, 'dashboard'])->name('dashboard');
    Route::get('/cards', [CardDistributorController::class, 'cards'])->name('cards');
    Route::get('/sales', [CardDistributorController::class, 'sales'])->name('sales');
    Route::get('/balance', [CardDistributorController::class, 'balance'])->name('balance');
});

// Customer Panel
Route::prefix('panel/customer')->name('panel.customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
    Route::get('/billing', [CustomerController::class, 'billing'])->name('billing');
    Route::get('/usage', [CustomerController::class, 'usage'])->name('usage');
    Route::get('/tickets', [CustomerController::class, 'tickets'])->name('tickets');
});

// Developer Panel
Route::prefix('panel/developer')->name('panel.developer.')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');
    Route::get('/api-docs', [DeveloperController::class, 'apiDocs'])->name('api-docs');
    Route::get('/logs', [DeveloperController::class, 'logs'])->name('logs');
    Route::get('/settings', [DeveloperController::class, 'settings'])->name('settings');
    Route::get('/debug', [DeveloperController::class, 'debug'])->name('debug');
});

