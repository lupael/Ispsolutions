<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Show wallet adjustment form.
     */
    public function adjustForm(User $user): View
    {
        // Ensure tenant isolation
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('panels.admin.wallet.adjust', compact('user'));
    }

    /**
     * Process wallet adjustment.
     */
    public function adjust(Request $request, User $user): RedirectResponse
    {
        // Ensure tenant isolation
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:255',
        ]);

        try {
            $this->walletService->adjust(
                $user,
                (float) $validated['amount'],
                $validated['description'],
                auth()->id()
            );

            $action = $validated['amount'] > 0 ? 'credited' : 'debited';
            return redirect()->back()->with('success', "Wallet balance {$action} successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show wallet transaction history.
     */
    public function history(User $user): View
    {
        // Ensure tenant isolation
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $transactions = $user->walletTransactions()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panels.admin.wallet.history', compact('user', 'transactions'));
    }
}
