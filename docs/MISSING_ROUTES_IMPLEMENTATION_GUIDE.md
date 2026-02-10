# Missing Routes and Controller Methods - Implementation Guide

## Overview
This document lists all missing routes, controller methods, and views identified during the comprehensive audit. These need to be implemented to make all href="#" links functional.

---

## 1. Card Distributor Module

### Missing Controller Methods in `CardDistributorController.php`

```php
/**
 * Display the specified card
 */
public function showCard(Card $card): View
{
    $this->authorize('view', $card);
    
    return view('panels.card-distributor.cards.show', [
        'card' => $card
    ]);
}

/**
 * Show sell card form
 */
public function sellCard(Card $card): View
{
    $this->authorize('sell', $card);
    
    if ($card->status !== 'available') {
        return redirect()->back()->with('error', 'This card is not available for sale.');
    }
    
    return view('panels.card-distributor.cards.sell', [
        'card' => $card
    ]);
}

/**
 * Process card sale
 */
public function processSale(Request $request, Card $card): RedirectResponse
{
    $this->authorize('sell', $card);
    
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'amount' => 'required|numeric|min:0',
    ]);
    
    // TODO: Implement card sale logic
    // - Mark card as sold
    // - Create transaction record
    // - Update distributor balance
    // - Send notification to customer
    
    return redirect()->route('panel.card-distributor.sales')
        ->with('success', 'Card sold successfully.');
}

/**
 * Show sale creation form
 */
public function createSale(): View
{
    $this->authorize('create-sales', CardDistributor::class);
    
    // TODO: Load available cards
    $cards = Card::where('status', 'available')->get();
    
    return view('panels.card-distributor.sales.create', [
        'cards' => $cards
    ]);
}

/**
 * Export sales report
 */
public function exportSales(Request $request)
{
    $this->authorize('export-sales', CardDistributor::class);
    
    // TODO: Implement Excel/PDF export
    // - Filter by date range
    // - Include transaction details
    // - Generate downloadable file
    
    return Excel::download(new SalesExport(), 'sales-report.xlsx');
}

/**
 * Show transactions
 */
public function transactions(): View
{
    $this->authorize('view-transactions', CardDistributor::class);
    
    // TODO: Load distributor transactions
    $transactions = Transaction::where('distributor_id', auth()->id())
        ->latest()
        ->paginate(20);
    
    return view('panels.card-distributor.transactions', [
        'transactions' => $transactions
    ]);
}
```

### Missing Routes in `routes/web.php`

Add inside the card-distributor route group:

```php
Route::prefix('panel/card-distributor')->name('panel.card-distributor.')->middleware(['auth', 'tenant', 'role:card-distributor'])->group(function () {
    // Existing routes...
    Route::get('/dashboard', [CardDistributorController::class, 'dashboard'])->name('dashboard');
    Route::get('/cards', [CardDistributorController::class, 'cards'])->name('cards');
    Route::get('/sales', [CardDistributorController::class, 'sales'])->name('sales');
    Route::get('/commissions', [CardDistributorController::class, 'commissions'])->name('commissions');
    Route::get('/balance', [CardDistributorController::class, 'balance'])->name('balance');
    
    // NEW ROUTES TO ADD:
    Route::get('/cards/{card}', [CardDistributorController::class, 'showCard'])->name('cards.show');
    Route::get('/cards/{card}/sell', [CardDistributorController::class, 'sellCard'])->name('cards.sell');
    Route::post('/cards/{card}/process-sale', [CardDistributorController::class, 'processSale'])->name('cards.process-sale');
    Route::get('/sales/create', [CardDistributorController::class, 'createSale'])->name('sales.create');
    Route::get('/sales/export', [CardDistributorController::class, 'exportSales'])->name('sales.export');
    Route::get('/transactions', [CardDistributorController::class, 'transactions'])->name('transactions');
});
```

### Missing Views

1. `resources/views/panels/card-distributor/cards/show.blade.php` - Card detail view
2. `resources/views/panels/card-distributor/cards/sell.blade.php` - Sell card form
3. `resources/views/panels/card-distributor/sales/create.blade.php` - New sale form
4. `resources/views/panels/card-distributor/transactions.blade.php` - Transaction history

---

## 2. Sales Manager Module

### Missing Controller Methods in `SalesManagerController.php`

```php
/**
 * Display the specified lead
 */
public function showLead(Lead $lead): View
{
    $this->authorize('view', $lead);
    
    return view('panels.sales-manager.leads.show', [
        'lead' => $lead
    ]);
}

/**
 * Display the specified bill
 */
public function showBill(Bill $bill): View
{
    $this->authorize('view', $bill);
    
    return view('panels.sales-manager.subscriptions.bills.show', [
        'bill' => $bill
    ]);
}

/**
 * Process bill payment
 */
public function payBill(Request $request, Bill $bill): RedirectResponse
{
    $this->authorize('pay', $bill);
    
    // TODO: Implement bill payment logic
    // - Validate payment method
    // - Process payment through gateway
    // - Update bill status
    // - Send confirmation
    
    return redirect()->route('panel.sales-manager.subscriptions.bills')
        ->with('success', 'Payment processed successfully.');
}
```

### Missing Routes

```php
Route::prefix('panel/sales-manager')->name('panel.sales-manager.')->middleware(['auth', 'tenant', 'role:sales-manager'])->group(function () {
    // Existing routes...
    
    // NEW ROUTES TO ADD:
    Route::get('/leads/{lead}', [SalesManagerController::class, 'showLead'])->name('leads.show');
    Route::get('/subscriptions/bills/{bill}', [SalesManagerController::class, 'showBill'])->name('subscriptions.bills.show');
    Route::post('/subscriptions/bills/{bill}/pay', [SalesManagerController::class, 'payBill'])->name('subscriptions.bills.pay');
});
```

