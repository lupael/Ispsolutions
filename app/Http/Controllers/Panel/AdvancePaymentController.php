<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AdvancePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvancePaymentController extends Controller
{
    /**
     * Display advance payments for a customer.
     */
    public function index(User $customer)
    {
        $advancePayments = $customer->advancePayments()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        $totalBalance = $customer->advancePayments()
            ->sum('remaining_balance');

        return view('panel.customers.advance-payments.index', compact('customer', 'advancePayments', 'totalBalance'));
    }

    /**
     * Show form to create new advance payment.
     */
    public function create(User $customer)
    {
        return view('panel.customers.advance-payments.create', compact('customer'));
    }

    /**
     * Store a new advance payment.
     */
    public function store(Request $request, User $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_date' => 'required|date',
        ]);

        AdvancePayment::create([
            'user_id' => $customer->id,
            'amount' => $request->input('amount'),
            'remaining_balance' => $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'transaction_reference' => $request->input('transaction_reference'),
            'notes' => $request->input('notes'),
            'payment_date' => $request->input('payment_date'),
            'received_by' => Auth::id(),
        ]);

        return redirect()
            ->route('panel.customers.advance-payments.index', $customer)
            ->with('success', 'Advance payment recorded successfully.');
    }

    /**
     * Display a specific advance payment.
     */
    public function show(User $customer, AdvancePayment $advancePayment)
    {
        return view('panel.customers.advance-payments.show', compact('customer', 'advancePayment'));
    }
}
