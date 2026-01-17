<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate invoice for a user and package
     */
    public function generateInvoice(User $user, ServicePackage $package, ?Carbon $billingDate = null): Invoice
    {
        $billingDate = $billingDate ?? now();
        
        return DB::transaction(function () use ($user, $package, $billingDate) {
            $amount = $package->price;
            $taxRate = config('billing.tax_rate', 0); // VAT/TAX rate from config
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            // Determine billing period based on package type
            [$periodStart, $periodEnd, $dueDate] = $this->calculateBillingPeriod($package, $billingDate);

            return Invoice::create([
                'tenant_id' => $user->tenant_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
            ]);
        });
    }

    /**
     * Process payment for an invoice
     */
    public function processPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $payment = Payment::create([
                'tenant_id' => $invoice->tenant_id,
                'payment_number' => $this->generatePaymentNumber(),
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'payment_gateway_id' => $paymentData['gateway_id'] ?? null,
                'amount' => $paymentData['amount'],
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'status' => $paymentData['status'] ?? 'completed',
                'payment_method' => $paymentData['method'] ?? 'cash',
                'payment_data' => $paymentData['data'] ?? null,
                'paid_at' => now(),
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // Update invoice status if fully paid
            if ($invoice->total_amount <= $this->getTotalPaid($invoice)) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                // Auto-unlock account on payment
                $user = $invoice->user;
                if ($user) {
                    $this->unlockAccountOnPayment($user);
                }
            }

            return $payment;
        });
    }

    /**
     * Generate invoice for daily billing
     */
    public function generateDailyInvoice(User $user, ServicePackage $package, int $validityDays = 1): Invoice
    {
        $billingDate = now();
        
        return DB::transaction(function () use ($user, $package, $validityDays, $billingDate) {
            // Calculate pro-rated amount based on validity days
            $dailyBaseDays = config('billing.daily_billing_base_days', 30);
            $dailyRate = $package->price / $dailyBaseDays;
            $amount = $dailyRate * $validityDays;
            
            $taxRate = config('billing.tax_rate', 0);
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $periodStart = $billingDate->copy()->startOfDay();
            $periodEnd = $periodStart->copy()->addDays($validityDays - 1)->endOfDay();
            $dueDate = $periodEnd->copy(); // Due immediately at end of period

            return Invoice::create([
                'tenant_id' => $user->tenant_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => round($amount, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'status' => 'pending',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
            ]);
        });
    }

    /**
     * Generate invoice for monthly billing
     */
    public function generateMonthlyInvoice(User $user, ServicePackage $package, ?Carbon $billingDate = null): Invoice
    {
        $billingDate = $billingDate ?? now();
        
        return DB::transaction(function () use ($user, $package, $billingDate) {
            $amount = $package->price;
            $taxRate = config('billing.tax_rate', 0);
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $periodStart = $billingDate->copy()->startOfDay();
            $periodEnd = $periodStart->copy()->addMonth()->endOfDay();
            $dueDate = $periodEnd->copy()->addDays(7); // 7 days grace period

            return Invoice::create([
                'tenant_id' => $user->tenant_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
            ]);
        });
    }

    /**
     * Calculate billing period based on package
     */
    protected function calculateBillingPeriod(ServicePackage $package, Carbon $billingDate): array
    {
        $periodStart = $billingDate->copy()->startOfDay();
        
        // Determine period based on package billing type
        $billingType = $package->billing_type ?? 'monthly';
        
        if ($billingType === 'daily') {
            $validityDays = $package->validity_days ?? 1;
            $periodEnd = $periodStart->copy()->addDays($validityDays)->endOfDay();
            $dueDate = $periodEnd->copy();
        } else {
            // Monthly or default
            $periodEnd = $periodStart->copy()->addMonth()->endOfDay();
            $dueDate = $periodEnd->copy()->addDays(7); // 7 days grace period
        }

        return [$periodStart, $periodEnd, $dueDate];
    }

    /**
     * Lock expired accounts
     */
    public function lockExpiredAccounts(): int
    {
        $expiredUsers = User::whereHas('invoices', function ($query) {
            $query->where('status', '!=', 'paid')
                ->whereDate('due_date', '<', today());
        })->where('is_active', true)->get();

        $count = 0;
        foreach ($expiredUsers as $user) {
            $user->update(['is_active' => false]);
            $count++;
        }

        return $count;
    }

    /**
     * Unlock account on payment
     */
    public function unlockAccountOnPayment(User $user): void
    {
        // Check if all invoices are paid or user has no overdue invoices
        $hasOverdueInvoices = $user->invoices()
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<', today())
            ->exists();

        if (!$hasOverdueInvoices && !$user->is_active) {
            $user->update(['is_active' => true]);
        }
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . str_pad(Invoice::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique payment number
     */
    protected function generatePaymentNumber(): string
    {
        return 'PAY-' . date('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get total amount paid for an invoice
     */
    protected function getTotalPaid(Invoice $invoice): float
    {
        return $invoice->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Mark overdue invoices
     */
    public function markOverdueInvoices(): int
    {
        return Invoice::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);
    }
}
