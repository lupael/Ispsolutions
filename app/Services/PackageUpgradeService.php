<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Package Upgrade Service
 * 
 * Handles package upgrade operations including:
 * - Calculating upgrade costs
 * - Validating upgrade eligibility
 * - Processing package transitions
 */
class PackageUpgradeService
{
    protected PackageHierarchyService $hierarchyService;

    public function __construct(PackageHierarchyService $hierarchyService)
    {
        $this->hierarchyService = $hierarchyService;
    }

    /**
     * Get available upgrade options for a customer
     * 
     * @param User $customer
     * @return array
     */
    public function getUpgradeOptions(User $customer): array
    {
        // Get the NetworkUser's package (Package model), not User's servicePackage (ServicePackage model)
        $networkUser = $customer->networkUser;
        $currentPackage = $networkUser?->package;

        if (!$currentPackage) {
            return [
                'has_package' => false,
                'message' => __('Customer does not have an active package'),
                'upgrades' => collect(),
            ];
        }

        $upgradePaths = $this->hierarchyService->getUpgradePaths($currentPackage);

        $upgradeOptions = $upgradePaths->map(function ($targetPackage) use ($currentPackage) {
            return $this->hierarchyService->calculateUpgrade($currentPackage, $targetPackage);
        });

        return [
            'has_package' => true,
            'current_package' => [
                'id' => $currentPackage->id,
                'name' => $currentPackage->name,
                'price' => $currentPackage->price,
                'speed_download' => $currentPackage->bandwidth_download,
                'speed_upload' => $currentPackage->bandwidth_upload,
                'validity_days' => $currentPackage->validity_days,
            ],
            'upgrades' => $upgradeOptions,
            'upgrade_count' => $upgradeOptions->count(),
        ];
    }

    /**
     * Calculate prorated cost for upgrade
     * 
     * @param User $customer
     * @param Package $newPackage
     * @return array
     */
    public function calculateProratedCost(User $customer, Package $newPackage): array
    {
        // Get the NetworkUser's package (Package model), not User's servicePackage (ServicePackage model)
        $networkUser = $customer->networkUser;
        $currentPackage = $networkUser?->package;
        
        if (!$currentPackage) {
            return [
                'error' => __('Customer does not have an active package'),
            ];
        }

        // Get remaining days (ensure non-negative)
        $expiryDate = $customer->expiry_date ?? now();
        $remainingDays = max(0, now()->diffInDays($expiryDate, false));

        // Calculate prorated amounts (prevent division by zero)
        $currentDailyRate = $currentPackage->price / max(1, $currentPackage->validity_days);
        $newDailyRate = $newPackage->price / max(1, $newPackage->validity_days);

        $unusedAmount = $currentDailyRate * $remainingDays;
        $newPackageCost = $newDailyRate * $remainingDays;
        $upgradeCost = max(0, $newPackageCost - $unusedAmount);

        return [
            'current_package' => [
                'name' => $currentPackage->name,
                'price' => $currentPackage->price,
                'daily_rate' => round($currentDailyRate, 2),
            ],
            'new_package' => [
                'name' => $newPackage->name,
                'price' => $newPackage->price,
                'daily_rate' => round($newDailyRate, 2),
            ],
            'remaining_days' => $remainingDays,
            'unused_amount' => round($unusedAmount, 2),
            'new_package_cost' => round($newPackageCost, 2),
            'upgrade_cost' => round($upgradeCost, 2),
            'total_to_pay' => round($upgradeCost, 2),
        ];
    }

    /**
     * Validate if customer is eligible for upgrade
     * 
     * @param User $customer
     * @param Package $targetPackage
     * @return array
     */
    public function validateUpgradeEligibility(User $customer, Package $targetPackage): array
    {
        $errors = [];
        $warnings = [];

        // Get the NetworkUser's package (Package model), not User's servicePackage
        $networkUser = $customer->networkUser;
        $currentPackage = $networkUser?->package;

        // Check if customer has a package
        if (!$currentPackage) {
            $errors[] = __('Customer does not have an active package');
        }

        // Check if target package is active
        if ($targetPackage->status !== 'active') {
            $errors[] = __('Target package is not active');
        }

        // Check if it's actually an upgrade
        if ($currentPackage && $targetPackage->price < $currentPackage->price) {
            $warnings[] = __('This is a downgrade, not an upgrade');
        }

        // Check customer status
        if ($customer->status === 'suspended') {
            $warnings[] = __('Customer is currently suspended');
        }

        // Check if customer has outstanding balance
        $balance = $customer->balance ?? 0;
        if ($balance < 0) {
            $warnings[] = __('Customer has outstanding balance of :amount', [
                'amount' => abs($balance),
            ]);
        }

        return [
            'eligible' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Preview the upgrade
     * 
     * @param User $customer
     * @param Package $targetPackage
     * @return array
     */
    public function previewUpgrade(User $customer, Package $targetPackage): array
    {
        $eligibility = $this->validateUpgradeEligibility($customer, $targetPackage);
        $costDetails = $this->calculateProratedCost($customer, $targetPackage);
        
        // Get the NetworkUser's package (Package model)
        $networkUser = $customer->networkUser;
        $currentPackage = $networkUser?->package;
        
        $comparison = $currentPackage 
            ? $this->hierarchyService->calculateUpgrade($currentPackage, $targetPackage)
            : null;

        return [
            'eligibility' => $eligibility,
            'cost_details' => $costDetails,
            'comparison' => $comparison,
            'preview' => [
                'new_speed_download' => $targetPackage->bandwidth_download,
                'new_speed_upload' => $targetPackage->bandwidth_upload,
                'new_validity' => $targetPackage->validity_days,
                'new_price' => $targetPackage->price,
            ],
        ];
    }

    /**
     * Process the upgrade (updates customer package)
     * 
     * @param User $customer
     * @param Package $targetPackage
     * @param bool $payNow Whether payment is being made now
     * @return array
     */
    public function processUpgrade(User $customer, Package $targetPackage, bool $payNow = false): array
    {
        $eligibility = $this->validateUpgradeEligibility($customer, $targetPackage);
        
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'errors' => $eligibility['errors'],
            ];
        }

        DB::beginTransaction();
        try {
            $costDetails = $this->calculateProratedCost($customer, $targetPackage);

            // Update package
            $customer->service_package_id = $targetPackage->id;
            
            // If paying now, deduct from balance
            if ($payNow) {
                $customer->balance = ($customer->balance ?? 0) - $costDetails['upgrade_cost'];
            }

            $customer->save();

            DB::commit();

            return [
                'success' => true,
                'message' => __('Package upgraded successfully'),
                'cost_details' => $costDetails,
                'new_package' => [
                    'id' => $targetPackage->id,
                    'name' => $targetPackage->name,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'errors' => [__('Failed to process upgrade: :error', ['error' => $e->getMessage()])],
            ];
        }
    }
}
