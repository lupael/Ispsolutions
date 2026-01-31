<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResellerBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * ResellerSignupController (Operator Signup Controller)
 *
 * @deprecated Class name kept for backward compatibility. Consider using OperatorSignupController.
 *
 * Handles operator registration, approval, and onboarding workflow.
 * 
 * Terminology Update (Issue #320):
 * - "Reseller" → "Operator" (Level 30)
 * - "Sub-Reseller" → "Sub-Operator" (Level 40)
 * 
 * This controller manages the signup process for new Operators who want to
 * manage customers and earn commissions from their customer payments.
 */
class ResellerSignupController extends Controller
{
    public function __construct(
        private ResellerBillingService $resellerBillingService
    ) {
    }

    /**
     * Show operator signup form
     * 
     * @return View
     */
    public function showSignupForm(): View
    {
        return view('panel.reseller.signup');
    }

    /**
     * Handle operator signup submission
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function signup(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'business_type' => 'required|string|in:individual,company',
            'expected_customers' => 'required|integer|min:1',
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create operator account in pending state
            $reseller = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_reseller' => true, // Field name kept for backward compatibility, refers to operator
                'reseller_status' => 'pending', // pending, approved, rejected (field name kept for backward compatibility)
                'reseller_application_data' => json_encode([ // Field name kept for backward compatibility
                    'business_name' => $request->business_name,
                    'business_address' => $request->business_address,
                    'business_type' => $request->business_type,
                    'expected_customers' => $request->expected_customers,
                    'applied_at' => now()->toDateTimeString(),
                ]),
                'commission_rate' => 0.10, // Default 10% commission for operators
                'operator_level' => User::OPERATOR_LEVEL_OPERATOR, // Level 30: Operator (set upon approval)
                'status' => 'inactive', // Inactive until approved
            ]);

            // Send notification to admins for approval
            // TODO: Implement admin notification

            return redirect()
                ->route('reseller.signup.success')
                ->with('success', 'Your reseller application has been submitted successfully. You will be notified once it is reviewed.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to submit application. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show success page after signup
     */
    public function signupSuccess(): View
    {
        return view('panel.reseller.signup-success');
    }

    /**
     * Show reseller application list (Admin only)
     */
    public function listApplications(): View
    {
        $this->authorize('viewAny', User::class);

        $applications = User::where('is_reseller', true)
            ->where('reseller_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panel.reseller.applications', compact('applications'));
    }

    /**
     * Show reseller application details (Admin only)
     */
    public function viewApplication(int $id): View
    {
        $this->authorize('viewAny', User::class);

        $application = User::where('is_reseller', true)->findOrFail($id);
        $applicationData = json_decode($application->reseller_application_data, true);

        return view('panel.reseller.application-detail', compact('application', 'applicationData'));
    }

    /**
     * Approve reseller application (Admin only)
     */
    public function approveApplication(Request $request, int $id): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validator = Validator::make($request->all(), [
            'commission_rate' => 'required|numeric|min:0|max:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $reseller = User::where('is_reseller', true)->findOrFail($id);

            $reseller->update([
                'reseller_status' => 'approved',
                'status' => 'active',
                'commission_rate' => $request->commission_rate,
                'reseller_approved_at' => now(),
                'reseller_approved_by' => auth()->id(),
                'reseller_approval_notes' => $request->notes,
            ]);

            // Send approval notification to reseller
            // TODO: Implement notification

            return redirect()
                ->route('reseller.applications')
                ->with('success', "Reseller application for {$reseller->name} has been approved.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve application. Please try again.');
        }
    }

    /**
     * Reject reseller application (Admin only)
     */
    public function rejectApplication(Request $request, int $id): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $reseller = User::where('is_reseller', true)->findOrFail($id);

            $reseller->update([
                'reseller_status' => 'rejected',
                'reseller_rejected_at' => now(),
                'reseller_rejected_by' => auth()->id(),
                'reseller_rejection_reason' => $request->rejection_reason,
            ]);

            // Send rejection notification to applicant
            // TODO: Implement notification

            return redirect()
                ->route('reseller.applications')
                ->with('success', "Reseller application for {$reseller->name} has been rejected.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject application. Please try again.');
        }
    }

    /**
     * Show reseller dashboard
     */
    public function dashboard(): View
    {
        $reseller = auth()->user();

        // Check if user is a reseller
        if (!$reseller->is_reseller || $reseller->reseller_status !== 'approved') {
            abort(403, 'Access denied. You must be an approved reseller.');
        }

        // Get reseller statistics
        $childAccountsCount = $reseller->childAccounts()->count();
        $activeChildAccounts = $reseller->childAccounts()->where('status', 'active')->count();
        
        // Get revenue data for current month
        $revenueData = $this->resellerBillingService->calculateChildAccountsRevenue(
            $reseller,
            now()->startOfMonth()->toDateString(),
            now()->toDateString()
        );

        return view('panel.reseller.dashboard', compact(
            'reseller',
            'childAccountsCount',
            'activeChildAccounts',
            'revenueData'
        ));
    }

    /**
     * Show reseller commission report
     */
    public function commissionReport(Request $request): View
    {
        $reseller = auth()->user();

        // Check if user is a reseller
        if (!$reseller->is_reseller || $reseller->reseller_status !== 'approved') {
            abort(403, 'Access denied. You must be an approved reseller.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $report = $this->resellerBillingService->generateCommissionReport($reseller, $startDate, $endDate);

        return view('panel.reseller.commission-report', compact('report', 'startDate', 'endDate'));
    }
}
