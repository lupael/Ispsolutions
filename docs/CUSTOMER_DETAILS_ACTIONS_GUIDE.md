# Customer Details Page — Actions (Developer Documentation)

This document describes **all actions available on the customer details page** in the ISP Solution, what each action does, where it is implemented (routes/controllers), and includes code examples for implementation.

## Table of Contents
1. [Overview](#overview)
2. [How Actions Are Triggered](#how-actions-are-triggered)
3. [Customer Status Management](#customer-status-management)
4. [Package & Billing Management](#package--billing-management)
5. [Network & Speed Management](#network--speed-management)
6. [Communication & Support](#communication--support)
7. [Implementation Roadmap](#implementation-roadmap)

## Overview

The customer details page (`/panel/admin/customers/{id}`) provides operators with various actions to manage customer accounts, billing, network settings, and support. All actions are protected by Laravel policies based on user roles and permissions.

**Primary Files:**
- UI: `resources/views/panels/admin/customers/show.blade.php`
- Component: `resources/views/components/tabbed-customer-details.blade.php`
- Controller: `app/Http/Controllers/Panel/AdminController.php`
- Policy: `app/Policies/CustomerPolicy.php`
- Routes: `routes/web.php`

## How Actions Are Triggered

Actions on the customer details page can be triggered in two ways:

### 1. Direct Links (GET requests)
Used for actions that open forms or display information:
```blade
<a href="{{ route('panel.admin.customers.edit', $customer->id) }}" 
   class="btn btn-primary">
    Edit Customer
</a>
```

### 2. AJAX Actions (POST/PUT/DELETE requests)
Used for quick state changes without page reload:
```blade
<button onclick="executeAction('{{ route('panel.admin.customers.activate', $customer->id) }}', 'POST')"
        class="btn btn-success">
    Activate
</button>
```

### 3. Authorization Guards
All actions are protected using Laravel's `@can` directive:
```blade
@can('activate', $customer)
    <button>Activate</button>
@endcan
```

---

## Customer Status Management

### 1. Activate Customer

**Purpose:** Change customer status to 'active' and enable network access  
**Guard:** `@can('activate', $customer)`  
**Route:** `panel.admin.customers.activate` (POST)  
**Controller:** `AdminController@customersActivate`

#### What It Does:
1. Updates `users.status = 'active'`
2. Updates `users.is_active = true`
3. For PPPoE customers:
   - Updates RADIUS password (radcheck)
   - Updates IP address configuration (radreply)
   - Applies rate limits
   - Disconnects active session to force re-authentication
4. For Hotspot customers:
   - Updates hotspot_users table
   - Clears any restrictions
5. Logs the action in audit logs

#### Implementation Example:
```php
// app/Http/Controllers/Panel/AdminController.php
public function customersActivate(Request $request, $id): JsonResponse
{
    $customer = User::findOrFail($id);
    $this->authorize('activate', $customer);

    DB::beginTransaction();
    try {
        // Update user status
        $customer->status = 'active';
        $customer->is_active = true;
        $customer->save();

        // Update network access based on service type
        $networkUser = $customer->networkUser;
        if ($networkUser) {
            $networkUser->status = 'active';
            $networkUser->save();

            switch ($networkUser->service_type) {
                case 'pppoe':
                    $this->activatePppoeUser($networkUser);
                    break;
                case 'hotspot':
                    $this->activateHotspotUser($networkUser);
                    break;
            }
        }

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'customer_activated',
            'model' => 'User',
            'model_id' => $customer->id,
            'details' => "Customer {$customer->username} activated",
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Customer activated successfully',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to activate customer: ' . $e->getMessage(),
        ], 500);
    }
}

protected function activatePppoeUser(NetworkUser $networkUser): void
{
    // Update RADIUS password
    RadCheck::updateOrCreate(
        ['username' => $networkUser->username, 'attribute' => 'Cleartext-Password'],
        ['op' => ':=', 'value' => $networkUser->password]
    );

    // Update IP configuration if static IP assigned
    if ($networkUser->ip_address) {
        RadReply::updateOrCreate(
            ['username' => $networkUser->username, 'attribute' => 'Framed-IP-Address'],
            ['op' => ':=', 'value' => $networkUser->ip_address]
        );
    }

    // Apply rate limits from package
    if ($package = $networkUser->package) {
        $this->updateRateLimits($networkUser, $package);
    }

    // Disconnect active session to force re-authentication
    $this->disconnectPppoeUser($networkUser);
}
```

#### UI Implementation:
```blade
@can('activate', $customer)
    @if($customer->status !== 'active')
        <button id="activateBtn" 
                data-customer-id="{{ $customer->id }}"
                class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Activate
        </button>
    @endif
@endcan
```

#### JavaScript Handler:
```javascript
document.getElementById('activateBtn')?.addEventListener('click', async function() {
    if (!confirm('Are you sure you want to activate this customer?')) {
        return;
    }

    const customerId = this.dataset.customerId;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⏳</span> Activating...';

    try {
        const response = await fetch(`/panel/admin/customers/${customerId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Success', data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('Error', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Activate';
        }
    } catch (error) {
        showNotification('Error', 'Failed to activate customer', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Activate';
    }
});
```

---

### 2. Suspend Customer

**Purpose:** Temporarily disable customer's network access  
**Guard:** `@can('suspend', $customer)`  
**Route:** `panel.admin.customers.suspend` (POST)  
**Controller:** `AdminController@customersSuspend`

#### What It Does:
1. Updates `users.status = 'suspended'`
2. Records suspension reason
3. For PPPoE customers:
   - Removes/disables RADIUS credentials
   - Disconnects active sessions
   - Can optionally assign to suspended pool with limited access
4. For Hotspot customers:
   - Disables hotspot account
5. Optionally sends notification to customer
6. Logs the action

#### Implementation Example:
```php
public function customersSuspend(Request $request, $id): JsonResponse
{
    $customer = User::findOrFail($id);
    $this->authorize('suspend', $customer);

    $request->validate([
        'reason' => 'required|string|max:255',
        'send_notification' => 'boolean',
    ]);

    DB::beginTransaction();
    try {
        // Update user status
        $customer->status = 'suspended';
        $customer->suspend_reason = $request->reason;
        $customer->suspended_at = now();
        $customer->suspended_by = auth()->id();
        $customer->save();

        // Update network access
        $networkUser = $customer->networkUser;
        if ($networkUser) {
            $networkUser->status = 'suspended';
            $networkUser->save();

            switch ($networkUser->service_type) {
                case 'pppoe':
                    $this->suspendPppoeUser($networkUser, $request->reason);
                    break;
                case 'hotspot':
                    $this->suspendHotspotUser($networkUser);
                    break;
            }
        }

        // Send notification if requested
        if ($request->send_notification) {
            $this->sendSuspensionNotification($customer, $request->reason);
        }

        // Log action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'customer_suspended',
            'model' => 'User',
            'model_id' => $customer->id,
            'details' => "Customer {$customer->username} suspended. Reason: {$request->reason}",
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Customer suspended successfully',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to suspend customer: ' . $e->getMessage(),
        ], 500);
    }
}

protected function suspendPppoeUser(NetworkUser $networkUser, string $reason): void
{
    // Remove or disable RADIUS password
    RadCheck::where('username', $networkUser->username)
        ->where('attribute', 'Cleartext-Password')
        ->delete();

    // Optionally assign to suspended pool with limited access
    // This allows customer to see payment page
    $suspendedPool = config('radius.suspended_pool_name');
    if ($suspendedPool) {
        RadReply::updateOrCreate(
            ['username' => $networkUser->username, 'attribute' => 'Pool-Name'],
            ['op' => ':=', 'value' => $suspendedPool]
        );
    }

    // Disconnect active session
    $this->disconnectPppoeUser($networkUser);
}
```

#### UI Implementation:
```blade
@can('suspend', $customer)
    @if($customer->status === 'active')
        <button onclick="showSuspendModal({{ $customer->id }})"
                class="inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Suspend
        </button>
    @endif
@endcan

<!-- Suspend Modal -->
<div id="suspendModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Suspend Customer</h3>
        <form id="suspendForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <select name="reason" required class="w-full rounded-md border-gray-300">
                    <option value="">Select reason...</option>
                    <option value="non_payment">Non-payment</option>
                    <option value="violation">Terms violation</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="customer_request">Customer request</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="send_notification" class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700">Send notification to customer</span>
                </label>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md">Suspend</button>
            </div>
        </form>
    </div>
</div>
```

---

### 3. Disconnect Customer

**Purpose:** Forcefully disconnect customer's active session  
**Guard:** `@can('disconnect', $customer)`  
**Route:** `panel.admin.customers.disconnect` (POST)  
**Controller:** To be created: `CustomerDisconnectController`

#### What It Does:
1. Finds active sessions from RADIUS/router
2. Sends disconnect request to NAS (Network Access Server)
3. For PPPoE: Uses MikroTik API to remove active PPP session
4. For Hotspot: Removes active hotspot session
5. Can use RADIUS CoA (Change of Authorization) if supported
6. Logs the disconnection

#### Implementation Example:
```php
// app/Http/Controllers/Panel/CustomerDisconnectController.php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NetworkUser;
use App\Models\RadAcct;
use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerDisconnectController extends Controller
{
    protected MikrotikService $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function disconnect(Request $request, $id): JsonResponse
    {
        $customer = User::findOrFail($id);
        $this->authorize('disconnect', $customer);

        try {
            $networkUser = $customer->networkUser;
            if (!$networkUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No network user found',
                ], 404);
            }

            $disconnected = false;
            $sessions = [];

            switch ($networkUser->service_type) {
                case 'pppoe':
                    $disconnected = $this->disconnectPppoe($networkUser);
                    break;
                case 'hotspot':
                    $disconnected = $this->disconnectHotspot($networkUser);
                    break;
            }

            if ($disconnected) {
                // Log action
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'customer_disconnected',
                    'model' => 'User',
                    'model_id' => $customer->id,
                    'details' => "Customer {$customer->username} disconnected from network",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Customer disconnected successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No active sessions found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function disconnectPppoe(NetworkUser $networkUser): bool
    {
        // Find active sessions
        $activeSessions = RadAcct::where('username', $networkUser->username)
            ->whereNull('acctstoptime')
            ->get();

        if ($activeSessions->isEmpty()) {
            return false;
        }

        $disconnected = false;
        foreach ($activeSessions as $session) {
            // Try to disconnect via MikroTik API
            if ($session->nasipaddress) {
                try {
                    $router = \App\Models\MikrotikRouter::where('ip_address', $session->nasipaddress)->first();
                    if ($router) {
                        $this->mikrotikService->connect(
                            $router->ip_address,
                            $router->username,
                            $router->password,
                            $router->port ?? 8728
                        );

                        // Find and remove PPP session
                        $pppSessions = $this->mikrotikService->query('/ppp/active/print', [
                            '?name' => $networkUser->username
                        ]);

                        foreach ($pppSessions as $pppSession) {
                            $this->mikrotikService->query('/ppp/active/remove', [
                                '.id' => $pppSession['.id']
                            ]);
                            $disconnected = true;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to disconnect PPPoE user via API: " . $e->getMessage());
                }
            }

            // Alternative: Use RADIUS CoA
            // $this->sendRadiusDisconnect($session);
        }

        return $disconnected;
    }

    protected function disconnectHotspot(NetworkUser $networkUser): bool
    {
        // Find active hotspot sessions
        $activeSessions = RadAcct::where('username', $networkUser->username)
            ->whereNull('acctstoptime')
            ->get();

        if ($activeSessions->isEmpty()) {
            return false;
        }

        $disconnected = false;
        foreach ($activeSessions as $session) {
            if ($session->nasipaddress) {
                try {
                    $router = \App\Models\MikrotikRouter::where('ip_address', $session->nasipaddress)->first();
                    if ($router) {
                        $this->mikrotikService->connect(
                            $router->ip_address,
                            $router->username,
                            $router->password,
                            $router->port ?? 8728
                        );

                        // Remove hotspot active session
                        $hotspotSessions = $this->mikrotikService->query('/ip/hotspot/active/print', [
                            '?user' => $networkUser->username
                        ]);

                        foreach ($hotspotSessions as $hotspotSession) {
                            $this->mikrotikService->query('/ip/hotspot/active/remove', [
                                '.id' => $hotspotSession['.id']
                            ]);
                            $disconnected = true;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to disconnect hotspot user: " . $e->getMessage());
                }
            }
        }

        return $disconnected;
    }
}
```

---

## Package & Billing Management

### 4. Change Package

**Purpose:** Upgrade/downgrade customer's service package  
**Guard:** `@can('changePackage', $customer)`  
**Route:** `panel.admin.customers.change-package` (GET/POST)  
**Controller:** To be created: `CustomerPackageChangeController`

#### What It Does:
1. Shows form with available packages
2. Calculates prorated charges/credits
3. Updates customer's package
4. Generates invoice for difference
5. Updates RADIUS attributes (speed limits, etc.)
6. Disconnects session to apply changes
7. Records package change history

#### Implementation:
```php
// app/Http/Controllers/Panel/CustomerPackageChangeController.php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\PackageChangeRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPackageChangeController extends Controller
{
    public function edit($id): View
    {
        $customer = User::with('networkUser.package')->findOrFail($id);
        $this->authorize('changePackage', $customer);

        $packages = Package::where('is_active', true)
            ->where('tenant_id', $customer->tenant_id)
            ->orderBy('price')
            ->get();

        return view('panels.admin.customers.change-package', compact('customer', 'packages'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);
        $this->authorize('changePackage', $customer);

        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'effective_date' => 'required|date',
            'prorate' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $newPackage = Package::findOrFail($request->package_id);
            $oldPackage = $customer->networkUser->package;

            // Calculate prorated amount
            $proratedAmount = 0;
            if ($request->prorate) {
                $proratedAmount = $this->calculateProration($customer, $oldPackage, $newPackage);
            }

            // Create package change request
            $changeRequest = PackageChangeRequest::create([
                'user_id' => $customer->id,
                'old_package_id' => $oldPackage->id,
                'new_package_id' => $newPackage->id,
                'effective_date' => $request->effective_date,
                'prorated_amount' => $proratedAmount,
                'status' => 'approved',
                'requested_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update network user package
            $customer->networkUser->update([
                'package_id' => $newPackage->id,
            ]);

            // Generate invoice if prorated amount > 0
            if ($proratedAmount > 0) {
                $this->generateInvoice($customer, $changeRequest, $proratedAmount);
            }

            // Update RADIUS attributes
            $this->updateRadiusAttributes($customer->networkUser, $newPackage);

            // Disconnect to apply changes
            app(CustomerDisconnectController::class)->disconnect(request(), $customer->id);

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', 'Package changed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to change package: ' . $e->getMessage());
        }
    }

    protected function calculateProration(User $customer, Package $oldPackage, Package $newPackage): float
    {
        // Calculate remaining days in current billing period
        $now = now();
        $billingCycle = $customer->billing_cycle ?? 'monthly';
        
        if ($billingCycle === 'monthly') {
            $endOfPeriod = $now->copy()->endOfMonth();
            $remainingDays = $now->diffInDays($endOfPeriod);
            $totalDays = $now->daysInMonth;
        } else {
            // Handle other billing cycles
            $remainingDays = 0;
            $totalDays = 1;
        }

        // Calculate prorated credit for old package
        $credit = ($oldPackage->price / $totalDays) * $remainingDays;

        // Calculate prorated charge for new package
        $charge = ($newPackage->price / $totalDays) * $remainingDays;

        return max(0, $charge - $credit);
    }

    protected function updateRadiusAttributes(NetworkUser $networkUser, Package $package): void
    {
        // Update rate limits
        if ($package->bandwidth_download && $package->bandwidth_upload) {
            $rateLimit = "{$package->bandwidth_upload}k/{$package->bandwidth_download}k";
            
            \App\Models\RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );
        }

        // Update other package-specific attributes
        // (FUP, time limits, volume limits, etc.)
    }
}
```

---

### 5. Generate Bill

**Purpose:** Create invoice for customer  
**Guard:** `@can('generateBill', $customer)`  
**Route:** `panel.admin.customers.bills.create` (GET/POST)

---

### 6. Edit Billing Profile

**Purpose:** Change billing date, cycle, or payment method  
**Guard:** `@can('editBillingProfile', $customer)`

---

## Network & Speed Management

### 7. Edit Speed Limit

**Purpose:** Temporarily adjust customer's bandwidth limits  
**Guard:** `@can('editSpeedLimit', $customer)`  
**Route:** `panel.admin.customers.speed-limit` (GET/PUT)  
**Controller:** `CustomerSpeedLimitController` (existing, needs enhancement)

---

### 8. Edit Time Limit

**Purpose:** Set session time restrictions  
**Guard:** `@can('editSpeedLimit', $customer)`  
**Controller:** `CustomerTimeLimitController` (existing)

---

### 9. Edit Volume Limit

**Purpose:** Set data usage caps  
**Guard:** `@can('editSpeedLimit', $customer)`  
**Controller:** `CustomerVolumeLimitController` (existing)

---

### 10. Activate FUP (Fair Usage Policy)

**Purpose:** Enable reduced speed after quota exceeded  
**Guard:** `@can('activateFup', $customer)`  
**Route:** `panel.admin.customers.activate-fup` (POST)  
**Controller:** To be created: `ActivateFupController`

#### What It Does:
1. Checks current data usage
2. Compares with FUP threshold from package
3. If exceeded, applies reduced speed
4. Updates RADIUS rate limit attribute
5. Disconnects to apply new limit
6. Logs FUP activation

---

### 11. Remove MAC Bind

**Purpose:** Remove MAC address restriction  
**Guard:** `@can('removeMacBind', $customer)`  
**Route:** `panel.admin.customers.mac-bind` (DELETE)  
**Controller:** `CustomerMacBindController` (existing)

---

## Communication & Support

### 12. Send SMS

**Purpose:** Send SMS notification to customer  
**Guard:** `@can('sendSms', $customer)`

---

### 13. Send Payment Link

**Purpose:** Send online payment link via SMS/email  
**Guard:** `@can('sendLink', $customer)`

---

### 14. Add Complaint

**Purpose:** Create support ticket  
**Route:** `panel.tickets.create`

---

## Additional Actions

### 15. Advance Payment

**Purpose:** Record advance payment from customer  
**Guard:** `@can('advancePayment', $customer)`

---

### 16. Other Payment

**Purpose:** Record non-package payments (installation, etc.)

---

### 17. Internet History

**Purpose:** Export session/usage history

---

### 18. Change Operator

**Purpose:** Transfer customer to different operator  
**Guard:** `@can('changeOperator', $customer)`

---

## Implementation Roadmap

### Priority 1: Core Status Management (Completed)
- [x] Activate Customer (existing)
- [x] Suspend Customer (existing)
- [ ] Disconnect Customer

### Priority 2: Package & Billing
- [ ] Change Package
- [ ] Generate Bill
- [ ] Edit Billing Profile
- [ ] Advance Payment
- [ ] Other Payment

### Priority 3: Network Management
- [ ] Edit Speed Limit (enhance existing)
- [ ] Edit Time Limit (enhance existing)
- [ ] Edit Volume Limit (enhance existing)
- [ ] Activate FUP
- [ ] Remove MAC Bind (enhance existing)

### Priority 4: Communication
- [ ] Send SMS
- [ ] Send Payment Link

### Priority 5: Additional Features
- [ ] Add Complaint
- [ ] Internet History
- [ ] Change Operator

---

## Policy Updates Required

Add these methods to `app/Policies/CustomerPolicy.php`:

```php
/**
 * Determine if the user can disconnect the customer.
 */
public function disconnect(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('disconnect_customers');
}

/**
 * Determine if the user can change customer's package.
 */
public function changePackage(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('change_package');
}

/**
 * Determine if the user can edit speed limits.
 */
public function editSpeedLimit(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('edit_speed_limit');
}

/**
 * Determine if the user can activate FUP.
 */
public function activateFup(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('activate_fup');
}

/**
 * Determine if the user can remove MAC binding.
 */
public function removeMacBind(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('remove_mac_bind');
}

/**
 * Determine if the user can generate bills.
 */
public function generateBill(User $user, User $customer): bool
{
    return $user->hasPermission('generate_bills') && $this->view($user, $customer);
}

/**
 * Determine if the user can edit billing profile.
 */
public function editBillingProfile(User $user, User $customer): bool
{
    return $this->update($user, $customer) && $user->hasPermission('edit_billing_profile');
}

/**
 * Determine if the user can send SMS.
 */
public function sendSms(User $user, User $customer): bool
{
    return $user->hasPermission('send_sms') && $this->view($user, $customer);
}

/**
 * Determine if the user can send payment link.
 */
public function sendLink(User $user, User $customer): bool
{
    return $user->hasPermission('send_payment_link') && $this->view($user, $customer);
}

/**
 * Determine if the user can record advance payment.
 */
public function advancePayment(User $user, User $customer): bool
{
    return $user->hasPermission('record_payments') && $this->view($user, $customer);
}

/**
 * Determine if the user can change operator.
 */
public function changeOperator(User $user, User $customer): bool
{
    // Only high-level operators can transfer customers
    return $user->operator_level <= 20 && $user->hasPermission('change_operator');
}
```

---

## Routes to Add

Add these routes to `routes/web.php` in the admin panel section:

```php
// Customer Actions
Route::prefix('customers/{customer}')->name('customers.')->group(function () {
    Route::post('disconnect', [CustomerDisconnectController::class, 'disconnect'])->name('disconnect');
    Route::get('change-package', [CustomerPackageChangeController::class, 'edit'])->name('change-package.edit');
    Route::put('change-package', [CustomerPackageChangeController::class, 'update'])->name('change-package.update');
    Route::post('activate-fup', [ActivateFupController::class, 'activate'])->name('activate-fup');
    
    // Billing actions
    Route::get('bills/create', [CustomerBillingController::class, 'create'])->name('bills.create');
    Route::post('bills', [CustomerBillingController::class, 'store'])->name('bills.store');
    Route::get('billing-profile/edit', [CustomerBillingProfileController::class, 'edit'])->name('billing-profile.edit');
    Route::put('billing-profile', [CustomerBillingProfileController::class, 'update'])->name('billing-profile.update');
    
    // Payment actions
    Route::get('advance-payment', [CustomerAdvancePaymentController::class, 'create'])->name('advance-payment.create');
    Route::post('advance-payment', [CustomerAdvancePaymentController::class, 'store'])->name('advance-payment.store');
    Route::get('other-payment', [CustomerOtherPaymentController::class, 'create'])->name('other-payment.create');
    Route::post('other-payment', [CustomerOtherPaymentController::class, 'store'])->name('other-payment.store');
    
    // Communication actions
    Route::get('send-sms', [CustomerSmsController::class, 'create'])->name('send-sms.create');
    Route::post('send-sms', [CustomerSmsController::class, 'store'])->name('send-sms.store');
    Route::get('send-payment-link', [CustomerPaymentLinkController::class, 'create'])->name('send-payment-link.create');
    Route::post('send-payment-link', [CustomerPaymentLinkController::class, 'store'])->name('send-payment-link.store');
    
    // History and reports
    Route::get('internet-history', [CustomerInternetHistoryController::class, 'export'])->name('internet-history.export');
    
    // Operator change
    Route::get('change-operator', [CustomerOperatorChangeController::class, 'create'])->name('change-operator.create');
    Route::post('change-operator', [CustomerOperatorChangeController::class, 'store'])->name('change-operator.store');
});
```

---

## UI Enhancement Example

Enhanced customer details page with all actions:

```blade
<!-- resources/views/panels/admin/customers/show.blade.php -->
<div class="flex flex-wrap gap-2">
    <!-- Edit -->
    <a href="{{ route('panel.admin.customers.edit', $customer->id) }}" 
       class="btn btn-primary">
        Edit
    </a>

    <!-- Status Actions -->
    @can('activate', $customer)
        @if($customer->status !== 'active')
            <button onclick="activateCustomer({{ $customer->id }})" class="btn btn-success">
                Activate
            </button>
        @endif
    @endcan

    @can('suspend', $customer)
        @if($customer->status === 'active')
            <button onclick="showSuspendModal({{ $customer->id }})" class="btn btn-warning">
                Suspend
            </button>
        @endif
    @endcan

    @can('disconnect', $customer)
        <button onclick="disconnectCustomer({{ $customer->id }})" class="btn btn-danger">
            Disconnect
        </button>
    @endcan

    <!-- Package Actions -->
    @can('changePackage', $customer)
        <a href="{{ route('panel.admin.customers.change-package.edit', $customer->id) }}" 
           class="btn btn-info">
            Change Package
        </a>
    @endcan

    <!-- Speed Management -->
    @can('editSpeedLimit', $customer)
        <a href="{{ route('panel.admin.customers.speed-limit.edit', $customer->id) }}" 
           class="btn btn-secondary">
            Edit Speed
        </a>
    @endcan

    @can('activateFup', $customer)
        <button onclick="activateFup({{ $customer->id }})" class="btn btn-warning">
            Activate FUP
        </button>
    @endcan

    <!-- Billing Actions -->
    @can('generateBill', $customer)
        <a href="{{ route('panel.admin.customers.bills.create', $customer->id) }}" 
           class="btn btn-success">
            Generate Bill
        </a>
    @endcan

    @can('advancePayment', $customer)
        <a href="{{ route('panel.admin.customers.advance-payment.create', $customer->id) }}" 
           class="btn btn-success">
            Advance Payment
        </a>
    @endcan

    <!-- Communication Actions -->
    @can('sendSms', $customer)
        <a href="{{ route('panel.admin.customers.send-sms.create', $customer->id) }}" 
           class="btn btn-primary">
            Send SMS
        </a>
    @endcan

    @can('sendLink', $customer)
        <a href="{{ route('panel.admin.customers.send-payment-link.create', $customer->id) }}" 
           class="btn btn-primary">
            Send Payment Link
        </a>
    @endcan
</div>
```

---

## Notes

1. **Authorization**: All actions must be protected by both policy gates and appropriate permission checks.

2. **RADIUS Integration**: PPPoE actions require proper RADIUS server configuration and attribute mapping.

3. **MikroTik Integration**: Network control actions (disconnect, speed limit) require MikroTik API access.

4. **Transaction Safety**: Use database transactions for actions that modify multiple tables.

5. **Audit Logging**: All actions should be logged in the audit_logs table.

6. **Notifications**: Consider sending notifications to customers for important actions (suspend, package change, etc.).

7. **UI/UX**: Actions should provide immediate feedback and confirmation dialogs for destructive operations.

8. **Testing**: Each action should have comprehensive tests covering authorization, validation, and business logic.

---

## Reference

This implementation is inspired by the IspBills system but adapted to match the architecture and patterns used in the ISP Solution application.

For questions or suggestions, please refer to the project documentation or create an issue in the repository.
