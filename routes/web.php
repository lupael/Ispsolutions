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
| Payment Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\PaymentController;

Route::prefix('payments')->name('payments.')->middleware(['auth'])->group(function () {
    Route::get('invoices/{invoice}', [PaymentController::class, 'show'])->name('show');
    Route::post('invoices/{invoice}/initiate', [PaymentController::class, 'initiate'])->name('initiate');
    Route::post('invoices/{invoice}/manual', [PaymentController::class, 'recordManualPayment'])->name('manual');
    
    // Payment callbacks
    Route::get('success', [PaymentController::class, 'success'])->name('success');
    Route::get('failure', [PaymentController::class, 'failure'])->name('failure');
    Route::get('cancel', [PaymentController::class, 'cancel'])->name('cancel');
});

// Payment webhooks (no auth required) - webhook signature verification must be implemented in PaymentController::webhook
Route::post('webhooks/payment/{gateway}', [PaymentController::class, 'webhook'])->name('webhooks.payment');

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
    
    // ISP/Admin Management
    Route::get('/isp', [SuperAdminController::class, 'ispIndex'])->name('isp.index');
    Route::get('/isp/create', [SuperAdminController::class, 'ispCreate'])->name('isp.create');
    Route::post('/isp', [SuperAdminController::class, 'ispStore'])->name('isp.store');
    
    // Billing Configuration
    Route::get('/billing/fixed', [SuperAdminController::class, 'billingFixed'])->name('billing.fixed');
    Route::get('/billing/user-base', [SuperAdminController::class, 'billingUserBase'])->name('billing.user-base');
    Route::get('/billing/panel-base', [SuperAdminController::class, 'billingPanelBase'])->name('billing.panel-base');
    
    // Payment Gateway Management
    Route::get('/payment-gateway', [SuperAdminController::class, 'paymentGatewayIndex'])->name('payment-gateway.index');
    Route::get('/payment-gateway/create', [SuperAdminController::class, 'paymentGatewayCreate'])->name('payment-gateway.create');
    Route::post('/payment-gateway', [SuperAdminController::class, 'paymentGatewayStore'])->name('payment-gateway.store');
    
    // SMS Gateway Management
    Route::get('/sms-gateway', [SuperAdminController::class, 'smsGatewayIndex'])->name('sms-gateway.index');
    Route::get('/sms-gateway/create', [SuperAdminController::class, 'smsGatewayCreate'])->name('sms-gateway.create');
    Route::post('/sms-gateway', [SuperAdminController::class, 'smsGatewayStore'])->name('sms-gateway.store');
    
    // Logs & Settings
    Route::get('/logs', [SuperAdminController::class, 'logs'])->name('logs');
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
    
    // Customer Management
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::get('/customers/create', [AdminController::class, 'customersCreate'])->name('customers.create');
    Route::get('/customers/{id}/edit', [AdminController::class, 'customersEdit'])->name('customers.edit');
    Route::get('/customers/{id}', [AdminController::class, 'customersShow'])->name('customers.show');
    Route::get('/customers-deleted', [AdminController::class, 'deletedCustomers'])->name('customers.deleted');
    Route::get('/customers-online', [AdminController::class, 'onlineCustomers'])->name('customers.online');
    Route::get('/customers-offline', [AdminController::class, 'offlineCustomers'])->name('customers.offline');
    Route::get('/customers/import-requests', [AdminController::class, 'customerImportRequests'])->name('customers.import-requests');
    Route::get('/customers/pppoe-import', [AdminController::class, 'pppoeCustomerImport'])->name('customers.pppoe-import');
    Route::get('/customers/bulk-update', [AdminController::class, 'bulkUpdateUsers'])->name('customers.bulk-update');
    
    // Accounting & Finance
    Route::get('/accounting/transactions', [AdminController::class, 'accountTransactions'])->name('accounting.transactions');
    Route::get('/accounting/payment-gateway-transactions', [AdminController::class, 'paymentGatewayTransactions'])->name('accounting.payment-gateway-transactions');
    Route::get('/accounting/statement', [AdminController::class, 'accountStatement'])->name('accounting.statement');
    Route::get('/accounting/payable', [AdminController::class, 'accountsPayable'])->name('accounting.payable');
    Route::get('/accounting/receivable', [AdminController::class, 'accountsReceivable'])->name('accounting.receivable');
    Route::get('/accounting/income-expense-report', [AdminController::class, 'incomeExpenseReport'])->name('accounting.income-expense-report');
    Route::get('/accounting/expense-report', [AdminController::class, 'expenseReport'])->name('accounting.expense-report');
    Route::get('/accounting/expenses', [AdminController::class, 'expenses'])->name('accounting.expenses');
    Route::get('/accounting/vat-collections', [AdminController::class, 'vatCollections'])->name('accounting.vat-collections');
    Route::get('/accounting/customer-payments', [AdminController::class, 'customerPayments'])->name('accounting.customer-payments');
    Route::get('/accounting/gateway-customer-payments', [AdminController::class, 'gatewayCustomerPayments'])->name('accounting.gateway-customer-payments');
    
    // Operators Management
    Route::get('/operators', [AdminController::class, 'operators'])->name('operators');
    Route::get('/operators/create', [AdminController::class, 'operatorsCreate'])->name('operators.create');
    Route::get('/operators/{id}/edit', [AdminController::class, 'operatorsEdit'])->name('operators.edit');
    Route::get('/operators/sub-operators', [AdminController::class, 'subOperators'])->name('operators.sub-operators');
    Route::get('/operators/staff', [AdminController::class, 'staff'])->name('operators.staff');
    Route::get('/operators/{id}/profile', [AdminController::class, 'operatorProfile'])->name('operators.profile');
    Route::get('/operators/{id}/special-permissions', [AdminController::class, 'operatorSpecialPermissions'])->name('operators.special-permissions');
    
    // Payment Gateway Management
    Route::get('/payment-gateways', [AdminController::class, 'paymentGateways'])->name('payment-gateways');
    Route::get('/payment-gateways/create', [AdminController::class, 'paymentGatewaysCreate'])->name('payment-gateways.create');
    
    // Network Devices Management
    Route::get('/network/routers', [AdminController::class, 'routers'])->name('network.routers');
    Route::get('/network/routers/create', [AdminController::class, 'routersCreate'])->name('network.routers.create');
    Route::get('/network/olt', [AdminController::class, 'oltList'])->name('network.olt');
    Route::get('/network/olt/create', [AdminController::class, 'oltCreate'])->name('network.olt.create');
    Route::get('/olt/dashboard', [AdminController::class, 'oltDashboard'])->name('olt.dashboard');
    Route::get('/olt/{id}/monitor', [AdminController::class, 'oltMonitor'])->name('olt.monitor');
    Route::get('/olt/{id}/performance', [AdminController::class, 'oltPerformance'])->name('olt.performance');
    Route::get('/olt/templates', [AdminController::class, 'oltTemplates'])->name('olt.templates');
    Route::get('/olt/snmp-traps', [AdminController::class, 'oltSnmpTraps'])->name('olt.snmp-traps');
    Route::get('/olt/firmware', [AdminController::class, 'oltFirmware'])->name('olt.firmware');
    Route::get('/olt/backups', [AdminController::class, 'oltBackups'])->name('olt.backups');
    Route::get('/network/devices', [AdminController::class, 'devices'])->name('network.devices');
    Route::get('/network/device-monitors', [AdminController::class, 'deviceMonitors'])->name('network.device-monitors');
    Route::get('/network/devices-map', [AdminController::class, 'devicesMap'])->name('network.devices.map');
    Route::get('/network/ipv4-pools', [AdminController::class, 'ipv4Pools'])->name('network.ipv4-pools');
    Route::get('/network/ipv6-pools', [AdminController::class, 'ipv6Pools'])->name('network.ipv6-pools');
    Route::get('/network/pppoe-profiles', [AdminController::class, 'pppoeProfiles'])->name('network.pppoe-profiles');
    Route::get('/network/package-fup-edit/{id}', [AdminController::class, 'packageFupEdit'])->name('network.package-fup-edit');
    Route::get('/network/ping-test', [AdminController::class, 'pingTest'])->name('network.ping-test');
    
    // SMS Management
    Route::get('/sms/send', [AdminController::class, 'smsSend'])->name('sms.send');
    Route::get('/sms/broadcast', [AdminController::class, 'smsBroadcast'])->name('sms.broadcast');
    Route::get('/sms/histories', [AdminController::class, 'smsHistories'])->name('sms.histories');
    Route::get('/sms/events', [AdminController::class, 'smsEvents'])->name('sms.events');
    Route::get('/sms/due-date-notification', [AdminController::class, 'dueDateNotification'])->name('sms.due-date-notification');
    Route::get('/sms/payment-link-broadcast', [AdminController::class, 'paymentLinkBroadcast'])->name('sms.payment-link-broadcast');
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

