<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'onetime' => (float) $package->price / 30,
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
            // Create invoice with more robust unique number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT) . '-' . substr(uniqid(), -4);
            
            $invoice = Invoice::create([
                'user_id' => $customer->id,
                'tenant_id' => $customer->tenant_id,
                'invoice_number' => $invoiceNumber,
                'due_date' => Carbon::now()->addDays($days),
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'status' => 'pending',
                'notes' => "Daily recharge for {$days} day(s)",
            ]);

            // Update or create NetworkUser (service subscription) instead of tenant Subscription
            $networkUser = \App\Models\NetworkUser::where('user_id', $customer->id)
                ->where('package_id', $package->id)
                ->where('is_active', true)
                ->first();

            if ($networkUser) {
                // Extend existing service
                $networkUser->update([
                    'expiry_date' => Carbon::parse($networkUser->expiry_date)->addDays($days),
                ]);
            } else {
                // Create new network user service
                $networkUser = \App\Models\NetworkUser::create([
                    'user_id' => $customer->id,
                    'tenant_id' => $customer->tenant_id,
                    'username' => $customer->email,
                    'password' => bcrypt(Str::random(12)),
                    'service_type' => 'pppoe',
                    'package_id' => $package->id,
                    'expiry_date' => Carbon::now()->addDays($days),
                    'status' => 'active',
                    'is_active' => true,
                ]);
            }

            return [
                'success' => true,
                'invoice' => $invoice,
                'network_user' => $networkUser,
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
