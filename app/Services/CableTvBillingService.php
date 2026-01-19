<?php

namespace App\Services;

use App\Models\CableTvSubscription;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CableTvBillingService
{
    /**
     * Generate monthly invoice for a cable TV subscription
     */
    public function generateMonthlyInvoice(CableTvSubscription $subscription): Invoice
    {
        return DB::transaction(function () use ($subscription) {
            $package = $subscription->package;
            $amount = $package->monthly_price;
            $taxRate = config('billing.tax_rate', 0);
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $periodStart = $subscription->expiry_date->addDay();
            $periodEnd = $periodStart->copy()->addDays($package->validity_days);
            $dueDate = $periodStart->copy()->addDays(7);

            $invoice = Invoice::create([
                'tenant_id' => $subscription->tenant_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $subscription->user_id,
                'package_id' => $subscription->package_id,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
                'description' => "Cable TV Subscription - {$package->name} - Subscriber: {$subscription->subscriber_id}",
            ]);

            return $invoice;
        });
    }

    /**
     * Renew a cable TV subscription with payment
     */
    public function renewSubscription(CableTvSubscription $subscription, array $paymentData): array
    {
        return DB::transaction(function () use ($subscription, $paymentData) {
            $package = $subscription->package;
            
            // Calculate new expiry date
            $newExpiryDate = $subscription->expiry_date->isPast() 
                ? now()->addDays($package->validity_days)
                : $subscription->expiry_date->addDays($package->validity_days);

            // Create invoice first (before modifying subscription)
            $invoice = $this->generateMonthlyInvoice($subscription);

            // Create payment record
            $payment = Payment::create([
                'tenant_id' => $subscription->tenant_id,
                'user_id' => $subscription->user_id,
                'invoice_id' => $invoice->id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => "Cable TV renewal - Subscriber: {$subscription->subscriber_id}",
            ]);

            // Mark invoice as paid
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
            ]);

            // Update subscription last
            $subscription->update([
                'expiry_date' => $newExpiryDate,
                'status' => 'active',
            ]);

            return [
                'subscription' => $subscription->fresh(),
                'payment' => $payment,
                'invoice' => $invoice,
            ];
        });
    }

    /**
     * Suspend a cable TV subscription
     */
    public function suspendSubscription(CableTvSubscription $subscription, string $reason): bool
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'status' => 'suspended',
                'notes' => ($subscription->notes ?? '') . "\nSuspended on " . now()->toDateTimeString() . ": {$reason}",
            ]);

            // Log activity
            // Log suspension using audit log service
            if (class_exists(\App\Services\AuditLogService::class)) {
                app(\App\Services\AuditLogService::class)->log(
                    'suspend_cable_tv_subscription',
                    $subscription,
                    ['status' => 'active'],
                    ['status' => 'suspended', 'reason' => $reason]
                );
            }

            return true;
        });
    }

    /**
     * Reactivate a suspended cable TV subscription
     */
    public function reactivateSubscription(CableTvSubscription $subscription): bool
    {
        return DB::transaction(function () use ($subscription) {
            // Check if expired
            if ($subscription->expiry_date->isPast()) {
                return false;
            }

            $subscription->update([
                'status' => 'active',
                'notes' => ($subscription->notes ?? '') . "\nReactivated on " . now()->toDateTimeString(),
            ]);

            // Log activity
            activity()
                ->performedOn($subscription)
                ->log('Cable TV subscription reactivated');

            return true;
        });
    }

    /**
     * Calculate pro-rated amount for remaining days
     */
    public function calculateProRatedAmount(CableTvSubscription $subscription, int $daysRemaining): float
    {
        $package = $subscription->package;
        $dailyRate = $package->monthly_price / $package->validity_days;
        
        return round($dailyRate * $daysRemaining, 2);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats(int $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        $subscriptions = CableTvSubscription::forTenant($tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $subscriptions->where('status', 'active')->count(),
            'suspended_subscriptions' => $subscriptions->where('status', 'suspended')->count(),
            'expired_subscriptions' => $subscriptions->where('status', 'expired')->count(),
            'cancelled_subscriptions' => $subscriptions->where('status', 'cancelled')->count(),
            'new_subscriptions' => $subscriptions->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];
    }

    /**
     * Get revenue report for cable TV
     */
    public function getRevenueReport(int $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        $payments = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('notes', 'like', '%Cable TV%')
            ->get();

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('description', 'like', '%Cable TV%')
            ->get();

        return [
            'total_revenue' => $payments->sum('amount'),
            'total_invoices' => $invoices->count(),
            'paid_invoices' => $invoices->where('status', 'paid')->count(),
            'pending_invoices' => $invoices->where('status', 'pending')->count(),
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
        ];
    }

    /**
     * Get expiring subscriptions within specified days
     */
    public function getExpiringSubscriptions(int $tenantId, int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return CableTvSubscription::forTenant($tenantId)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->with(['package', 'user'])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get subscriptions grouped by package
     */
    public function getSubscriptionsByPackage(int $tenantId): array
    {
        $subscriptions = CableTvSubscription::forTenant($tenantId)
            ->with('package')
            ->get()
            ->groupBy('package_id');

        $result = [];
        foreach ($subscriptions as $packageId => $subs) {
            $package = $subs->first()->package;
            $result[] = [
                'package_id' => $packageId,
                'package_name' => $package->name,
                'total_subscriptions' => $subs->count(),
                'active_subscriptions' => $subs->where('status', 'active')->count(),
                'monthly_revenue' => $subs->where('status', 'active')->count() * $package->monthly_price,
            ];
        }

        return $result;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'CATV-';
        return DB::transaction(function () use ($prefix) {
            $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return $prefix . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Process expired subscriptions
     */
    public function processExpiredSubscriptions(int $tenantId): int
    {
        $expiredCount = 0;

        $subscriptions = CableTvSubscription::forTenant($tenantId)
            ->where('status', 'active')
            ->where('expiry_date', '<', now()->toDateString())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            $expiredCount++;

            // Log activity
            activity()
                ->performedOn($subscription)
                ->log('Cable TV subscription automatically expired');
        }

        return $expiredCount;
    }
}
