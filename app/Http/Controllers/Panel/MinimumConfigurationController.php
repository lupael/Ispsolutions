<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BackupSetting;
use App\Models\BillingProfile;
use App\Models\Customer;
use App\Models\CustomerImport;
use App\Models\Nas;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Minimum Configuration Controller
 *
 * Orchestrates the onboarding process for ISP Bills.
 * Ensures all necessary components are configured before operators can start managing customers.
 */
class MinimumConfigurationController extends Controller
{
    /**
     * Display the onboarding checklist.
     */
    public function index()
    {
        $user = Auth::user();
        $steps = $this->getOnboardingSteps($user);

        return view('panel.onboarding.index', [
            'steps' => $steps,
            'progress' => $this->calculateProgress($steps),
        ]);
    }

    /**
     * Get all onboarding steps with their completion status.
     */
    public function getOnboardingSteps(User $operator): array
    {
        $steps = [];

        // Step 1: Exam (Optional)
        $examEnabled = config('consumer.exam_attendance', false);
        if ($examEnabled) {
            $steps[] = [
                'number' => 1,
                'name' => 'Exam Attendance',
                'description' => 'Pass the exam if questions exist',
                'completed' => $this->checkExamCompleted($operator),
                'route' => 'exam.index',
                'required' => true,
            ];
        }

        // Step 2: Billing Profile
        $steps[] = [
            'number' => 2,
            'name' => 'Billing Profile',
            'description' => 'Create at least one billing profile',
            'completed' => $this->checkBillingProfileExists($operator),
            'route' => 'panel.admin.billing-profiles.create',
            'required' => true,
        ];

        // Step 3: Router Registration
        $steps[] = [
            'number' => 3,
            'name' => 'Router Registration',
            'description' => 'Add at least one router (NAS)',
            'completed' => $this->checkRouterExists($operator),
            'route' => 'panel.admin.network.routers.create',
            'required' => true,
        ];

        // Step 4: Customer Data
        $steps[] = [
            'number' => 4,
            'name' => 'Customer Data',
            'description' => 'Add at least one customer or import request',
            'completed' => $this->checkCustomerDataExists($operator),
            'route' => 'panel.admin.customers.create',
            'required' => true,
        ];

        // Step 5: Assign Billing Profile to Self
        if ($operator->operator_level === User::OPERATOR_LEVEL_ADMIN) {
            $steps[] = [
                'number' => 5,
                'name' => 'Assign Billing Profile to Self',
                'description' => 'Assign a billing profile to your account',
                'completed' => $this->checkOperatorHasBillingProfile($operator),
                'route' => 'panel.admin.operators.edit',
                'required' => true,
            ];
        }

        // Step 6: Assign Billing Profile to Resellers
        if ($operator->operator_level === User::OPERATOR_LEVEL_ADMIN) {
            $steps[] = [
                'number' => 6,
                'name' => 'Assign Billing Profile to Operators',
                'description' => 'All operators must have billing profiles',
                'completed' => $this->checkAllOperatorsHaveBillingProfiles($operator),
                'route' => 'panel.admin.operators.index',
                'required' => true,
            ];
        }

        // Step 7: Package Assignment
        $steps[] = [
            'number' => 7,
            'name' => 'Package Assignment',
            'description' => 'Create packages from master packages',
            'completed' => $this->checkPackagesExist($operator),
            'route' => 'panel.admin.packages.create',
            'required' => true,
        ];

        // Step 8: Package Pricing
        $steps[] = [
            'number' => 8,
            'name' => 'Package Pricing',
            'description' => 'All packages must have price > 1 (except Trial)',
            'completed' => $this->checkPackagePricing($operator),
            'route' => 'panel.admin.packages.index',
            'required' => true,
        ];

        // Step 9: Backup Settings
        $steps[] = [
            'number' => 9,
            'name' => 'Backup Settings',
            'description' => 'Configure backup settings for authentication',
            'completed' => $this->checkBackupSettingsConfigured($operator),
            'route' => 'panel.admin.backup-settings.create',
            'required' => true,
        ];

        // Step 10: Profile Completion
        $steps[] = [
            'number' => 10,
            'name' => 'Profile Completion',
            'description' => 'Complete your company profile',
            'completed' => $this->checkProfileCompleted($operator),
            'route' => 'panel.admin.profile.edit',
            'required' => true,
        ];

        return $steps;
    }

