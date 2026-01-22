<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionBill;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionBillingService
{
    /**
     * Generate bill for a subscription
     */
    public function generateBill(Subscription $subscription): SubscriptionBill
    {
        return DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;

            // Calculate billing period
            $billingPeriod = $this->calculateBillingPeriod($subscription);

            // Calculate amounts
            $amount = $subscription->amount ?? $plan->price;
            $tax = $this->calculateTax($amount);
            $discount = 0; // Can be customized based on business logic
            $totalAmount = $amount + $tax - $discount;

            $bill = SubscriptionBill::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'bill_number' => SubscriptionBill::generateBillNumber(),
                'billing_period_start' => $billingPeriod['start'],
                'billing_period_end' => $billingPeriod['end'],
                'amount' => $amount,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'currency' => $subscription->currency ?? 'USD',
                'status' => SubscriptionBill::STATUS_PENDING,
                'due_date' => $billingPeriod['end']->addDays(7), // 7 days grace period
            ]);

            Log::info('Subscription bill generated', [
                'subscription_id' => $subscription->id,
                'bill_id' => $bill->id,
                'amount' => $totalAmount,
            ]);

            return $bill;
        });
    }

    /**
     * Process payment for a bill
     */
    public function processBillPayment(
        SubscriptionBill $bill,
        string $paymentMethod,
        ?string $paymentReference = null
    ): SubscriptionBill {
        return DB::transaction(function () use ($bill, $paymentMethod, $paymentReference) {
            $bill->markAsPaid($paymentMethod, $paymentReference);

            // Update subscription if it was suspended
            $subscription = $bill->subscription;
            if ($subscription->status === 'suspended') {
                $subscription->update(['status' => 'active']);
            }

            Log::info('Subscription bill paid', [
                'bill_id' => $bill->id,
                'subscription_id' => $subscription->id,
                'payment_method' => $paymentMethod,
            ]);

            return $bill->fresh();
        });
    }

    /**
     * Generate bills for all active subscriptions
     */
    public function generateBillsForAllSubscriptions(): int
    {
        $subscriptions = Subscription::active()->get();
        $generated = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Check if a bill already exists for the current period
                if ($this->billExistsForCurrentPeriod($subscription)) {
                    continue;
                }

                $this->generateBill($subscription);
                $generated++;
            } catch (\Exception $e) {
                Log::error('Failed to generate bill for subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generated;
    }

    /**
     * Check if bill exists for current period
     */
    protected function billExistsForCurrentPeriod(Subscription $subscription): bool
    {
        $billingPeriod = $this->calculateBillingPeriod($subscription);

        return SubscriptionBill::where('subscription_id', $subscription->id)
            ->where('billing_period_start', $billingPeriod['start'])
            ->where('billing_period_end', $billingPeriod['end'])
            ->exists();
    }

    /**
     * Calculate billing period based on subscription
     */
    protected function calculateBillingPeriod(Subscription $subscription): array
    {
        $plan = $subscription->plan;

        // Determine reference date for billing period:
        // Prefer last_billing_date, then start_date, and finally fall back to today.
        $referenceDate = $subscription->last_billing_date ?? $subscription->start_date ?? \Carbon\Carbon::today();

        if (! $referenceDate instanceof \Carbon\Carbon) {
            $referenceDate = \Carbon\Carbon::parse($referenceDate);
        }

        $periodStart = $referenceDate->copy()->startOfDay();

        // For monthly billing: one month from the reference date
        if ($plan->billing_cycle === 'monthly') {
            $periodEnd = $periodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();

            return [
                'start' => $periodStart,
                'end' => $periodEnd,
            ];
        }

        // For yearly billing: one year from the reference date
        if ($plan->billing_cycle === 'yearly') {
            $periodEnd = $periodStart->copy()->addYear()->subDay()->endOfDay();

            return [
                'start' => $periodStart,
                'end' => $periodEnd,
            ];
        }

        // Default to monthly behavior for unknown cycles
        $periodEnd = $periodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Calculate tax (can be customized based on business logic)
     */
    protected function calculateTax(float $amount): float
    {
        $taxRate = config('billing.tax_rate', 0.0); // Default 0% if not configured

        return round($amount * $taxRate, 2);
    }

    /**
     * Send renewal reminder
     */
    public function sendRenewalReminder(Subscription $subscription): void
    {
        // Check if subscription is ending soon
        if (! $subscription->end_date || ! $subscription->end_date->isFuture()) {
            return;
        }

        $daysUntilExpiry = now()->diffInDays($subscription->end_date, false);

        // Send reminder if expiring in 7 days or less
        if ($daysUntilExpiry > 0 && $daysUntilExpiry <= 7) {
            try {
                // Get the tenant owner (subscriptions belong to tenants, not users)
                $tenant = $subscription->tenant;
                $user = $tenant?->owner;

                if ($user && $user->email) {
                    // Send email directly using Mail facade
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\SubscriptionRenewalReminder($subscription, $daysUntilExpiry)
                    );

                    Log::info('Renewal reminder sent', [
                        'subscription_id' => $subscription->id,
                        'tenant_id' => $subscription->tenant_id,
                        'user_id' => $user->id,
                        'days_until_expiry' => $daysUntilExpiry,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send renewal reminder', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Suspend subscription for overdue bills
     */
    public function suspendForOverdueBills(): int
    {
        $overdueBills = SubscriptionBill::overdue()->with('subscription')->get();
        $suspended = 0;

        foreach ($overdueBills as $bill) {
            $subscription = $bill->subscription;

            if ($subscription->status !== 'suspended') {
                $subscription->update(['status' => 'suspended']);
                $suspended++;

                Log::info('Subscription suspended for overdue bill', [
                    'subscription_id' => $subscription->id,
                    'bill_id' => $bill->id,
                ]);
            }
        }

        return $suspended;
    }

    /**
     * Calculate proration for subscription changes
     */
    public function calculateProration(Subscription $subscription, SubscriptionPlan $newPlan): float
    {
        $currentPlan = $subscription->plan;
        $now = now();

        // Get the billing period for accurate day calculation
        $billingPeriod = $this->calculateBillingPeriod($subscription);
        $periodStart = $billingPeriod['start'];
        $periodEnd = $billingPeriod['end'];

        $daysRemaining = $now->diffInDays($periodEnd, false);
        $totalDays = $periodStart->diffInDays($periodEnd);

        if ($daysRemaining <= 0 || $totalDays <= 0) {
            return $newPlan->price;
        }

        // Calculate unused amount from current plan
        $unusedAmount = ($currentPlan->price / $totalDays) * $daysRemaining;

        // Calculate prorated amount for new plan
        $proratedAmount = ($newPlan->price / $totalDays) * $daysRemaining;

        return max(0, $proratedAmount - $unusedAmount);
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradeSubscription(Subscription $subscription, SubscriptionPlan $newPlan): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlan) {
            $proratedAmount = $this->calculateProration($subscription, $newPlan);

            // Store old plan info before update
            $oldPlanName = $subscription->plan->name;

            // Update subscription
            $subscription->update([
                'plan_id' => $newPlan->id,
                'amount' => $newPlan->price,
            ]);

            // Create prorated bill if there's a difference
            if ($proratedAmount > 0) {
                SubscriptionBill::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'bill_number' => SubscriptionBill::generateBillNumber(),
                    'billing_period_start' => now(),
                    'billing_period_end' => $subscription->end_date,
                    'amount' => $proratedAmount,
                    'tax' => $this->calculateTax($proratedAmount),
                    'discount' => 0,
                    'total_amount' => $proratedAmount + $this->calculateTax($proratedAmount),
                    'currency' => $subscription->currency ?? 'USD',
                    'status' => SubscriptionBill::STATUS_PENDING,
                    'due_date' => now()->addDays(7),
                    'notes' => "Prorated upgrade from {$oldPlanName} to {$newPlan->name}",
                ]);
            }

            Log::info('Subscription upgraded', [
                'subscription_id' => $subscription->id,
                'old_plan' => $oldPlanName,
                'new_plan' => $newPlan->name,
                'prorated_amount' => $proratedAmount,
            ]);

            return $subscription->fresh();
        });
    }
}
