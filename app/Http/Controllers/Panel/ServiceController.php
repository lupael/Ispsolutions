<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CableTvSubscription;
use App\Models\CableTvPackage;
use App\Models\IpAllocation;
use App\Models\MikrotikPppoeUser;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display service dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Get existing services
        $cableTvSubscription = CableTvSubscription::where('customer_id', $user->id)
            ->with('package')
            ->first();

        $staticIps = IpAllocation::where('user_id', $user->id)
            ->where('allocation_type', 'static')
            ->get();

        $pppoeAccounts = MikrotikPppoeUser::where('tenant_id', $user->tenant_id)
            ->whereHas('networkUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return view('panels.customer.services.index', compact(
            'cableTvSubscription',
            'staticIps',
            'pppoeAccounts'
        ));
    }

    /**
     * Show service order form.
     */
    public function orderForm(string $serviceType): View
    {
        $user = auth()->user();

        $data = [];
        switch ($serviceType) {
            case 'cable-tv':
                $data['packages'] = CableTvPackage::where('tenant_id', $user->tenant_id)
                    ->where('is_active', true)
                    ->get();
                break;
            case 'static-ip':
                // Check available IPs
                $data['availableIps'] = IpAllocation::where('tenant_id', $user->tenant_id)
                    ->whereNull('user_id')
                    ->count();
                break;
            case 'pppoe':
                $data['info'] = 'PPPoE account will be created by admin upon approval.';
                break;
            default:
                abort(404);
        }

        return view('panels.customer.services.order', compact('serviceType', 'data'));
    }

    /**
     * Submit service order request.
     */
    public function submitOrder(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'service_type' => 'required|in:cable-tv,static-ip,pppoe',
            'package_id' => 'required_if:service_type,cable-tv|exists:cable_tv_packages,id,tenant_id,' . $user->tenant_id . ',is_active,1',
            'notes' => 'nullable|string|max:500',
        ]);

        // Build ticket message with service details
        $message = $request->notes ?? 'Customer requested ' . $request->service_type . ' service.';
        
        // Add package details for Cable TV orders
        if ($request->service_type === 'cable-tv' && $request->filled('package_id')) {
            $package = CableTvPackage::where('tenant_id', $user->tenant_id)
                ->where('id', $request->package_id)
                ->where('is_active', true)
                ->first();
            
            if ($package) {
                $message .= "\n\n--- Service Details ---\n";
                $message .= "Package: " . $package->name . "\n";
                $message .= "Price: " . $package->price . " BDT/month\n";
            }
        }

        // Create ticket for service request
        \App\Models\Ticket::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $user->id,
            'subject' => 'Service Order: ' . strtoupper(str_replace('-', ' ', $request->service_type)),
            'message' => $message,
            'priority' => 'medium',
            'status' => 'open',
            'category' => 'general',
            'created_by' => $user->id,
        ]);

        return redirect()->route('panel.customer.services.index')
            ->with('success', 'Service order request submitted. Our team will contact you soon.');
    }
}