    /**
     * Check if exam is completed.
     */
    protected function checkExamCompleted(User $operator): bool
    {
        // TODO: Implement exam check logic
        // This would check if operator has passed the exam
        return true; // Placeholder
    }

    /**
     * Check if at least one billing profile exists.
     */
    protected function checkBillingProfileExists(User $operator): bool
    {
        return BillingProfile::where('operator_id', $operator->id)->count() > 0;
    }

    /**
     * Check if at least one router (NAS) is registered.
     */
    protected function checkRouterExists(User $operator): bool
    {
        return Nas::where('operator_id', $operator->id)->count() > 0;
    }

    /**
     * Check if customer data exists or import is pending.
     */
    protected function checkCustomerDataExists(User $operator): bool
    {
        $hasCustomers = Customer::where('operator_id', $operator->id)->count() > 0;
        $hasImportRequest = CustomerImport::where('operator_id', $operator->id)->exists();

        return $hasCustomers || $hasImportRequest;
    }

    /**
     * Check if operator has assigned billing profile.
     */
    protected function checkOperatorHasBillingProfile(User $operator): bool
    {
        return DB::table('billing_profile_operator')
            ->where('operator_id', $operator->id)
            ->exists();
    }

    /**
     * Check if all operators under admin have billing profiles.
     */
    protected function checkAllOperatorsHaveBillingProfiles(User $operator): bool
    {
        $operators = User::where('parent_id', $operator->id)
            ->where('operator_level', User::OPERATOR_LEVEL_OPERATOR)
            ->get();

        if ($operators->isEmpty()) {
            return true; // No operators to check
        }

        foreach ($operators as $subOperator) {
            $hasProfile = DB::table('billing_profile_operator')
                ->where('operator_id', $subOperator->id)
                ->exists();

            if (! $hasProfile) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if packages exist for operator.
     */
    protected function checkPackagesExist(User $operator): bool
    {
        return Package::where('operator_id', $operator->id)->count() > 0;
    }

    /**
     * Check if all packages have proper pricing.
     */
    protected function checkPackagePricing(User $operator): bool
    {
        $packages = Package::where('operator_id', $operator->id)->get();

        if ($packages->isEmpty()) {
            return false;
        }

        foreach ($packages as $package) {
            // Skip trial packages
            if (stripos($package->name, 'trial') !== false) {
                continue;
            }

            // Check if price is greater than 1
            if (! isset($package->price) || $package->price <= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if backup settings are configured.
     */
    protected function checkBackupSettingsConfigured(User $operator): bool
    {
        return BackupSetting::where('operator_id', $operator->id)->exists();
    }

    /**
     * Check if operator profile is completed.
     */
    protected function checkProfileCompleted(User $operator): bool
    {
        // Check if company_in_native_lang field is set
        return ! empty($operator->company_in_native_lang);
    }

    /**
     * Calculate completion progress percentage.
     */
    protected function calculateProgress(array $steps): int
    {
        $total = count($steps);
        $completed = count(array_filter($steps, fn ($step) => $step['completed']));

        return $total > 0 ? (int) (($completed / $total) * 100) : 0;
    }

    /**
     * Check if onboarding is complete.
     */
    public function isOnboardingComplete(User $operator): bool
    {
        $steps = $this->getOnboardingSteps($operator);

        foreach ($steps as $step) {
            if ($step['required'] && ! $step['completed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the next incomplete step.
     */
    public function getNextIncompleteStep(User $operator): ?array
    {
        $steps = $this->getOnboardingSteps($operator);

        foreach ($steps as $step) {
            if ($step['required'] && ! $step['completed']) {
                return $step;
            }
        }

        return null;
    }
}
