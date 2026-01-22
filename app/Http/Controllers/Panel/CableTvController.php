<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCableTvSubscriptionRequest;
use App\Http\Requests\UpdateCableTvSubscriptionRequest;
use App\Models\CableTvChannel;
use App\Models\CableTvPackage;
use App\Models\CableTvSubscription;
use App\Models\User;
use App\Services\CableTvBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CableTvController extends Controller
{
    public function __construct(private CableTvBillingService $billingService)
    {
    }

    /**
     * Display subscriptions listing
     */
    public function index(Request $request): View
    {
        $query = CableTvSubscription::with(['package', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subscriber_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        $subscriptions = $query->latest()->paginate(20);
        $packages = CableTvPackage::active()->get();

        return view('panels.admin.cable-tv.index', compact('subscriptions', 'packages'));
    }

    /**
     * Show create subscription form
     */
    public function create(): View
    {
        $packages = CableTvPackage::active()->with('channels')->get();
        $users = User::where('is_active', true)->get();

        return view('panels.admin.cable-tv.create', compact('packages', 'users'));
    }

    /**
     * Store new subscription
     */
    public function store(StoreCableTvSubscriptionRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            $package = CableTvPackage::findOrFail($validated['package_id']);
            
            // Generate unique subscriber ID
            $subscriberId = $this->generateSubscriberId();
            
            // Calculate expiry date
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $expiryDate = $startDate->copy()->addDays($package->validity_days);

            $subscription = CableTvSubscription::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => $validated['user_id'] ?? null,
                'package_id' => $validated['package_id'],
                'subscriber_id' => $subscriberId,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'installation_address' => $validated['installation_address'] ?? null,
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
                'auto_renew' => $validated['auto_renew'] ?? false,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('admin.cable-tv.index')
                ->with('success', 'Cable TV subscription created successfully. Subscriber ID: ' . $subscriberId);
        } catch (\Exception $e) {
            Log::error('Failed to create cable TV subscription: ' . $e->getMessage(), [
                'data' => $request->validated(),
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to create subscription. Please try again.');
        }
    }

    /**
     * Show edit subscription form
     */
    public function edit(CableTvSubscription $subscription): View
    {
        $packages = CableTvPackage::active()->with('channels')->get();
        $users = User::where('is_active', true)->get();

        return view('panels.admin.cable-tv.edit', compact('subscription', 'packages', 'users'));
    }

    /**
     * Update subscription
     */
    public function update(UpdateCableTvSubscriptionRequest $request, CableTvSubscription $subscription): RedirectResponse
    {
        try {
            $subscription->update($request->validated());

            return redirect()->route('admin.cable-tv.index')
                ->with('success', 'Cable TV subscription updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update cable TV subscription: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to update subscription. Please try again.');
        }
    }

    /**
     * Delete subscription
     */
    public function destroy(CableTvSubscription $subscription): RedirectResponse
    {
        try {
            $subscription->delete();

            return redirect()->route('admin.cable-tv.index')
                ->with('success', 'Cable TV subscription deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete cable TV subscription: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);
            
            return back()->with('error', 'Failed to delete subscription. Please try again.');
        }
    }

    /**
     * Suspend subscription
     */
    public function suspend(Request $request, CableTvSubscription $subscription): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->billingService->suspendSubscription($subscription, $request->reason);

            return back()->with('success', 'Subscription suspended successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to suspend cable TV subscription: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);
            
            return back()->with('error', 'Failed to suspend subscription. Please try again.');
        }
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(CableTvSubscription $subscription): RedirectResponse
    {
        try {
            $result = $this->billingService->reactivateSubscription($subscription);

            if ($result) {
                return back()->with('success', 'Subscription reactivated successfully.');
            }

            return back()->with('error', 'Cannot reactivate expired subscription.');
        } catch (\Exception $e) {
            Log::error('Failed to reactivate cable TV subscription: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);
            
            return back()->with('error', 'Failed to reactivate subscription. Please try again.');
        }
    }

    /**
     * Renew subscription
     */
    public function renew(Request $request, CableTvSubscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        try {
            $result = $this->billingService->renewSubscription($subscription, $validated);

            return back()->with('success', 'Subscription renewed successfully. New expiry date: ' . $result['subscription']->expiry_date->format('Y-m-d'));
        } catch (\Exception $e) {
            Log::error('Failed to renew cable TV subscription: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
            ]);
            
            return back()->with('error', 'Failed to renew subscription. Please try again.');
        }
    }

    /**
     * Packages management
     */
    public function packagesIndex(): View
    {
        $packages = CableTvPackage::with('channels')->withCount('subscriptions')->latest()->paginate(20);

        return view('panels.admin.cable-tv.packages.index', compact('packages'));
    }

    /**
     * Channels management
     */
    public function channelsIndex(): View
    {
        $channels = CableTvChannel::withCount('packages')->latest()->paginate(20);

        return view('panels.admin.cable-tv.channels.index', compact('channels'));
    }

    /**
     * Generate unique subscriber ID
     */
    private function generateSubscriberId(): string
    {
        return DB::transaction(function () {
            $prefix = 'CATV-';
            $lastSubscription = CableTvSubscription::where('subscriber_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastSubscription) {
                $lastNumber = (int) substr($lastSubscription->subscriber_id, strlen($prefix));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}
