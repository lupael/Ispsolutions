<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyRechargeService
{
    /**
     * Calculate daily rate from package price
     */
    public function calculateDailyRate(Package $package): float
    {
        if ($package->daily_rate) {
            return (float) $package->daily_rate;
        }

        // Calculate based on billing cycle
        return match ($package->billing_cycle) {
            'daily' => (float) $package->price,
            'weekly' => (float) $package->price / 7,
            'monthly' => (float) $package->price / 30,
            'quarterly' => (float) $package->price / 90,
            'yearly' => (float) $package->price / 365,
            default => (float) $package->price / 30,
        };
    }

    /**
     * Process daily recharge for a customer
     */
    public function processDailyRecharge(User $customer, Package $package, int $days = 1): array
    {
        $dailyRate = $this->calculateDailyRate($package);
        $amount = $dailyRate * $days;

        return DB::transaction(function () use ($customer, $package, $days, $amount) {
            // Create invoice
            $invoice = Invoice::create([
                'user_id' => $customer->id,
                'tenant_id' => $customer->tenant_id,
                'invoice_number' => 'INV-' . time() . '-' . $customer->id,
                'invoice_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays($days),
                'subtotal' => $amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $amount,
                'status' => 'unpaid',
                'notes' => "Daily recharge for {$days} day(s)",
            ]);

            // Create or extend subscription
            $subscription = Subscription::where('user_id', $customer->id)
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                // Extend existing subscription
                $subscription->update([
                    'end_date' => Carbon::parse($subscription->end_date)->addDays($days),
                ]);
            } else {
                // Create new subscription
                $subscription = Subscription::create([
                    'user_id' => $customer->id,
                    'tenant_id' => $customer->tenant_id,
                    'package_id' => $package->id,
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays($days),
                    'status' => 'active',
                    'auto_renew' => false,
                ]);
            }

            return [
                'success' => true,
                'invoice' => $invoice,
                'subscription' => $subscription,
                'amount' => $amount,
                'days' => $days,
            ];
        });
    }

    /**
     * Process daily recharge with payment
     */
    public function processDailyRechargeWithPayment(
        User $customer,
        Package $package,
        int $days,
        string $paymentMethod = 'cash',
        ?string $transactionId = null
    ): array {
        $rechargeResult = $this->processDailyRecharge($customer, $package, $days);

        if ($rechargeResult['success']) {
            // Create payment
            $payment = Payment::create([
                'user_id' => $customer->id,
                'tenant_id' => $customer->tenant_id,
                'invoice_id' => $rechargeResult['invoice']->id,
                'amount' => $rechargeResult['amount'],
                'payment_method' => $paymentMethod,
                'payment_date' => Carbon::now(),
                'transaction_id' => $transactionId,
                'status' => 'completed',
            ]);

            // Mark invoice as paid
            $rechargeResult['invoice']->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            $rechargeResult['payment'] = $payment;
        }

        return $rechargeResult;
    }

    /**
     * Get available daily packages
     */
    public function getAvailableDailyPackages(?int $tenantId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Package::where('billing_cycle', 'daily')
            ->where('is_active', true);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->orderBy('daily_rate')->get();
    }

    /**
     * Calculate partial day charge
     */
    public function calculatePartialDayCharge(Package $package, Carbon $startTime, Carbon $endTime): float
    {
        if (!$package->allow_partial_day) {
            return $this->calculateDailyRate($package);
        }

        $hours = $startTime->diffInHours($endTime);
        $dailyRate = $this->calculateDailyRate($package);

        return round(($dailyRate / 24) * $hours, 2);
    }

    /**
     * Get recharge history for a customer
     */
    public function getRechargeHistory(User $customer, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('user_id', $customer->id)
            ->where('notes', 'LIKE', '%Daily recharge%')
            ->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
