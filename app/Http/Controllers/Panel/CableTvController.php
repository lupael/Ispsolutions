<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CableTvChannel;
use App\Models\CableTvPackage;
use App\Models\CableTvSubscription;
use App\Models\User;
use App\Services\CableTvBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'package_id' => 'required|exists:cable_tv_packages,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'installation_address' => 'nullable|string',
            'start_date' => 'required|date',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

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
    public function update(Request $request, CableTvSubscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'package_id' => 'required|exists:cable_tv_packages,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'installation_address' => 'nullable|string',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,suspended,expired,cancelled',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.cable-tv.index')
            ->with('success', 'Cable TV subscription updated successfully.');
    }

    /**
     * Delete subscription
     */
    public function destroy(CableTvSubscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('admin.cable-tv.index')
            ->with('success', 'Cable TV subscription deleted successfully.');
    }

    /**
     * Suspend subscription
     */
    public function suspend(Request $request, CableTvSubscription $subscription): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->billingService->suspendSubscription($subscription, $request->reason);

        return back()->with('success', 'Subscription suspended successfully.');
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(CableTvSubscription $subscription): RedirectResponse
    {
        $result = $this->billingService->reactivateSubscription($subscription);

        if ($result) {
            return back()->with('success', 'Subscription reactivated successfully.');
        }

        return back()->with('error', 'Cannot reactivate expired subscription.');
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

        $result = $this->billingService->renewSubscription($subscription, $validated);

        return back()->with('success', 'Subscription renewed successfully. New expiry date: ' . $result['subscription']->expiry_date->format('Y-m-d'));
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
