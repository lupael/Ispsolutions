<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModalController extends Controller
{
    public function __construct(
        private BillingService $billingService
    ) {
    }

    /**
     * Show Fair Usage Policy modal content
     */
    public function showFup(Package $package): View
    {
        $this->authorize('view', $package);

        return view('panels.modals.fup', compact('package'));
    }

    /**
     * Show billing profile modal content
     */
    public function showBillingProfile(int $profileId): View
    {
        // For now, return a placeholder
        // TODO: Implement when billing profiles are added
        return view('panels.modals.billing-profile', ['profileId' => $profileId]);
    }

    /**
     * Show quick action modal for customer
     */
    public function showQuickAction(User $customer, string $action): View
    {
        // Check if user can access this customer (tenant scoping)
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to customer');
        }

        // Validate action
        $allowedActions = ['activate', 'suspend', 'recharge'];
        if (!in_array($action, $allowedActions)) {
            abort(404);
        }

        return view('panels.modals.quick-action', [
            'customer' => $customer,
            'action' => $action
        ]);
    }

    /**
     * Execute quick action
     */
    public function executeQuickAction(Request $request, User $customer, string $action)
    {
        // Check if user can access this customer (tenant scoping)
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to customer'
            ], 403);
        }

        switch ($action) {
            case 'activate':
                $customer->update([
                    'status' => 'active',
                    'is_active' => true
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Customer activated successfully'
                ]);

            case 'suspend':
                $customer->update([
                    'status' => 'suspended',
                    'is_active' => false
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Customer suspended successfully'
                ]);

            case 'recharge':
                // Validate recharge data
                $validated = $request->validate([
                    'amount' => 'required|numeric|min:0',
                    'payment_method' => 'nullable|string',
                    'notes' => 'nullable|string|max:500',
                ]);

                try {
                    // Create a payment transaction for the recharge
                    $payment = \App\Models\Payment::create([
                        'tenant_id' => $customer->tenant_id,
                        'payment_number' => $this->billingService->generatePaymentNumber(),
                        'user_id' => $customer->user_id,
                        'amount' => $validated['amount'],
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'payment_date' => now(),
                        'status' => 'completed',
                        'notes' => $validated['notes'] ?? 'Quick recharge for customer: ' . $customer->username,
                        'collected_by' => auth()->id(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Customer recharged successfully with amount: ' . $validated['amount'],
                        'payment_id' => $payment->id,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recharge failed: ' . $e->getMessage(),
                    ], 500);
                }

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 400);
        }
    }
}
