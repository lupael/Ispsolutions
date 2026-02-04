<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Panel\ISPController; // Changed from AdminController to ISPController
use App\Http\Controllers\Panel\AnalyticsController;
use App\Http\Controllers\Panel\CableTvController;
use App\Http\Controllers\Panel\CardDistributorController;
use App\Http\Controllers\Panel\CustomerController;
use App\Http\Controllers\Panel\DeveloperController;
use App\Http\Controllers\Panel\ManagerController;
use App\Http\Controllers\Panel\MasterPackageController;
use App\Http\Controllers\Panel\NasNetwatchController;
use App\Http\Controllers\Panel\OnuController;
use App\Http\Controllers\Panel\OperatorPackageController;
use App\Http\Controllers\Panel\RouterBackupController;
use App\Http\Controllers\Panel\RouterConfigurationController;
use App\Http\Controllers\Panel\RouterFailoverController;
use App\Http\Controllers\Panel\RouterProvisioningController;
use App\Http\Controllers\Panel\SearchController;
use App\Http\Controllers\Panel\StaffController;
use App\Http\Controllers\Panel\SuperAdminController;
use App\Http\Controllers\Panel\TicketController;
use Illuminate\Support\Facades\Auth;
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

// Language switcher route
Route::post('/language/switch', [LanguageController::class, 'switch'])->middleware('auth')->name('language.switch');

// Password confirmation routes
Route::middleware('auth')->group(function () {
    Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->name('password.confirm.store');
});

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

// Payment webhooks
Route::post('webhooks/payment/{gateway}', [PaymentController::class, 'webhook'])
    ->middleware('rate_limit:webhooks')
    ->name('webhooks.payment');

/*
|--------------------------------------------------------------------------
| Hotspot Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HotspotController;
use App\Http\Controllers\HotspotLoginController;
use App\Http\Controllers\HotspotSelfSignupController;

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

Route::prefix('hotspot/login')->name('hotspot.login')->group(function () {
    Route::get('/', [HotspotLoginController::class, 'showLoginForm'])->name('');
    Route::post('/request-otp', [HotspotLoginController::class, 'requestLoginOtp'])->name('.request-otp');
    Route::get('/verify-otp', [HotspotLoginController::class, 'showVerifyLoginOtp'])->name('.verify-otp');
    Route::post('/verify-otp', [HotspotLoginController::class, 'verifyLoginOtp'])->name('.verify-otp.post');
    Route::get('/device-conflict', [HotspotLoginController::class, 'showDeviceConflict'])->name('.device-conflict');
    Route::post('/force-login', [HotspotLoginController::class, 'forceLogin'])->name('.force-login');
    Route::get('/link/{token}', [HotspotLoginController::class, 'processLinkLogin'])->name('.link-login');
    Route::post('/federated', [HotspotLoginController::class, 'federatedLogin'])->name('.federated');
});

Route::prefix('hotspot')->name('hotspot.')->middleware(['hotspot.auth'])->group(function () {
    Route::get('/dashboard', [HotspotLoginController::class, 'showDashboard'])->name('dashboard');
    Route::post('/logout', [HotspotLoginController::class, 'logout'])->name('logout');
    Route::get('/link-dashboard', [HotspotLoginController::class, 'showLinkDashboard'])->name('link-dashboard');
});

Route::prefix('hotspot')->name('hotspot.')->middleware(['auth'])->group(function () {
    Route::get('/', [HotspotController::class, 'index'])->name('index');
    Route::get('create', [HotspotController::class, 'create'])->name('create');
    Route::post('/', [HotspotController::class, 'store'])->name('store');
    Route::get('{hotspotUser}', [HotspotController::class, 'show'])->name('show');
    Route::get('{hotspotUser}/edit', [HotspotController::class, 'edit'])->name('edit');
    Route::put('{hotspotUser}', [HotspotController::class, 'update'])->name('update');
    Route::delete('{hotspotUser}', [HotspotController::class, 'destroy'])->middleware('password.confirm')->name('destroy');
    Route::post('{hotspotUser}/suspend', [HotspotController::class, 'suspend'])->name('suspend');
    Route::post('{hotspotUser}/reactivate', [HotspotController::class, 'reactivate'])->name('reactivate');
    Route::post('{hotspotUser}/renew', [HotspotController::class, 'renew'])->name('renew');
    Route::post('/generate-link', [HotspotLoginController::class, 'generateLinkLogin'])->name('generate-link');
});

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\PageController;

Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/support', [PageController::class, 'support'])->name('support');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        // UPDATED: Role to route mapping (admin changed to isp)
        $roleRoutes = [
            'super-admin'      => 'panel.super-admin.dashboard',
            'isp'              => 'panel.isp.dashboard', 
            'developer'        => 'panel.developer.dashboard',
            'manager'          => 'panel.manager.dashboard',
            'operator'         => 'panel.operator.dashboard',
            'sub-operator'     => 'panel.sub-operator.dashboard',
            'card-distributor' => 'panel.card-distributor.dashboard',
            'staff'            => 'panel.staff.dashboard',
            'customer'         => 'panel.customer.dashboard',
        ];

        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        Auth::logout();
        return redirect()->route('login')->withErrors([
            'email' => 'Your account does not have a valid role assigned. Please contact an administrator.',
        ]);
    }

    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Role-Based Panel Routes
|--------------------------------------------------------------------------
*/

