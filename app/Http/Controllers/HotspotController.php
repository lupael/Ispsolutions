<?php

namespace App\Http\Controllers;

use App\Models\HotspotUser;
use App\Models\Package;
use App\Services\HotspotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HotspotController extends Controller
{
    protected HotspotService $hotspotService;

    public function __construct(HotspotService $hotspotService)
    {
        $this->hotspotService = $hotspotService;
    }

    /**
     * Display hotspot users listing
     */
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        
        if ($tenantId === null) {
            abort(403, 'User must be assigned to a tenant to access hotspot management.');
        }
        
        $search = $request->get('search');
        $status = $request->get('status');

        $users = $this->hotspotService->searchUsers($tenantId, $search, $status);
        $stats = $this->hotspotService->getUserStats($tenantId);

        return view('hotspot.index', compact('users', 'stats', 'search', 'status'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $packages = Package::where('is_active', true)->get();

        return view('hotspot.create', compact('packages'));
    }

    /**
     * Store new hotspot user
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20|unique:hotspot_users,phone_number',
            'username' => 'nullable|string|max:50|unique:hotspot_users,username',
            'password' => 'nullable|string|min:6|max:50',
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            $tenantId = auth()->user()->tenant_id;
            
            if ($tenantId === null) {
                return back()->withErrors(['error' => 'User must be assigned to a tenant.'])->withInput();
            }
            
            $validated['tenant_id'] = $tenantId;

            $hotspotUser = $this->hotspotService->createHotspotUser($validated);

            return redirect()->route('hotspot.show', $hotspotUser)
                ->with('success', 'Hotspot user created successfully.')
                ->with('credentials', [
                    'username' => $hotspotUser->username,
                    'password' => $hotspotUser->plain_password,
                ]);
        } catch (\Exception $e) {
            Log::error('Hotspot user creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return back()->withErrors(['error' => 'Failed to create hotspot user.'])->withInput();
        }
    }

    /**
     * Show hotspot user details
     */
    public function show(HotspotUser $hotspotUser): View
    {
        $this->authorize('view', $hotspotUser);

        $hotspotUser->load(['package']);

        return view('hotspot.show', compact('hotspotUser'));
    }

    /**
     * Show edit form
     */
    public function edit(HotspotUser $hotspotUser): View
    {
        $this->authorize('update', $hotspotUser);

        $packages = Package::where('is_active', true)->get();

        return view('hotspot.edit', compact('hotspotUser', 'packages'));
    }

    /**
     * Update hotspot user
     */
    public function update(Request $request, HotspotUser $hotspotUser): RedirectResponse
    {
        $this->authorize('update', $hotspotUser);

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20|unique:hotspot_users,phone_number,' . $hotspotUser->id,
            'username' => 'required|string|max:50|unique:hotspot_users,username,' . $hotspotUser->id,
            'password' => 'nullable|string|min:6|max:50',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,suspended,expired,pending',
        ]);

        try {
            if (! empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $hotspotUser->update($validated);

            return redirect()->route('hotspot.show', $hotspotUser)
                ->with('success', 'Hotspot user updated successfully.');
        } catch (\Exception $e) {
            Log::error('Hotspot user update failed', [
                'error' => $e->getMessage(),
                'user_id' => $hotspotUser->id,
            ]);

            return back()->withErrors(['error' => 'Failed to update hotspot user.'])->withInput();
        }
    }

    /**
     * Suspend hotspot user
     */
    public function suspend(HotspotUser $hotspotUser): RedirectResponse
    {
        $this->authorize('update', $hotspotUser);

        try {
            $this->hotspotService->suspend($hotspotUser);

            return back()->with('success', 'Hotspot user suspended successfully.');
        } catch (\Exception $e) {
            Log::error('Hotspot user suspension failed', [
                'error' => $e->getMessage(),
                'user_id' => $hotspotUser->id,
            ]);

            return back()->withErrors(['error' => 'Failed to suspend hotspot user.']);
        }
    }

    /**
     * Reactivate hotspot user
     */
    public function reactivate(HotspotUser $hotspotUser): RedirectResponse
    {
        $this->authorize('update', $hotspotUser);

        try {
            $this->hotspotService->reactivate($hotspotUser);

            return back()->with('success', 'Hotspot user reactivated successfully.');
        } catch (\Exception $e) {
            Log::error('Hotspot user reactivation failed', [
                'error' => $e->getMessage(),
                'user_id' => $hotspotUser->id,
            ]);

            return back()->withErrors(['error' => 'Failed to reactivate hotspot user.']);
        }
    }

    /**
     * Renew hotspot user subscription
     */
    public function renew(Request $request, HotspotUser $hotspotUser): RedirectResponse
    {
        $this->authorize('update', $hotspotUser);

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            $this->hotspotService->renewSubscription($hotspotUser, $validated['package_id']);

            return back()->with('success', 'Subscription renewed successfully.');
        } catch (\Exception $e) {
            Log::error('Subscription renewal failed', [
                'error' => $e->getMessage(),
                'user_id' => $hotspotUser->id,
            ]);

            return back()->withErrors(['error' => 'Failed to renew subscription.']);
        }
    }

    /**
     * Delete hotspot user
     */
    public function destroy(HotspotUser $hotspotUser): RedirectResponse
    {
        $this->authorize('delete', $hotspotUser);

        try {
            $hotspotUser->delete();

            return redirect()->route('hotspot.index')
                ->with('success', 'Hotspot user deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Hotspot user deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $hotspotUser->id,
            ]);

            return back()->withErrors(['error' => 'Failed to delete hotspot user.']);
        }
    }

    // ==================== Public Self-Signup Methods ====================

    /**
     * Show self-signup form
     */
    public function signupForm(): View
    {
        $packages = Package::where('is_active', true)
            ->where('billing_type', 'daily')
            ->get();

        return view('hotspot.signup', compact('packages'));
    }

    /**
     * Request OTP for self-signup
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        try {
            $hotspotUser = $this->hotspotService->generateOTP(
                $validated['phone_number'],
                $validated['tenant_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully.',
                // In production, don't send OTP in response
                'otp' => config('app.debug') ? $hotspotUser->plain_otp : null,
                'expires_at' => $hotspotUser->otp_expires_at,
            ]);
        } catch (\Exception $e) {
            Log::error('OTP generation failed', [
                'error' => $e->getMessage(),
                'phone_number' => $validated['phone_number'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify OTP and complete signup
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'otp_code' => 'required|string|size:6',
            'package_id' => 'required|exists:packages,id',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        try {
            $hotspotUser = $this->hotspotService->verifyOTP(
                $validated['phone_number'],
                $validated['otp_code'],
                $validated['package_id'],
                $validated['tenant_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Account activated successfully.',
                'credentials' => [
                    'username' => $hotspotUser->username,
                    'password' => $hotspotUser->plain_password,
                ],
                'expires_at' => $hotspotUser->expires_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
