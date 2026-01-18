<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RechargeCard;
use App\Services\CardDistributionService;
use Illuminate\View\View;

class CardDistributorController extends Controller
{
    protected CardDistributionService $cardService;

    public function __construct(CardDistributionService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Display the card distributor dashboard.
     */
    public function dashboard(): View
    {
        $distributorId = auth()->id();

        $stats = $this->cardService->getDistributorSummary(auth()->user());

        return view('panels.card-distributor.dashboard', compact('stats'));
    }

    /**
     * Display cards listing.
     */
    public function cards(): View
    {
        $cards = RechargeCard::where('assigned_to', auth()->id())
            ->with(['generatedBy', 'usedBy'])
            ->latest()
            ->paginate(20);

        return view('panels.card-distributor.cards.index', compact('cards'));
    }

    /**
     * Display sales listing.
     */
    public function sales(): View
    {
        $sales = RechargeCard::where('assigned_to', auth()->id())
            ->where('status', 'used')
            ->with(['generatedBy', 'usedBy'])
            ->latest('used_at')
            ->paginate(20);

        return view('panels.card-distributor.sales.index', compact('sales'));
    }

    /**
     * Display balance.
     */
    public function balance(): View
    {
        $summary = $this->cardService->getDistributorSummary(auth()->user());

        return view('panels.card-distributor.balance', compact('summary'));
    }

    /**
     * Display commissions.
     */
    public function commissions(): View
    {
        // TODO: Implement commission tracking for card distributors
        // For now, return empty paginated collection to prevent blade errors
        $commissions = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            20,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panels.card-distributor.commissions.index', compact('commissions'));
    }
}