// Global Search Route
Route::get('/panel/search', [SearchController::class, 'search'])->middleware(['auth'])->name('panel.search');

// Super Admin Panel
Route::prefix('panel/super-admin')->name('panel.super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::get('/users/create', [SuperAdminController::class, 'usersCreate'])->name('users.create');
    Route::post('/users', [SuperAdminController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{id}/edit', [SuperAdminController::class, 'usersEdit'])->name('users.edit');
    Route::put('/users/{id}', [SuperAdminController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/users/{id}', [SuperAdminController::class, 'usersDestroy'])->middleware('password.confirm')->name('users.destroy');
    Route::get('/roles', [SuperAdminController::class, 'roles'])->name('roles');
    Route::get('/isp', [SuperAdminController::class, 'ispIndex'])->name('isp.index');
    Route::get('/isp/create', [SuperAdminController::class, 'ispCreate'])->name('isp.create');
    Route::post('/isp', [SuperAdminController::class, 'ispStore'])->name('isp.store');
    Route::get('/isp/{id}/edit', [SuperAdminController::class, 'ispEdit'])->name('isp.edit');
    Route::put('/isp/{id}', [SuperAdminController::class, 'ispUpdate'])->name('isp.update');
    Route::get('/billing/fixed', [SuperAdminController::class, 'billingFixed'])->name('billing.fixed');
    Route::post('/billing/fixed', [SuperAdminController::class, 'billingFixedStore'])->name('billing.fixed.store');
    Route::get('/billing/user-base', [SuperAdminController::class, 'billingUserBase'])->name('billing.user-base');
    Route::post('/billing/user-base', [SuperAdminController::class, 'billingUserBaseStore'])->name('billing.user-base.store');
    Route::get('/billing/panel-base', [SuperAdminController::class, 'billingPanelBase'])->name('billing.panel-base');
    Route::post('/billing/panel-base', [SuperAdminController::class, 'billingPanelBaseStore'])->name('billing.panel-base.store');
    Route::get('/payment-gateway', [SuperAdminController::class, 'paymentGatewayIndex'])->name('payment-gateway.index');
    Route::get('/payment-gateway/create', [SuperAdminController::class, 'paymentGatewayCreate'])->name('payment-gateway.create');
    Route::get('/payment-gateway/settings', [SuperAdminController::class, 'paymentGatewaySettings'])->name('payment-gateway.settings');
    Route::post('/payment-gateway', [SuperAdminController::class, 'paymentGatewayStore'])->name('payment-gateway.store');
    Route::get('/payment-gateway/{id}/edit', [SuperAdminController::class, 'paymentGatewayEdit'])->name('payment-gateway.edit');
    Route::put('/payment-gateway/{id}', [SuperAdminController::class, 'paymentGatewayUpdate'])->name('payment-gateway.update');
    Route::delete('/payment-gateway/{id}', [SuperAdminController::class, 'paymentGatewayDestroy'])->name('payment-gateway.destroy');
    Route::get('/sms-gateway', [SuperAdminController::class, 'smsGatewayIndex'])->name('sms-gateway.index');
    Route::get('/sms-gateway/create', [SuperAdminController::class, 'smsGatewayCreate'])->name('sms-gateway.create');
    Route::post('/sms-gateway', [SuperAdminController::class, 'smsGatewayStore'])->name('sms-gateway.store');
    Route::get('/sms-gateway/{id}/edit', [SuperAdminController::class, 'smsGatewayEdit'])->name('sms-gateway.edit');
    Route::put('/sms-gateway/{id}', [SuperAdminController::class, 'smsGatewayUpdate'])->name('sms-gateway.update');
    Route::delete('/sms-gateway/{id}', [SuperAdminController::class, 'smsGatewayDestroy'])->name('sms-gateway.destroy');
    Route::get('/logs', [SuperAdminController::class, 'logs'])->name('logs');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// UPDATED: ISP Panel (formerly Admin Panel)
Route::prefix('panel/isp')->name('panel.isp.')->middleware(['auth', 'tenant', 'role:isp'])->group(function () {
    Route::get('/dashboard', [ISPController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [ISPController::class, 'users'])->name('users');
    Route::get('/users/create', [ISPController::class, 'usersCreate'])->name('users.create');
    Route::post('/users', [ISPController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{id}/edit', [ISPController::class, 'usersEdit'])->name('users.edit');
    Route::put('/users/{id}', [ISPController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/users/{id}', [ISPController::class, 'usersDestroy'])->middleware('password.confirm')->name('users.destroy');

    Route::get('/packages', [ISPController::class, 'packages'])->name('packages.index');
    Route::get('/packages/create', [ISPController::class, 'packagesCreate'])->name('packages.create');
    Route::post('/packages', [ISPController::class, 'packagesStore'])->name('packages.store');
    Route::get('/packages/{id}/edit', [ISPController::class, 'packagesEdit'])->name('packages.edit');
    Route::put('/packages/{id}', [ISPController::class, 'packagesUpdate'])->name('packages.update');
    Route::delete('/packages/{id}', [ISPController::class, 'packagesDestroy'])->name('packages.destroy');

    Route::get('/packages/{package}/profiles', [\App\Http\Controllers\Panel\PackageProfileController::class, 'index'])->name('packages.profiles');
    Route::put('/packages/{package}/profiles', [\App\Http\Controllers\Panel\PackageProfileController::class, 'update'])->name('packages.profiles.update');
    Route::post('/packages/profiles/apply-to-customer', [\App\Http\Controllers\Panel\PackageProfileController::class, 'applyToCustomer'])->name('packages.profiles.apply');
    Route::get('/routers/{router}/profiles', [\App\Http\Controllers\Panel\PackageProfileController::class, 'getRouterProfiles'])->name('routers.profiles');

    Route::get('/settings', [ISPController::class, 'settings'])->name('settings');
    Route::get('/settings/role-labels', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'index'])->name('settings.role-labels');
    Route::put('/settings/role-labels', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'update'])->name('settings.role-labels.update');
    Route::delete('/settings/role-labels/{roleSlug}', [\App\Http\Controllers\Panel\RoleLabelSettingController::class, 'destroy'])->middleware('password.confirm')->name('settings.role-labels.destroy');

    Route::resource('custom-fields', \App\Http\Controllers\Panel\CustomerCustomFieldController::class);
    Route::post('/custom-fields/reorder', [\App\Http\Controllers\Panel\CustomerCustomFieldController::class, 'reorder'])->name('custom-fields.reorder');
    Route::resource('billing-profiles', \App\Http\Controllers\Panel\BillingProfileController::class);
    Route::get('/onboarding', [\App\Http\Controllers\Panel\MinimumConfigurationController::class, 'index'])->name('onboarding');
    Route::get('/backup-settings', [\App\Http\Controllers\Panel\BackupSettingController::class, 'index'])->name('backup-settings.index');
    Route::get('/backup-settings/create', [\App\Http\Controllers\Panel\BackupSettingController::class, 'create'])->name('backup-settings.create');
    Route::post('/backup-settings', [\App\Http\Controllers\Panel\BackupSettingController::class, 'store'])->name('backup-settings.store');
    Route::get('/backup-settings/edit', [\App\Http\Controllers\Panel\BackupSettingController::class, 'edit'])->name('backup-settings.edit');
    Route::put('/backup-settings', [\App\Http\Controllers\Panel\BackupSettingController::class, 'update'])->name('backup-settings.update');
    Route::resource('special-permissions', \App\Http\Controllers\Panel\SpecialPermissionController::class)->except(['show', 'edit', 'update']);

    Route::get('/users/{user}/wallet/adjust', [\App\Http\Controllers\Panel\WalletController::class, 'adjustForm'])->name('wallet.adjust-form');
    Route::post('/users/{user}/wallet/adjust', [\App\Http\Controllers\Panel\WalletController::class, 'adjust'])->middleware('password.confirm')->name('wallet.adjust');
    Route::get('/users/{user}/wallet/history', [\App\Http\Controllers\Panel\WalletController::class, 'history'])->name('wallet.history');

    Route::get('/ip-pools/migrate', [\App\Http\Controllers\Panel\IpPoolMigrationController::class, 'index'])->name('ip-pools.migrate');
    Route::post('/ip-pools/migrate/validate', [\App\Http\Controllers\Panel\IpPoolMigrationController::class, 'validateMigration'])->name('ip-pools.migrate.validate');
    Route::post('/ip-pools/migrate/start', [\App\Http\Controllers\Panel\IpPoolMigrationController::class, 'start'])->name('ip-pools.migrate.start');
    Route::get('/ip-pools/migration-progress/{migrationId}', [\App\Http\Controllers\Panel\IpPoolMigrationController::class, 'index'])->name('ip-pools.migration-progress');

    // Network Device Management
    Route::get('/mikrotik', function () {
        return redirect()->route('panel.isp.network.routers');
    })->name('mikrotik');
    Route::get('/cisco', [ISPController::class, 'ciscoDevices'])->name('cisco');
    Route::get('/olt', function () {
        return redirect()->route('panel.isp.network.olt');
    })->name('olt');

    // Customer Management
    Route::get('/customers', [ISPController::class, 'customers'])->name('customers.index');
    Route::get('/customers/create', [ISPController::class, 'customersCreate'])->name('customers.create');
    Route::post('/customers', [ISPController::class, 'customersStore'])->name('customers.store');
    Route::get('/customers/import-requests', [ISPController::class, 'customerImportRequests'])->name('customers.import-requests');
    Route::get('/customers/pppoe-import', [\App\Http\Controllers\Panel\CustomerImportController::class, 'index'])->name('customers.pppoe-import');
    Route::post('/customers/pppoe-import', [\App\Http\Controllers\Panel\CustomerImportController::class, 'store'])->name('customers.pppoe-import.store');
    Route::get('/customers/bulk-update', [ISPController::class, 'bulkUpdateUsers'])->name('customers.bulk-update');
    Route::get('/customers/{id}/edit', [ISPController::class, 'customersEdit'])->name('customers.edit');
    Route::put('/customers/{id}', [ISPController::class, 'customersUpdate'])->name('customers.update');
    Route::patch('/customers/{id}/partial', [ISPController::class, 'customersPartialUpdate'])->name('customers.partial-update');
    Route::delete('/customers/{id}', [ISPController::class, 'customersDestroy'])->middleware('password.confirm')->name('customers.destroy');
    Route::post('/customers/{id}/restore', [ISPController::class, 'restoreCustomer'])->name('customers.restore');
    Route::delete('/customers/{id}/force-delete', [ISPController::class, 'forceDeleteCustomer'])->middleware('password.confirm')->name('customers.force-delete');
    Route::post('/customers/{id}/suspend', [ISPController::class, 'customersSuspend'])->name('customers.suspend');
    Route::post('/customers/{id}/activate', [ISPController::class, 'customersActivate'])->name('customers.activate');
    Route::get('/customers/{id}', [ISPController::class, 'customersShow'])->name('customers.show');

    // Customer Actions
    Route::post('/customers/{id}/disconnect', [\App\Http\Controllers\Panel\CustomerDisconnectController::class, 'disconnect'])->name('customers.disconnect');
    Route::get('/customers/{id}/change-package', [\App\Http\Controllers\Panel\CustomerPackageChangeController::class, 'edit'])->name('customers.change-package.edit');
    Route::put('/customers/{id}/change-package', [\App\Http\Controllers\Panel\CustomerPackageChangeController::class, 'update'])->name('customers.change-package.update');
    Route::get('/customers/{customer}/bills/create', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'createBill'])->name('customers.bills.create');
    Route::post('/customers/{customer}/bills', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'storeBill'])->name('customers.bills.store');
    Route::get('/customers/{customer}/billing-profile', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'editBillingProfile'])->name('customers.billing-profile.edit');
    Route::put('/customers/{customer}/billing-profile', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'updateBillingProfile'])->name('customers.billing-profile.update');
    Route::get('/customers/{customer}/other-payment', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'createOtherPayment'])->name('customers.other-payment.create');
    Route::post('/customers/{customer}/other-payment', [\App\Http\Controllers\Panel\CustomerBillingController::class, 'storeOtherPayment'])->name('customers.other-payment.store');
    Route::get('/customers/{customer}/send-sms', [\App\Http\Controllers\Panel\CustomerCommunicationController::class, 'showSmsForm'])->name('customers.send-sms');
    Route::post('/customers/{customer}/send-sms', [\App\Http\Controllers\Panel\CustomerCommunicationController::class, 'sendSms'])->name('customers.send-sms.send');
    Route::get('/customers/{customer}/send-payment-link', [\App\Http\Controllers\Panel\CustomerCommunicationController::class, 'showPaymentLinkForm'])->name('customers.send-payment-link');
    Route::post('/customers/{customer}/send-payment-link', [\App\Http\Controllers\Panel\CustomerCommunicationController::class, 'sendPaymentLink'])->name('customers.send-payment-link.send');
    Route::get('/customers/{customer}/internet-history', [\App\Http\Controllers\Panel\CustomerHistoryController::class, 'internetHistory'])->name('customers.internet-history');
    Route::post('/customers/{customer}/internet-history/export', [\App\Http\Controllers\Panel\CustomerHistoryController::class, 'exportHistory'])->name('customers.internet-history.export');
    Route::get('/customers/{customer}/change-operator', [\App\Http\Controllers\Panel\CustomerOperatorController::class, 'edit'])->name('customers.change-operator.edit');
    Route::put('/customers/{customer}/change-operator', [\App\Http\Controllers\Panel\CustomerOperatorController::class, 'update'])->name('customers.change-operator.update');
    Route::get('/customers/{customer}/check-usage', [\App\Http\Controllers\Panel\CustomerUsageController::class, 'checkUsage'])->name('customers.check-usage');
    Route::get('/customers/{customer}/suspend-date', [\App\Http\Controllers\Panel\CustomerSuspendDateController::class, 'edit'])->name('customers.suspend-date.edit');
    Route::put('/customers/{customer}/suspend-date', [\App\Http\Controllers\Panel\CustomerSuspendDateController::class, 'update'])->name('customers.suspend-date.update');
    Route::get('/customers/{customer}/hotspot-recharge', [\App\Http\Controllers\Panel\CustomerHotspotRechargeController::class, 'create'])->name('customers.hotspot-recharge.create');
    Route::post('/customers/{customer}/hotspot-recharge', [\App\Http\Controllers\Panel\CustomerHotspotRechargeController::class, 'store'])->name('customers.hotspot-recharge.store');
    Route::get('/customers/{customer}/advance-payment', [\App\Http\Controllers\Panel\AdvancePaymentController::class, 'create'])->name('customers.advance-payment.create');
    Route::post('/customers/{customer}/advance-payment', [\App\Http\Controllers\Panel\AdvancePaymentController::class, 'store'])->name('customers.advance-payment.store');
    Route::get('/customers/{customer}/advance-payment/{advancePayment}', [\App\Http\Controllers\Panel\AdvancePaymentController::class, 'show'])->name('customers.advance-payment.show');
    Route::get('/customers/{customer}/daily-recharge', [\App\Http\Controllers\Panel\DailyRechargeController::class, 'show'])->name('customers.daily-recharge.show');
    Route::post('/customers/{customer}/daily-recharge', [\App\Http\Controllers\Panel\DailyRechargeController::class, 'recharge'])->name('customers.daily-recharge.process');
    Route::post('/customers/daily-recharge/calculate', [\App\Http\Controllers\Panel\DailyRechargeController::class, 'calculateRate'])->name('customers.daily-recharge.calculate');
    Route::get('/customers/{customer}/auto-renewal', [\App\Http\Controllers\Panel\DailyRechargeController::class, 'autoRenewal'])->name('customers.auto-renewal');
    Route::post('/customers/{customer}/auto-renewal', [\App\Http\Controllers\Panel\DailyRechargeController::class, 'updateAutoRenewal'])->name('customers.auto-renewal.update');

    Route::get('/customers-deleted', [ISPController::class, 'deletedCustomers'])->name('customers.deleted');
    Route::get('/customers-online', [ISPController::class, 'onlineCustomers'])->name('customers.online');
    Route::get('/customers-offline', [ISPController::class, 'offlineCustomers'])->name('customers.offline');

    Route::get('/zones', [\App\Http\Controllers\Panel\ZoneController::class, 'index'])->name('zones.index');
    // ... Additional Accounting/Zone routes would continue here
});
