<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel\AdminController;
use App\Http\Controllers\Panel\AnalyticsController;
use App\Http\Controllers\Panel\CableTvController;
use App\Http\Controllers\Panel\CardDistributorController;
use App\Http\Controllers\Panel\CustomerController;
use App\Http\Controllers\Panel\DeveloperController;
use App\Http\Controllers\Panel\ManagerController;
use App\Http\Controllers\Panel\StaffController;
use App\Http\Controllers\Panel\SuperAdminController;
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
| Hotspot Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HotspotController;
use App\Http\Controllers\HotspotSelfSignupController;

// Public hotspot self-signup routes (no authentication required)
Route::prefix('hotspot/signup')->name('hotspot.signup.')->group(function () {
    Route::get('/', [HotspotSelfSignupController::class, 'showRegistrationForm'])->name('');
    Route::post('/request-otp', [HotspotSelfSignupController::class, 'requestOtp'])->name('request-otp');

    Route::get('/verify-otp', [HotspotSelfSignupController::class, 'showVerifyOtp'])->name('verify-otp');
    Route::post('/verify-otp', [HotspotSelfSignupController::class, 'verifyOtp'])->name('verify-otp.post');
    Route::post('/resend-otp', [HotspotSelfSignupController::class, 'resendOtp'])->name('resend-otp');

    Route::get('/complete', [HotspotSelfSignupController::class, 'showCompleteProfile'])->name('complete');
    Route::post('/complete', [HotspotSelfSignupController::class, 'completeRegistration'])->name('complete.post');

    Route::get('/payment/{user}', [HotspotSelfSignupController::class, 'showPaymentPage'])->name('payment');
    Route::post('/payment/{user}', [HotspotSelfSignupController::class, 'processPayment'])->name('payment.post');
    Route::get('/payment/callback', [HotspotSelfSignupController::class, 'paymentCallback'])->name('payment.callback');

    Route::get('/success', [HotspotSelfSignupController::class, 'showSuccess'])->name('success');
    Route::get('/error', [HotspotSelfSignupController::class, 'showError'])->name('error');
});

