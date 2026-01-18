<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\IpAllocation;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaticIpBillingService
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Generate monthly invoice for static IP allocation
     */
    public function generateMonthlyInvoice(User $user, IpAllocation $ipAllocation, ServicePackage $package, ?Carbon $billingDate = null): Invoice
    {
        $billingDate = $billingDate ?? now();

        return DB::transaction(function () use ($user, $ipAllocation, $package, $billingDate) {
            $amount = $package->price;
            $taxRate = config('billing.tax_rate', 0);
            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $periodStart = $billingDate->copy()->startOfDay();
            $periodEnd = $periodStart->copy()->addMonth()->endOfDay();
            $dueDate = $periodEnd->copy()->addDays(7); // 7 days grace period

            return Invoice::create([
                'tenant_id' => $user->tenant_id,
                'invoice_number' => $this->billingService->generateInvoiceNumber(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
                'notes' => "Static IP: {$ipAllocation->ip_address}",
            ]);
        });
    }

    /**
     * Generate invoices for all active static IP allocations
     */
    public function generateMonthlyInvoicesForStaticIPs(): int
    {
        $activeAllocations = IpAllocation::where('status', 'allocated')
            ->whereNotNull('username')
            ->get();

        $count = 0;

        foreach ($activeAllocations as $allocation) {
            // Find user by username (adjust based on your user model)
            $user = User::where('username', $allocation->username)->first();
            
            if (!$user) {
                continue;
            }

            // Check if user already has an invoice for this month
            $hasInvoiceThisMonth = Invoice::where('user_id', $user->id)
                ->whereYear('billing_period_start', now()->year)
                ->whereMonth('billing_period_start', now()->month)
                ->where('notes', 'like', "%Static IP: {$allocation->ip_address}%")
                ->exists();

            if ($hasInvoiceThisMonth) {
                continue;
            }

            // Get static IP package (you may need to adjust this logic)
            $package = $this->getStaticIpPackage($user);
            
            if ($package) {
                $this->generateMonthlyInvoice($user, $allocation, $package);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get static IP package for user
     * This is a helper method - adjust based on your business logic
     */
    protected function getStaticIpPackage(User $user): ?ServicePackage
    {
        // Option 1: User has a specific static IP package assigned
        if ($user->package && $user->package->billing_type === 'monthly') {
            return $user->package;
        }

        // Option 2: Get default static IP package
        return ServicePackage::where('name', 'like', '%Static IP%')
            ->where('billing_type', 'monthly')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Allocate static IP to user
     */
    public function allocateStaticIp(User $user, string $ipAddress, ServicePackage $package): IpAllocation
    {
        return DB::transaction(function () use ($user, $ipAddress, $package) {
            // Find or create IP allocation
            $allocation = IpAllocation::updateOrCreate(
                ['ip_address' => $ipAddress],
                [
                    'username' => $user->username,
                    'allocated_at' => now(),
                    'status' => 'allocated',
                ]
            );

            // Generate initial invoice
            $this->generateMonthlyInvoice($user, $allocation, $package);

            return $allocation;
        });
    }

    /**
     * Release static IP from user
     */
    public function releaseStaticIp(IpAllocation $allocation): IpAllocation
    {
        $allocation->update([
            'username' => null,
            'released_at' => now(),
            'status' => 'available',
        ]);

        return $allocation;
    }

    /**
     * Get static IP allocation statistics
     */
    public function getStaticIpStats(int $tenantId): array
    {
        // Get users from tenant
        $tenantUsernames = User::where('tenant_id', $tenantId)->pluck('username')->toArray();

        $query = IpAllocation::whereIn('username', $tenantUsernames);

        return [
            'total_allocated' => (clone $query)->where('status', 'allocated')->count(),
            'total_available' => IpAllocation::where('status', 'available')->count(),
            'revenue_this_month' => Invoice::whereIn('user_id', User::where('tenant_id', $tenantId)->pluck('id'))
                ->where('notes', 'like', '%Static IP:%')
                ->whereYear('billing_period_start', now()->year)
                ->whereMonth('billing_period_start', now()->month)
                ->sum('total_amount'),
        ];
    }

    /**
     * Get allocations by user
     */
    public function getUserAllocations(User $user)
    {
        return IpAllocation::where('username', $user->username)
            ->where('status', 'allocated')
            ->get();
    }
}
