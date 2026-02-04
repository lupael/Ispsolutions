<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Panel\ISPController; 
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
use App\Http\Controllers\Panel\ZoneController;
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
Route::post('/language/switch', [LanguageController::class, 'switch'])->middleware('auth')->name('language.switch');

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
    Route::get('success', [PaymentController::class, 'success'])->name('success');
    Route::get('failure', [PaymentController::class, 'failure'])->name('failure');
    Route::get('cancel', [PaymentController::class, 'cancel'])->name('cancel');
});

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
            'email' => 'Your account does not have a valid role assigned.',
        ]);
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Role-Based Panel Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// ISP Panel
Route::prefix('panel/isp')->name('panel.isp.')->middleware(['auth', 'tenant', 'role:isp'])->group(function () {
    Route::get('/dashboard', [ISPController::class, 'dashboard'])->name('dashboard');
    
    // User/Customer Management
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

    // Customer Management
    Route::get('/customers', [ISPController::class, 'customers'])->name('customers.index');
    Route::get('/customers/create', [ISPController::class, 'customersCreate'])->name('customers.create');
    Route::post('/customers', [ISPController::class, 'customersStore'])->name('customers.store');
    Route::get('/customers-online', [ISPController::class, 'onlineCustomers'])->name('customers.online');
    Route::get('/customers-offline', [ISPController::class, 'offlineCustomers'])->name('customers.offline');
    Route::get('/customers/{id}/edit', [ISPController::class, 'customersEdit'])->name('customers.edit');
    Route::put('/customers/{id}', [ISPController::class, 'customersUpdate'])->name('customers.update');
    Route::get('/customers/{id}', [ISPController::class, 'customersShow'])->name('customers.show');

    // Accounting (FIXED)
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/transactions', [ISPController::class, 'accountTransactions'])->name('transactions');
        Route::get('/customer-payments', [ISPController::class, 'customerPayments'])->name('customer-payments');
        Route::get('/gateway-payments', [ISPController::class, 'gatewayCustomerPayments'])->name('gateway-customer-payments');
        Route::get('/expenses', [ISPController::class, 'expenses'])->name('expenses');
        Route::get('/income-expense-report', [ISPController::class, 'incomeExpenseReport'])->name('income-expense-report');
        Route::get('/statement', [ISPController::class, 'accountStatement'])->name('statement');
        Route::get('/receivable', [ISPController::class, 'receivableReport'])->name('receivable');
        Route::get('/payable', [ISPController::class, 'payableReport'])->name('payable');
    });

    // Zones
    Route::get('/zones', [ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/create', [ZoneController::class, 'create'])->name('zones.create');
    Route::post('/zones', [ZoneController::class, 'store'])->name('zones.store');
    Route::get('/zones/{id}/edit', [ZoneController::class, 'edit'])->name('zones.edit');
    Route::put('/zones/{id}', [ZoneController::class, 'update'])->name('zones.update');
    Route::delete('/zones/{id}', [ZoneController::class, 'destroy'])->name('zones.destroy');

    // Network & Devices
    Route::get('/mikrotik', function () {
        return redirect()->route('panel.isp.network.routers');
    })->name('mikrotik');
    Route::get('/olt', function () {
        return redirect()->route('panel.isp.network.olt');
    })->name('olt');

    // Tickets
    Route::resource('tickets', TicketController::class);
    
    // Settings
    Route::get('/settings', [ISPController::class, 'settings'])->name('settings');
});
