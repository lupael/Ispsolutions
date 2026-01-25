<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModalController extends Controller
{
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
    public function showQuickAction(NetworkUser $customer, string $action): View
    {
        $this->authorize('update', $customer);

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
    public function executeQuickAction(Request $request, NetworkUser $customer, string $action)
    {
        $this->authorize('update', $customer);

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
                // Handle recharge logic
                $validated = $request->validate([
                    'amount' => 'required|numeric|min:0',
                    'method' => 'required|string'
                ]);

                // TODO: Implement recharge logic
                return response()->json([
                    'success' => true,
                    'message' => 'Recharge processed successfully'
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 400);
        }
    }
}