// Developer Panel (Supreme Authority)
Route::prefix('panel/developer')->name('panel.developer.')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');
    
    // Tenancy Management
    Route::get('/tenancies', [DeveloperController::class, 'tenancies'])->name('tenancies.index');
    Route::get('/tenancies/create', [DeveloperController::class, 'createTenancy'])->name('tenancies.create');
    Route::post('/tenancies', [DeveloperController::class, 'storeTenancy'])->name('tenancies.store');
    Route::post('/tenancies/{tenant}/toggle-status', [DeveloperController::class, 'toggleTenancyStatus'])->name('tenancies.toggle-status');
    
    // Subscription Management
    Route::get('/subscriptions', [DeveloperController::class, 'subscriptions'])->name('subscriptions.index');
    
    // System Access
    Route::get('/access-panel', [DeveloperController::class, 'accessPanel'])->name('access-panel');
    Route::get('/customers/search', [DeveloperController::class, 'searchCustomers'])->name('customers.search');
    Route::get('/customers', [DeveloperController::class, 'allCustomers'])->name('customers.index');
    
    // Audit & Logs
    Route::get('/audit-logs', [DeveloperController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/logs', [DeveloperController::class, 'logs'])->name('logs');
    Route::get('/error-logs', [DeveloperController::class, 'errorLogs'])->name('error-logs');
    
    // API Management
    Route::get('/api-docs', [DeveloperController::class, 'apiDocs'])->name('api-docs');
    Route::get('/api-keys', [DeveloperController::class, 'apiKeys'])->name('api-keys');
    
    // System Tools
    Route::get('/settings', [DeveloperController::class, 'settings'])->name('settings');
    Route::get('/debug', [DeveloperController::class, 'debug'])->name('debug');
});

