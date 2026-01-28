<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\PackageChangeRequest;
use App\Models\RadReply;
use App\Models\User;
use App\Services\PackageUpgradeService;
use App\Services\PackageHierarchyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CustomerPackageChangeController extends Controller
{
    protected PackageUpgradeService $upgradeService;

    public function __construct(
        PackageUpgradeService $upgradeService
    ) {
        $this->upgradeService = $upgradeService;
    }

    /**
     * Show the form for changing customer package.
     */
    public function edit($id): View
    {
        $customer = User::with('networkUser.package')->findOrFail($id);
        $this->authorize('changePackage', $customer);

        $packages = Package::where('is_active', true)
            ->where('tenant_id', $customer->tenant_id)
            ->orderBy('price')
            ->get();

        $networkUser = NetworkUser::where('user_id', $customer->id)->first();

        // Get upgrade options and recommendations
        $upgradeOptions = $this->upgradeService->getUpgradeOptions($customer);

        return view('panels.admin.customers.change-package', compact(
            'customer',
            'packages',
            'networkUser',
            'upgradeOptions'
        ));
    }

    /**
     * Process the package change.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $customer = User::findOrFail($id);
        $this->authorize('changePackage', $customer);

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'effective_date' => 'required|date',
            'prorate' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $newPackage = Package::findOrFail($request->package_id);

        // Validate package status (active)
        if ($newPackage->status !== 'active') {
            return back()->withErrors(['package_id' => __('Selected package is not active')]);
        }

        // Note: is_active attribute may not exist on all Package models
        // Only check if the attribute is present
        if (isset($newPackage->is_active) && !$newPackage->is_active) {
            return back()->withErrors(['package_id' => __('Selected package is not available')]);
        }

        // Validate upgrade eligibility using the service
        $eligibility = $this->upgradeService->validateUpgradeEligibility($customer, $newPackage);
        if (!$eligibility['eligible']) {
            return back()->withErrors(['package_id' => implode(' ', $eligibility['errors'])]);
        }

        // Show warnings if any (but don't block)
        if (!empty($eligibility['warnings'])) {
            session()->flash('warnings', $eligibility['warnings']);
        }

        DB::beginTransaction();
        try {
            $networkUser = NetworkUser::where('user_id', $customer->id)->firstOrFail();
            $oldPackage = $networkUser->package;

            if ($oldPackage && $oldPackage->id === $newPackage->id) {
                return back()->with('error', 'Customer is already on this package');
            }

            // Calculate prorated amount using the upgrade service
            $proratedAmount = 0;
            if ($request->prorate && $oldPackage) {
                $costDetails = $this->upgradeService->calculateProratedCost($customer, $newPackage);
                $proratedAmount = $costDetails['upgrade_cost'] ?? 0;
            }

            // Create package change request
            $changeRequest = PackageChangeRequest::create([
                'user_id' => $customer->id,
                'old_package_id' => $oldPackage?->id,
                'new_package_id' => $newPackage->id,
                'effective_date' => $request->effective_date,
                'prorated_amount' => $proratedAmount,
                'status' => 'approved',
                'requested_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'reason' => $request->reason,
            ]);

            // Update network user package
            $networkUser->update([
                'package_id' => $newPackage->id,
            ]);

            // Generate invoice if prorated amount > 0
            if ($proratedAmount > 0) {
                $this->generateInvoice($customer, $changeRequest, $proratedAmount);
            }

            // Update RADIUS attributes
            $this->updateRadiusAttributes($networkUser, $newPackage);

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'package_changed',
                'model' => 'User',
                'model_id' => $customer->id,
                'details' => "Package changed from {$oldPackage?->name} to {$newPackage->name}. Prorated amount: {$proratedAmount}",
            ]);

            // Disconnect to apply changes
            if (class_exists(CustomerDisconnectController::class)) {
                try {
                    app(CustomerDisconnectController::class)->disconnect($request, $customer->id);
                } catch (\Exception $e) {
                    Log::warning('Failed to disconnect customer after package change', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', 'Package changed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to change package', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to change package: ' . $e->getMessage());
        }
    }

    /**
     * Calculate prorated amount for package change.
     */
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
            // Handle other billing cycles (daily, yearly, etc.)
            $remainingDays = 0;
            $totalDays = 1;
        }

        // Calculate prorated credit for old package
        $credit = ($oldPackage->price / $totalDays) * $remainingDays;

        // Calculate prorated charge for new package
        $charge = ($newPackage->price / $totalDays) * $remainingDays;

        return max(0, $charge - $credit);
    }

    /**
     * Generate invoice for package change.
     */
    protected function generateInvoice(User $customer, PackageChangeRequest $changeRequest, float $amount): void
    {
        Invoice::create([
            'user_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'description' => "Package change: {$changeRequest->oldPackage?->name} to {$changeRequest->newPackage->name}",
            'subtotal' => $amount,
            'total_amount' => $amount,
            'due_date' => now()->addDays(7),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Update RADIUS attributes for new package.
     */
    protected function updateRadiusAttributes(NetworkUser $networkUser, Package $package): void
    {
        // Update rate limits
        if ($package->bandwidth_download && $package->bandwidth_upload) {
            // Format: upload/download (in Kbps)
            $rateLimit = "{$package->bandwidth_upload}k/{$package->bandwidth_download}k";

            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );
        }

        // Update session timeout if specified
        if ($package->session_timeout) {
            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Session-Timeout'],
                ['op' => ':=', 'value' => (string) $package->session_timeout]
            );
        }

        // Update idle timeout if specified
        if ($package->idle_timeout) {
            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Idle-Timeout'],
                ['op' => ':=', 'value' => (string) $package->idle_timeout]
            );
        }

        // Update FUP attributes if package has FUP
        if ($package->packageFup) {
            $fup = $package->packageFup;
            if ($fup->data_limit && $fup->reduced_speed_download && $fup->reduced_speed_upload) {
                // Store FUP configuration in RADIUS reply
                RadReply::updateOrCreate(
                    ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Total-Limit'],
                    ['op' => ':=', 'value' => (string) $fup->data_limit]
                );
            }
        }
    }

    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
