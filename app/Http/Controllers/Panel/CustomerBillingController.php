<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use App\Models\ServicePackage;
use App\Services\BillingService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show the generate bill form
     */
    public function createBill(User $customer)
    {
        $this->authorize('generateBill', $customer);

        $packages = ServicePackage::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('panels.admin.customers.billing.generate-bill', compact('customer', 'packages'));
    }

    /**
     * Generate a new bill for customer
     */
    public function storeBill(Request $request, User $customer)
    {
        $this->authorize('generateBill', $customer);

        $validated = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'amount' => 'required|numeric|min:0.01',
            'billing_period_start' => 'required|date',
            'billing_period_end' => 'required|date|after:billing_period_start',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $amount = $validated['amount'];
            $taxRate = $validated['tax_rate'] ?? config('billing.tax_rate', 0);
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $invoice = Invoice::create([
                'tenant_id' => $customer->tenant_id,
                'invoice_number' => $this->billingService->generateInvoiceNumber(),
                'user_id' => $customer->id,
                'package_id' => $validated['package_id'],
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_period_start' => $validated['billing_period_start'],
                'billing_period_end' => $validated['billing_period_end'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->auditLogService->log(
                'invoice_generated',
                'Generated manual invoice',
                ['invoice_id' => $invoice->id, 'customer_id' => $customer->id]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', "Invoice #{$invoice->invoice_number} generated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Show edit billing profile form
     */
    public function editBillingProfile(User $customer)
    {
        $this->authorize('editBillingProfile', $customer);

        return view('panels.admin.customers.billing.edit-profile', compact('customer'));
    }

    /**
     * Update billing profile
     */
    public function updateBillingProfile(Request $request, User $customer)
    {
        $this->authorize('editBillingProfile', $customer);

        $validated = $request->validate([
            'billing_date' => 'required|integer|min:1|max:28',
            'billing_cycle' => 'required|in:monthly,daily,yearly',
            'payment_method' => 'nullable|string|max:50',
            'billing_contact_name' => 'nullable|string|max:255',
            'billing_contact_email' => 'nullable|email|max:255',
            'billing_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $customer->update([
                'billing_date' => $validated['billing_date'],
                'billing_cycle' => $validated['billing_cycle'],
                'preferred_payment_method' => $validated['payment_method'],
                'billing_contact_name' => $validated['billing_contact_name'],
                'billing_contact_email' => $validated['billing_contact_email'],
                'billing_contact_phone' => $validated['billing_contact_phone'],
            ]);

            $this->auditLogService->log(
                'billing_profile_updated',
                'Updated customer billing profile',
                ['customer_id' => $customer->id, 'changes' => $validated]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', 'Billing profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update billing profile: ' . $e->getMessage());
        }
    }

    /**
     * Show other payment form
     */
    public function createOtherPayment(User $customer)
    {
        $this->authorize('advancePayment', $customer);

        return view('panels.admin.customers.billing.other-payment', compact('customer'));
    }

    /**
     * Store other payment (non-package payment)
     */
    public function storeOtherPayment(Request $request, User $customer)
    {
        $this->authorize('advancePayment', $customer);

        $validated = $request->validate([
            'payment_type' => 'required|string|in:installation,equipment,maintenance,late_fee,other',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,online,card',
            'transaction_reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $payment = \App\Models\Payment::create([
                'tenant_id' => $customer->tenant_id,
                'payment_number' => $this->billingService->generatePaymentNumber(),
                'user_id' => $customer->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_reference'],
                'status' => 'completed',
                'payment_type' => $validated['payment_type'],
                'notes' => $validated['description'],
                'paid_at' => $validated['payment_date'],
                'collected_by' => auth()->id(),
            ]);

            $this->auditLogService->log(
                'other_payment_recorded',
                "Recorded {$validated['payment_type']} payment",
                ['payment_id' => $payment->id, 'customer_id' => $customer->id]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', "Payment recorded successfully. Reference: {$payment->payment_number}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
}