---

## 3. Operator Module

### Missing Controller Methods in `OperatorController.php`

```php
/**
 * Display the specified bill
 */
public function showBill(Bill $bill): View
{
    $this->authorize('view', $bill);
    
    return view('panels.operator.bills.show', [
        'bill' => $bill
    ]);
}

/**
 * Display the specified customer
 */
public function showCustomer(Customer $customer): View
{
    $this->authorize('view', $customer);
    
    return view('panels.operator.customers.show', [
        'customer' => $customer
    ]);
}

/**
 * Display the specified complaint
 */
public function showComplaint(Complaint $complaint): View
{
    $this->authorize('view', $complaint);
    
    return view('panels.operator.complaints.show', [
        'complaint' => $complaint
    ]);
}

/**
 * Display SMS payment details
 */
public function showSmsPayment(SmsPayment $payment): View
{
    $this->authorize('view', $payment);
    
    return view('panels.operator.sms-payments.show', [
        'payment' => $payment
    ]);
}
```

### Missing Routes

```php
Route::prefix('panel/operator')->name('panel.operator.')->middleware(['auth', 'tenant', 'role:operator'])->group(function () {
    // Existing routes...
    
    // NEW ROUTES TO ADD:
    Route::get('/bills/{bill}', [OperatorController::class, 'showBill'])->name('bills.show');
    Route::get('/customers/{customer}', [OperatorController::class, 'showCustomer'])->name('customers.show');
    Route::get('/complaints/{complaint}', [OperatorController::class, 'showComplaint'])->name('complaints.show');
    Route::get('/sms-payments/{payment}', [OperatorController::class, 'showSmsPayment'])->name('sms-payments.show');
});
```

---

## 4. Manager Module

### Missing Controller Methods in `ManagerController.php`

```php
/**
 * Display session details
 */
public function showSession(Session $session): View
{
    $this->authorize('view', $session);
    
    return view('panels.manager.sessions.show', [
        'session' => $session
    ]);
}

/**
 * Disconnect user session
 */
public function disconnectSession(Session $session): RedirectResponse
{
    $this->authorize('disconnect', $session);
    
    // TODO: Call RADIUS/MikroTik API to disconnect session
    
    return redirect()->route('panel.manager.sessions')
        ->with('success', 'Session disconnected successfully.');
}
```

### Missing Routes

```php
Route::prefix('panel/manager')->name('panel.manager.')->middleware(['auth', 'tenant', 'role:manager'])->group(function () {
    // Existing routes...
    
    // NEW ROUTES TO ADD:
    Route::get('/sessions/{session}', [ManagerController::class, 'showSession'])->name('sessions.show');
    Route::post('/sessions/{session}/disconnect', [ManagerController::class, 'disconnectSession'])->name('sessions.disconnect');
});
```

---

## 5. Admin Module

### Missing Controller Methods in `AdminController.php`

```php
/**
 * Restore soft-deleted customer
 */
public function restoreCustomer(int $id): RedirectResponse
{
    $customer = Customer::onlyTrashed()->findOrFail($id);
    
    $this->authorize('restore', $customer);
    
    $customer->restore();
    
    return redirect()->route('panel.admin.customers.deleted')
        ->with('success', 'Customer restored successfully.');
}

/**
 * Permanently delete customer
 */
public function forceDeleteCustomer(int $id): RedirectResponse
{
    $customer = Customer::onlyTrashed()->findOrFail($id);
    
    $this->authorize('forceDelete', $customer);
    
    $customer->forceDelete();
    
    return redirect()->route('panel.admin.customers.deleted')
        ->with('success', 'Customer permanently deleted.');
}
```

### Missing Routes

```php
Route::prefix('panel/admin')->name('panel.admin.')->middleware(['auth', 'tenant', 'role:admin'])->group(function () {
    // Existing routes...
    
    // NEW ROUTES TO ADD:
    Route::post('/customers/{customer}/restore', [AdminController::class, 'restoreCustomer'])->name('customers.restore');
    Route::delete('/customers/{customer}/force-delete', [AdminController::class, 'forceDeleteCustomer'])->name('customers.force-delete');
});
```

---

## 6. Sub-Operator Module

Similar structure to Operator module - needs showCustomer, showBill methods and corresponding routes.

---

## 7. Staff Module

Needs showNetworkUser, createSupportTicket methods and routes.

---

## 8. Super Admin Module

Needs role management routes (create, edit, delete).

---

## 9. Developer Module

Needs API documentation routes if not using external docs.

---

## 10. Shared Modules

### Policy Pages (All Users)

Create a new controller `PageController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }
    
    public function termsOfService(): View
    {
        return view('pages.terms-of-service');
    }
}
```

Add routes:

```php
// Public pages
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
```

Create views:
1. `resources/views/pages/privacy-policy.blade.php`
2. `resources/views/pages/terms-of-service.blade.php`

---

## Implementation Priority

### High Priority (User-facing features)
1. Customer detail views (all roles)
2. Bill detail views (all roles)
3. Session management (Manager)
4. Complaint management (Operator/Admin)

### Medium Priority (Admin features)
1. Card sales management (Card Distributor)
2. Lead management (Sales Manager)
3. Report exports (all roles)
4. Customer restore/delete (Admin)

### Low Priority (Enhancement)
1. Policy pages (all users)
2. API documentation (Developer)
3. Advanced analytics

---

## Estimation

- **Routes:** ~40 new routes
- **Controller Methods:** ~35 new methods
- **Views:** ~25 new view files
- **Policies:** ~10 policy methods
- **Estimated Time:** 60-80 hours