// Admin hotspot management routes
Route::prefix('hotspot')->name('hotspot.')->middleware(['auth'])->group(function () {
    Route::get('/', [HotspotController::class, 'index'])->name('index');
    Route::get('create', [HotspotController::class, 'create'])->name('create');
    Route::post('/', [HotspotController::class, 'store'])->name('store');
    Route::get('{hotspotUser}', [HotspotController::class, 'show'])->name('show');
    Route::get('{hotspotUser}/edit', [HotspotController::class, 'edit'])->name('edit');
    Route::put('{hotspotUser}', [HotspotController::class, 'update'])->name('update');
    Route::delete('{hotspotUser}', [HotspotController::class, 'destroy'])->name('destroy');
    Route::post('{hotspotUser}/suspend', [HotspotController::class, 'suspend'])->name('suspend');
    Route::post('{hotspotUser}/reactivate', [HotspotController::class, 'reactivate'])->name('reactivate');
    Route::post('{hotspotUser}/renew', [HotspotController::class, 'renew'])->name('renew');
});

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
    Route::get('/payment-gateway/settings', [SuperAdminController::class, 'paymentGatewaySettings'])->name('payment-gateway.settings');
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
    Route::get('/packages/create', [AdminController::class, 'packagesCreate'])->name('packages.create');
    Route::post('/packages', [AdminController::class, 'packagesStore'])->name('packages.store');
    Route::get('/packages/{id}/edit', [AdminController::class, 'packagesEdit'])->name('packages.edit');
    Route::put('/packages/{id}', [AdminController::class, 'packagesUpdate'])->name('packages.update');
    Route::delete('/packages/{id}', [AdminController::class, 'packagesDestroy'])->name('packages.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

    // Role Label Settings
    Route::get('/settings/role-labels', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'index'])->name('settings.role-labels');
    Route::put('/settings/role-labels', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'update'])->name('settings.role-labels.update');
    Route::delete('/settings/role-labels/{roleSlug}', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'destroy'])->name('settings.role-labels.destroy');

    // Network Device Management - Legacy routes (kept for backward compatibility)
    // These redirect to the more organized /network/* routes
    Route::get('/mikrotik', function () {
        return redirect()->route('panel.admin.network.routers');
    })->name('mikrotik');
    Route::get('/nas', [AdminController::class, 'nasDevices'])->name('nas');
    Route::get('/cisco', [AdminController::class, 'ciscoDevices'])->name('cisco');
    Route::get('/olt', function () {
        return redirect()->route('panel.admin.network.olt');
    })->name('olt');

    // Customer Management
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::get('/customers/create', [AdminController::class, 'customersCreate'])->name('customers.create');
    Route::get('/customers/import-requests', [AdminController::class, 'customerImportRequests'])->name('customers.import-requests');
    Route::get('/customers/pppoe-import', [AdminController::class, 'pppoeCustomerImport'])->name('customers.pppoe-import');
    Route::get('/customers/bulk-update', [AdminController::class, 'bulkUpdateUsers'])->name('customers.bulk-update');
    Route::get('/customers/{id}/edit', [AdminController::class, 'customersEdit'])->name('customers.edit');
    Route::get('/customers/{id}', [AdminController::class, 'customersShow'])->name('customers.show');
    Route::get('/customers-deleted', [AdminController::class, 'deletedCustomers'])->name('customers.deleted');
    Route::get('/customers-online', [AdminController::class, 'onlineCustomers'])->name('customers.online');
    Route::get('/customers-offline', [AdminController::class, 'offlineCustomers'])->name('customers.offline');

    // Zone Management
    Route::get('/zones', [\App\Http\Controllers\Panel\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/create', [\App\Http\Controllers\Panel\ZoneController::class, 'create'])->name('zones.create');
    Route::post('/zones', [\App\Http\Controllers\Panel\ZoneController::class, 'store'])->name('zones.store');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Panel\ZoneController::class, 'show'])->name('zones.show');
    Route::get('/zones/{zone}/edit', [\App\Http\Controllers\Panel\ZoneController::class, 'edit'])->name('zones.edit');
    Route::put('/zones/{zone}', [\App\Http\Controllers\Panel\ZoneController::class, 'update'])->name('zones.update');
    Route::delete('/zones/{zone}', [\App\Http\Controllers\Panel\ZoneController::class, 'destroy'])->name('zones.destroy');
    Route::get('/zones-report', [\App\Http\Controllers\Panel\ZoneController::class, 'report'])->name('zones.report');
    Route::post('/zones/bulk-assign', [\App\Http\Controllers\Panel\ZoneController::class, 'bulkAssign'])->name('zones.bulk-assign');

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
    Route::put('/operators/{id}/special-permissions', [AdminController::class, 'updateOperatorSpecialPermissions'])->name('operators.special-permissions.update');
    
    // Operator Impersonation
    Route::post('/operators/{operatorId}/login-as', [AdminController::class, 'loginAsOperator'])->name('operators.login-as');
    
    // Stop impersonating - accessible from impersonated sessions
    Route::post('/stop-impersonating', [AdminController::class, 'stopImpersonating'])
        ->name('stop-impersonating')
        ->withoutMiddleware('role:admin')
        ->middleware('auth');

    // Operator Wallet Management
    Route::get('/operators/wallets', [AdminController::class, 'operatorWallets'])->name('operators.wallets');
    Route::get('/operators/{operator}/add-funds', [AdminController::class, 'addOperatorFunds'])->name('operators.add-funds');
    Route::post('/operators/{operator}/add-funds', [AdminController::class, 'storeOperatorFunds'])->name('operators.store-funds');
    Route::get('/operators/{operator}/deduct-funds', [AdminController::class, 'deductOperatorFunds'])->name('operators.deduct-funds');
    Route::post('/operators/{operator}/deduct-funds', [AdminController::class, 'processDeductOperatorFunds'])->name('operators.process-deduct-funds');
    Route::get('/operators/{operator}/wallet-history', [AdminController::class, 'operatorWalletHistory'])->name('operators.wallet-history');

    // Operator Package Rates
    Route::get('/operators/package-rates', [AdminController::class, 'operatorPackageRates'])->name('operators.package-rates');
    Route::get('/operators/{operator}/assign-package-rate', [AdminController::class, 'assignOperatorPackageRate'])->name('operators.assign-package-rate');
    Route::post('/operators/{operator}/assign-package-rate', [AdminController::class, 'storeOperatorPackageRate'])->name('operators.store-package-rate');
    Route::delete('/operators/{operator}/package-rate/{package}', [AdminController::class, 'deleteOperatorPackageRate'])->name('operators.delete-package-rate');

    // Operator SMS Rates
    Route::get('/operators/sms-rates', [AdminController::class, 'operatorSmsRates'])->name('operators.sms-rates');
    Route::get('/operators/{operator}/assign-sms-rate', [AdminController::class, 'assignOperatorSmsRate'])->name('operators.assign-sms-rate');
    Route::post('/operators/{operator}/assign-sms-rate', [AdminController::class, 'storeOperatorSmsRate'])->name('operators.store-sms-rate');
    Route::delete('/operators/{operator}/sms-rate', [AdminController::class, 'deleteOperatorSmsRate'])->name('operators.delete-sms-rate');

    // Payment Gateway Management
    Route::get('/payment-gateways', [AdminController::class, 'paymentGateways'])->name('payment-gateways');
    Route::get('/payment-gateways/create', [AdminController::class, 'paymentGatewaysCreate'])->name('payment-gateways.create');

    // Package Profile Mappings
    Route::prefix('packages/{package}/mappings')->name('packages.mappings.')->scopeBindings()->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'store'])->name('store');
        Route::get('/{mapping}/edit', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'edit'])->name('edit');
        Route::put('/{mapping}', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'update'])->name('update');
        Route::delete('/{mapping}', [\App\Http\Controllers\Panel\PackageProfileMappingController::class, 'destroy'])->name('destroy');
    });

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
    
    // SMS Gateway Management
    Route::prefix('sms/gateways')->name('sms.gateways.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'store'])->name('store');
        Route::get('/{gateway}', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'show'])->name('show');
        Route::get('/{gateway}/edit', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'edit'])->name('edit');
        Route::put('/{gateway}', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'update'])->name('update');
        Route::delete('/{gateway}', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'destroy'])->name('destroy');
        Route::post('/{gateway}/test', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'test'])->name('test');
        Route::post('/{gateway}/set-default', [\App\Http\Controllers\Panel\SmsGatewayController::class, 'setDefault'])->name('set-default');
    });

    // Logs Management with role-based access control
    // System/Activity Log - Developer, Super Admin, Admin
    Route::get('/logs/system', [AdminController::class, 'activityLogs'])
        ->name('logs.system')
        ->middleware('role:developer,super-admin,admin');

    // Laravel Log - Developer only
    Route::get('/logs/laravel', [AdminController::class, 'laravelLogs'])
        ->name('logs.laravel')
        ->middleware('role:developer');

    // Scheduler Log - Developer, Super Admin
    Route::get('/logs/scheduler', [AdminController::class, 'schedulerLogs'])
        ->name('logs.scheduler')
        ->middleware('role:developer,super-admin');

    // Router Log - Developer, Super Admin, Admin
    Route::get('/logs/router', [AdminController::class, 'routerLogs'])
        ->name('logs.router')
        ->middleware('role:developer,super-admin,admin');

    // PPP Log - All (filtered by router/customer ownership)
    Route::get('/logs/ppp', [AdminController::class, 'pppLogs'])
        ->name('logs.ppp');

    // Hotspot Log - All (filtered by router/customer ownership)
    Route::get('/logs/hotspot', [AdminController::class, 'hotspotLogs'])
        ->name('logs.hotspot');

    // RADIUS Log - Developer only
    Route::get('/logs/radius', [AdminController::class, 'radiusLogs'])
        ->name('logs.radius')
        ->middleware('role:developer');

    // Activity Log - Developer, Super Admin, Admin
    Route::get('/logs/activity', [AdminController::class, 'activityLogs'])
        ->name('logs.activity')
        ->middleware('role:developer,super-admin,admin');

    // Analytics Routes
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/analytics/revenue-report', [AnalyticsController::class, 'revenueReport'])->name('analytics.revenue-report');
    Route::get('/analytics/customer-report', [AnalyticsController::class, 'customerReport'])->name('analytics.customer-report');
    Route::get('/analytics/service-report', [AnalyticsController::class, 'serviceReport'])->name('analytics.service-report');
    Route::get('/analytics/export', [AnalyticsController::class, 'exportAnalytics'])->name('analytics.export');

    // Analytics API Endpoints (for AJAX requests)
    Route::get('/api/analytics/revenue', [AnalyticsController::class, 'revenueAnalytics'])->name('analytics.api.revenue');
    Route::get('/api/analytics/customers', [AnalyticsController::class, 'customerAnalytics'])->name('analytics.api.customers');
    Route::get('/api/analytics/services', [AnalyticsController::class, 'serviceAnalytics'])->name('analytics.api.services');
    Route::get('/api/analytics/growth', [AnalyticsController::class, 'growthMetrics'])->name('analytics.api.growth');
    Route::get('/api/analytics/performance', [AnalyticsController::class, 'performanceIndicators'])->name('analytics.api.performance');

    // Yearly Reports
    Route::prefix('yearly-reports')->name('yearly-reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\YearlyReportController::class, 'index'])->name('index');
        Route::get('/card-distributor-payments', [\App\Http\Controllers\Panel\YearlyReportController::class, 'cardDistributorPayments'])->name('card-distributor-payments');
        Route::get('/cash-in', [\App\Http\Controllers\Panel\YearlyReportController::class, 'cashIn'])->name('cash-in');
        Route::get('/cash-out', [\App\Http\Controllers\Panel\YearlyReportController::class, 'cashOut'])->name('cash-out');
        Route::get('/operator-income', [\App\Http\Controllers\Panel\YearlyReportController::class, 'operatorIncome'])->name('operator-income');
        Route::get('/expenses', [\App\Http\Controllers\Panel\YearlyReportController::class, 'expenses'])->name('expenses');
        Route::get('/{reportType}/export-excel', [\App\Http\Controllers\Panel\YearlyReportController::class, 'exportExcel'])->name('export-excel');
        Route::get('/{reportType}/export-pdf', [\App\Http\Controllers\Panel\YearlyReportController::class, 'exportPdf'])->name('export-pdf');
    });

    // Cable TV Management
    Route::prefix('cable-tv')->name('cable-tv.')->group(function () {
        Route::get('/', [CableTvController::class, 'index'])->name('index');
        Route::get('/create', [CableTvController::class, 'create'])->name('create');
        Route::post('/', [CableTvController::class, 'store'])->name('store');
        Route::get('/{subscription}/edit', [CableTvController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [CableTvController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [CableTvController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/suspend', [CableTvController::class, 'suspend'])->name('suspend');
        Route::post('/{subscription}/reactivate', [CableTvController::class, 'reactivate'])->name('reactivate');
        Route::post('/{subscription}/renew', [CableTvController::class, 'renew'])->name('renew');

        Route::get('/packages', [CableTvController::class, 'packagesIndex'])->name('packages.index');
        Route::get('/channels', [CableTvController::class, 'channelsIndex'])->name('channels.index');
    });

    // PDF & Excel Export Routes
    Route::prefix('export')->name('export.')->group(function () {
        // Invoice PDF exports
        Route::get('/invoice/{invoice}/pdf', [AdminController::class, 'downloadInvoicePdf'])->name('invoice.pdf');
        Route::get('/invoice/{invoice}/view', [AdminController::class, 'streamInvoicePdf'])->name('invoice.view');

        // Payment receipt PDF
        Route::get('/payment/{payment}/receipt', [AdminController::class, 'downloadPaymentReceiptPdf'])->name('payment.receipt');

        // Excel exports
        Route::get('/invoices/excel', [AdminController::class, 'exportInvoices'])->name('invoices.excel');
        Route::get('/payments/excel', [AdminController::class, 'exportPayments'])->name('payments.excel');

        // Customer statement PDF
        Route::get('/customer/{customer}/statement', [AdminController::class, 'customerStatementPdf'])->name('customer.statement');

        // Monthly report PDF
        Route::get('/monthly-report', [AdminController::class, 'monthlyReportPdf'])->name('monthly.report');
    });

    // Accounting report exports (outside export prefix to match view expectations)
    Route::get('/reports/transactions/export', [AdminController::class, 'exportTransactions'])->name('reports.transactions.export');
    Route::get('/reports/vat-collections/export', [AdminController::class, 'exportVatCollections'])->name('reports.vat-collections.export');
    Route::get('/reports/expenses/export', [AdminController::class, 'exportExpenseReport'])->name('reports.expenses.export');
    Route::get('/reports/income-expense/export', [AdminController::class, 'exportIncomeExpenseReport'])->name('reports.income-expense.export');
    Route::get('/reports/receivable/export', [AdminController::class, 'exportReceivable'])->name('reports.receivable.export');
    Route::get('/reports/payable/export', [AdminController::class, 'exportPayable'])->name('reports.payable.export');
});

// Sales Manager Panel
Route::prefix('panel/sales-manager')->name('panel.sales-manager.')->middleware(['auth', 'role:sales-manager'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Panel\SalesManagerController::class, 'dashboard'])->name('dashboard');

    // Admin (ISP Client) Management
    Route::get('/admins', [\App\Http\Controllers\Panel\SalesManagerController::class, 'admins'])->name('admins.index');

    // Lead Management
    Route::get('/leads/affiliate', [\App\Http\Controllers\Panel\SalesManagerController::class, 'affiliateLeads'])->name('leads.affiliate');
    Route::get('/leads/create', [\App\Http\Controllers\Panel\SalesManagerController::class, 'createLead'])->name('leads.create');

    // Sales Tracking
    Route::get('/sales-comments', [\App\Http\Controllers\Panel\SalesManagerController::class, 'salesComments'])->name('sales-comments');

    // Subscription Management
    Route::get('/subscriptions/bills', [\App\Http\Controllers\Panel\SalesManagerController::class, 'subscriptionBills'])->name('subscriptions.bills');
    Route::get('/subscriptions/payment/create', [\App\Http\Controllers\Panel\SalesManagerController::class, 'createSubscriptionPayment'])->name('subscriptions.payment.create');
    Route::get('/subscriptions/pending-payments', [\App\Http\Controllers\Panel\SalesManagerController::class, 'pendingSubscriptionPayments'])->name('subscriptions.pending-payments');

    // Communication
    Route::get('/notice-broadcast', [\App\Http\Controllers\Panel\SalesManagerController::class, 'noticeBroadcast'])->name('notice-broadcast');

    // Security
    Route::get('/change-password', [\App\Http\Controllers\Panel\SalesManagerController::class, 'changePassword'])->name('change-password');
    Route::get('/secure-login', [\App\Http\Controllers\Panel\SalesManagerController::class, 'secureLogin'])->name('secure-login');
});

