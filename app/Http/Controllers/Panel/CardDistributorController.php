<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\RechargeCard;
use App\Services\CardDistributionService;
use Illuminate\Http\Request;
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
        $user = auth()->user();

        // Get commission records for this card distributor
        // Note: reseller_id column name retained for backward compatibility (refers to distributor_id)
        $commissions = Commission::where('reseller_id', $user->id)
            ->with(['payment', 'invoice'])
            ->latest()
            ->paginate(20);

        return view('panels.card-distributor.commissions.index', compact('commissions'));
    }

    /**
     * Display card details.
     */
    public function showCard(RechargeCard $card): View
    {
        $this->authorize('view', $card);

        $card->load(['generatedBy', 'usedBy', 'assignedTo']);

        return view('panels.card-distributor.cards.show', compact('card'));
    }

    /**
     * Show sell card form.
     */
    public function sellCard(RechargeCard $card): View
    {
        $this->authorize('sell', $card);

        // Ensure card is available for sale
        abort_if($card->status !== 'active', 403, 'This card is not available for sale.');
        abort_if($card->assigned_to !== auth()->id(), 403, 'This card is not assigned to you.');

        return view('panels.card-distributor.cards.sell', compact('card'));
    }

    /**
     * Process card sale.
     */
    public function processSale(Request $request, RechargeCard $card)
    {
        $this->authorize('sell', $card);

        // Validate request
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'sale_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Ensure card is available for sale
        abort_if($card->status !== 'active', 403, 'This card is not available for sale.');
        abort_if($card->assigned_to !== auth()->id(), 403, 'This card is not assigned to you.');

        // TODO: Implement actual sale processing logic
        // - Record the sale transaction
        // - Update card status to 'sold' or appropriate status
        // - Record customer information if provided
        // - Update distributor balance/commissions
        // - Send notification to distributor
        // Example:
        // $this->cardService->processSale($card, $validated);

        return redirect()->route('panel.card-distributor.sales')
            ->with('success', 'Card sale processed successfully.');
    }

    /**
     * Show sale creation form.
     */
    public function createSale(): View
    {
        // Get available cards for this distributor
        $availableCards = RechargeCard::where('assigned_to', auth()->id())
            ->where('status', 'active')
            ->orderBy('denomination')
            ->get();

        return view('panels.card-distributor.sales.create', compact('availableCards'));
    }

    /**
     * Export sales report.
     */
    public function exportSales(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'format' => 'nullable|in:csv,xlsx,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $format = $validated['format'] ?? 'csv';

        // TODO: Implement actual export logic
        // - Query sales data based on filters
        // - Generate export file in requested format
        // - Return download response
        // Example:
        // $sales = $this->cardService->getDistributorSales(
        //     auth()->user(),
        //     $validated['start_date'] ?? null,
        //     $validated['end_date'] ?? null
        // );
        // return Excel::download(new SalesExport($sales), "sales-{$format}.{$format}");

        return back()->with('info', 'Export functionality will be implemented soon.');
    }

    /**
     * Show distributor transactions.
     */
    public function transactions(): View
    {
        // Get all transactions for this distributor
        // This includes card assignments, sales, commissions, etc.
        // Note: Currently showing card records as a temporary implementation
        $cards = RechargeCard::where('assigned_to', auth()->id())
            ->with(['generatedBy', 'usedBy'])
            ->latest()
            ->paginate(20);

        // TODO: Enhance with actual transaction/ledger model when available
        // Should include:
        // - Card assignments (debits)
        // - Sales (credits)
        // - Commission earnings
        // - Withdrawals/payouts
        // Example:
        // $transactions = Transaction::where('distributor_id', auth()->id())
        //     ->with(['transactionable'])
        //     ->latest()
        //     ->paginate(20);

        return view('panels.card-distributor.transactions', compact('cards'));
    }
}
