<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicePackage;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResellerController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Display the reseller dashboard.
     */
    public function dashboard(): View
    {
        $resellerId = auth()->id();
        $commissionSummary = $this->commissionService->getResellerCommissionSummary(auth()->user());
        
        $stats = [
            'total_customers' => User::where('created_by', $resellerId)->count(),
            'active_customers' => User::where('created_by', $resellerId)
                ->where('is_active', true)->count(),
            'commission_earned' => $commissionSummary['total_earned'],
            'pending_commission' => $commissionSummary['pending'],
        ];

        return view('panels.reseller.dashboard', compact('stats'));
    }

    /**
     * Display customers listing.
     */
    public function customers(): View
    {
        $resellerId = auth()->id();
        $customers = User::where('created_by', $resellerId)->latest()->paginate(20);

        return view('panels.reseller.customers.index', compact('customers'));
    }

    /**
     * Display available packages.
     */
    public function packages(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $packages = ServicePackage::where('tenant_id', $tenantId)->get();

        return view('panels.reseller.packages.index', compact('packages'));
    }

    /**
     * Display commission reports.
     */
    public function commission(): View
    {
        $commissions = Commission::where('reseller_id', auth()->id())
            ->with(['payment.user', 'invoice'])
            ->latest()
            ->paginate(20);

        $summary = $this->commissionService->getResellerCommissionSummary(auth()->user());

        return view('panels.reseller.commission', compact('commissions', 'summary'));
    }
}