// Manager Panel
Route::prefix('panel/manager')->name('panel.manager.')->middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/network-users', [ManagerController::class, 'networkUsers'])->name('network-users');
    Route::get('/sessions', [ManagerController::class, 'sessions'])->name('sessions');
    Route::get('/reports', [ManagerController::class, 'reports'])->name('reports');
    Route::get('/customers', [ManagerController::class, 'customers'])->name('customers.index');
    Route::get('/payments', [ManagerController::class, 'payments'])->name('payments.index');
    Route::get('/complaints', [ManagerController::class, 'complaints'])->name('complaints.index');
});

// Operator Panel
Route::prefix('panel/operator')->name('panel.operator.')->middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Panel\OperatorController::class, 'dashboard'])->name('dashboard');
    Route::get('/sub-operators', [\App\Http\Controllers\Panel\OperatorController::class, 'subOperators'])->name('sub-operators.index');
    Route::get('/customers', [\App\Http\Controllers\Panel\OperatorController::class, 'customers'])->name('customers.index');
    Route::get('/bills', [\App\Http\Controllers\Panel\OperatorController::class, 'bills'])->name('bills.index');
    Route::get('/payments/create', [\App\Http\Controllers\Panel\OperatorController::class, 'createPayment'])->name('payments.create');
    Route::post('/payments', [\App\Http\Controllers\Panel\OperatorController::class, 'storePayment'])->name('payments.store');
    Route::get('/cards', [\App\Http\Controllers\Panel\OperatorController::class, 'cards'])->name('cards.index');
    Route::get('/complaints', [\App\Http\Controllers\Panel\OperatorController::class, 'complaints'])->name('complaints.index');
    Route::get('/reports', [\App\Http\Controllers\Panel\OperatorController::class, 'reports'])->name('reports.index');
    Route::get('/sms', [\App\Http\Controllers\Panel\OperatorController::class, 'sms'])->name('sms.index');
    Route::post('/sms/send', [\App\Http\Controllers\Panel\OperatorController::class, 'sendSms'])->name('sms.send');
    Route::get('/packages', [\App\Http\Controllers\Panel\OperatorController::class, 'packages'])->name('packages');
    Route::get('/commission', [\App\Http\Controllers\Panel\OperatorController::class, 'commission'])->name('commission');
});

