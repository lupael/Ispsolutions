<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicePackage;
use App\Services\HotspotService;
use App\Services\BillingService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerHotspotRechargeController extends Controller
{
    public function __construct(
        private HotspotService $hotspotService,
        private BillingService $billingService,
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show hotspot recharge form
     */
    public function create(User $customer)
    {
        $this->authorize('hotspotRecharge', $customer);

        // Get hotspot packages
        $packages = ServicePackage::where('status', 'active')
            ->where('connection_type', 'hotspot')
            ->orderBy('name')
            ->get();

        return view('panels.admin.customers.hotspot.recharge', compact('customer', 'packages'));
    }

    /**
     * Process hotspot recharge
     */
    public function store(Request $request, User $customer)
    {
        $this->authorize('hotspotRecharge', $customer);

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|string|in:cash,bank_transfer,online,card',
            'transaction_reference' => 'nullable|string|max:255',
            'validity_days' => 'nullable|integer|min:1',
            'data_limit_mb' => 'nullable|integer|min:1',
            'time_limit_hours' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $package = ServicePackage::findOrFail($validated['package_id']);

            // Calculate expiry
            $validityDays = $validated['validity_days'] ?? $package->validity_days ?? 30;
            $expiryDate = now()->addDays($validityDays);

            // Update customer
            $customer->update([
                'service_package_id' => $package->id,
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);

            // Update RADIUS limits if provided
            if (isset($validated['data_limit_mb'])) {
                $this->hotspotService->updateDataLimit($customer, $validated['data_limit_mb'] * 1048576);
            }

            if (isset($validated['time_limit_hours'])) {
                $this->hotspotService->updateTimeLimit($customer, $validated['time_limit_hours'] * 3600);
            }

            // Create payment record
            $payment = \App\Models\Payment::create([
                'tenant_id' => $customer->tenant_id,
                'payment_number' => $this->billingService->generatePaymentNumber(),
                'user_id' => $customer->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_reference'],
                'status' => 'completed',
                'payment_type' => 'hotspot_recharge',
                'notes' => "Hotspot recharge - {$package->name}",
                'paid_at' => now(),
                'collected_by' => auth()->id(),
            ]);

            $this->auditLogService->log(
                'hotspot_recharged',
                'Hotspot customer recharged',
                [
                    'customer_id' => $customer->id,
                    'package_id' => $package->id,
                    'validity_days' => $validityDays,
                    'payment_id' => $payment->id
                ]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', "Hotspot recharged successfully. Valid until: {$expiryDate->format('Y-m-d')}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to recharge hotspot: ' . $e->getMessage());
        }
    }
}