// Sub-Operator Panel
Route::prefix('panel/sub-operator')->name('panel.sub-operator.')->middleware(['auth', 'role:sub-operator'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Panel\SubOperatorController::class, 'dashboard'])->name('dashboard');
    Route::get('/customers', [\App\Http\Controllers\Panel\SubOperatorController::class, 'customers'])->name('customers.index');
    Route::get('/bills', [\App\Http\Controllers\Panel\SubOperatorController::class, 'bills'])->name('bills.index');
    Route::get('/payments/create', [\App\Http\Controllers\Panel\SubOperatorController::class, 'createPayment'])->name('payments.create');
    Route::post('/payments', [\App\Http\Controllers\Panel\SubOperatorController::class, 'storePayment'])->name('payments.store');
    Route::get('/reports', [\App\Http\Controllers\Panel\SubOperatorController::class, 'reports'])->name('reports.index');
    Route::get('/packages', [\App\Http\Controllers\Panel\SubOperatorController::class, 'packages'])->name('packages');
    Route::get('/commission', [\App\Http\Controllers\Panel\SubOperatorController::class, 'commission'])->name('commission');
});

// Accountant Panel
Route::prefix('panel/accountant')->name('panel.accountant.')->middleware(['auth', 'role:accountant'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Panel\AccountantController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports/income-expense', [\App\Http\Controllers\Panel\AccountantController::class, 'incomeExpenseReport'])->name('reports.income-expense');
    Route::get('/reports/payments', [\App\Http\Controllers\Panel\AccountantController::class, 'paymentHistory'])->name('reports.payments');
    Route::get('/reports/statements', [\App\Http\Controllers\Panel\AccountantController::class, 'customerStatements'])->name('reports.statements');
    Route::get('/transactions', [\App\Http\Controllers\Panel\AccountantController::class, 'transactions'])->name('transactions.index');
    Route::get('/expenses', [\App\Http\Controllers\Panel\AccountantController::class, 'expenses'])->name('expenses.index');
    Route::get('/vat/collections', [\App\Http\Controllers\Panel\AccountantController::class, 'vatCollections'])->name('vat.collections');
    Route::get('/payments/history', [\App\Http\Controllers\Panel\AccountantController::class, 'paymentsHistory'])->name('payments.history');
    Route::get('/customers/{customer}/statement', [\App\Http\Controllers\Panel\AccountantController::class, 'customerStatement'])->name('customers.statements');
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

// Card Distributor Panel (DEPRECATED - Role removed from v3.1 role hierarchy)
// Route::prefix('panel/card-distributor')->name('panel.card-distributor.')->middleware(['auth', 'role:card-distributor'])->group(function () {
//     Route::get('/dashboard', [CardDistributorController::class, 'dashboard'])->name('dashboard');
//     Route::get('/cards', [CardDistributorController::class, 'cards'])->name('cards.index');
//     Route::get('/sales', [CardDistributorController::class, 'sales'])->name('sales.index');
//     Route::get('/commissions', [CardDistributorController::class, 'commissions'])->name('commissions.index');
//     Route::get('/balance', [CardDistributorController::class, 'balance'])->name('balance');
// });

// Customer Panel
Route::prefix('panel/customer')->name('panel.customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
    Route::get('/billing', [CustomerController::class, 'billing'])->name('billing');
    Route::get('/usage', [CustomerController::class, 'usage'])->name('usage');
    Route::get('/tickets', [CustomerController::class, 'tickets'])->name('tickets');

    // PDF Export Routes for Customer
    Route::get('/invoice/{invoice}/pdf', [CustomerController::class, 'downloadInvoicePdf'])->name('invoice.pdf');
    Route::get('/invoice/{invoice}/view', [CustomerController::class, 'viewInvoicePdf'])->name('invoice.view');
    Route::get('/payment/{payment}/receipt', [CustomerController::class, 'downloadPaymentReceiptPdf'])->name('payment.receipt');
    Route::get('/statement', [CustomerController::class, 'accountStatementPdf'])->name('statement.pdf');
});

// Developer Panel (Supreme Authority)
Route::prefix('panel/developer')->name('panel.developer.')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');

    // Tenancy Management
    Route::get('/tenancies', [DeveloperController::class, 'tenancies'])->name('tenancies.index');
    Route::get('/tenancies/create', [DeveloperController::class, 'createTenancy'])->name('tenancies.create');
    Route::post('/tenancies', [DeveloperController::class, 'storeTenancy'])->name('tenancies.store');
    Route::post('/tenancies/{tenant}/toggle-status', [DeveloperController::class, 'toggleTenancyStatus'])->name('tenancies.toggle-status');

    // Super Admin Management
    Route::get('/super-admins', [DeveloperController::class, 'superAdmins'])->name('super-admins.index');
    Route::get('/super-admins/create', [DeveloperController::class, 'createSuperAdmin'])->name('super-admins.create');

    // Admin (ISP) Management
    Route::get('/admins', [DeveloperController::class, 'allAdmins'])->name('admins.index');

    // Subscription Management
    Route::get('/subscriptions', [DeveloperController::class, 'subscriptionPlans'])->name('subscriptions.index');

    // Gateway Configuration
    Route::get('/gateways/payment', [DeveloperController::class, 'paymentGateways'])->name('gateways.payment');
    Route::get('/gateways/sms', [DeveloperController::class, 'smsGateways'])->name('gateways.sms');

    // VPN Pools
    Route::get('/vpn-pools', [DeveloperController::class, 'vpnPools'])->name('vpn-pools');

    // System Access
    Route::get('/access-panel', [DeveloperController::class, 'accessPanel'])->name('access-panel');
    Route::get('/customers/search', [DeveloperController::class, 'searchCustomers'])->name('customers.search');
    Route::get('/customers', [DeveloperController::class, 'allCustomers'])->name('customers.index');
    Route::get('/customers/{id}', [DeveloperController::class, 'showCustomer'])->name('customers.show');

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

/*
|--------------------------------------------------------------------------
| Shared Panel Features Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Panel\AnalyticsDashboardController;
use App\Http\Controllers\Panel\ApiKeyController;
use App\Http\Controllers\Panel\AuditLogController;
use App\Http\Controllers\Panel\NotificationController;
use App\Http\Controllers\Panel\TwoFactorAuthController;

// Audit Logs (Available to all authenticated users with proper permissions)
Route::prefix('audit-logs')->name('audit-logs.')->middleware(['auth', 'can:view-audit-logs'])->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('index');
    Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});

// Two-Factor Authentication
Route::prefix('2fa')->name('2fa.')->middleware(['auth'])->group(function () {
    Route::get('/', [TwoFactorAuthController::class, 'index'])->name('index');
    Route::get('/enable', [TwoFactorAuthController::class, 'enable'])->name('enable');
    Route::post('/verify', [TwoFactorAuthController::class, 'verify'])->name('verify');
    Route::get('/recovery-codes', [TwoFactorAuthController::class, 'recoveryCodes'])->name('recovery-codes');
    Route::post('/regenerate-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])->name('regenerate-codes');
    Route::delete('/disable', [TwoFactorAuthController::class, 'disable'])->name('disable');
});

// API Key Management
Route::prefix('api-keys')->name('api-keys.')->middleware(['auth', 'can:manage-api-keys'])->group(function () {
    Route::get('/', [ApiKeyController::class, 'index'])->name('index');
    Route::get('/create', [ApiKeyController::class, 'create'])->name('create');
    Route::post('/', [ApiKeyController::class, 'store'])->name('store');
    Route::get('/{apiKey}', [ApiKeyController::class, 'show'])->name('show');
    Route::get('/{apiKey}/edit', [ApiKeyController::class, 'edit'])->name('edit');
    Route::put('/{apiKey}', [ApiKeyController::class, 'update'])->name('update');
    Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
});

// Analytics Dashboard
Route::prefix('analytics')->name('analytics.')->middleware(['auth', 'can:view-analytics'])->group(function () {
    Route::get('/dashboard', [AnalyticsDashboardController::class, 'index'])->name('dashboard');
    Route::get('/revenue', [AnalyticsDashboardController::class, 'revenue'])->name('revenue');
    Route::get('/customers', [AnalyticsDashboardController::class, 'customers'])->name('customers');
});

// Notification Center
Route::prefix('notifications')->name('notifications.')->middleware(['auth'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
    Route::post('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
    Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
});
