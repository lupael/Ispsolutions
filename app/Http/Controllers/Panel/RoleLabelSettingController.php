<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleLabelSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoleLabelSettingController extends Controller
{
    /**
     * Display the role label settings management page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get customizable roles (operator and sub-operator)
        $customizableRoles = Role::whereIn('slug', ['operator', 'sub-operator'])->get();

        // Get current settings for this tenant
        $settings = RoleLabelSetting::where('tenant_id', $tenantId)
            ->get()
            ->keyBy('role_slug');

        return view('panels.admin.settings.role-labels', compact('customizableRoles', 'settings'));
    }

    /**
     * Update or create a role label setting.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_slug' => 'required|string|in:operator,sub-operator',
            'custom_label' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if (empty($validated['custom_label'])) {
            // Remove custom label if empty
            RoleLabelSetting::removeCustomLabel($tenantId, $validated['role_slug']);

            return redirect()->back()->with('success', 'Custom role label removed. Using default label.');
        }

        // Set or update custom label
        RoleLabelSetting::setCustomLabel($tenantId, $validated['role_slug'], $validated['custom_label']);

        return redirect()->back()->with('success', 'Role label updated successfully.');
    }

    /**
     * Remove a custom role label.
     */
    public function destroy(string $roleSlug): RedirectResponse
    {
        // Validate role slug
        if (! in_array($roleSlug, ['operator', 'sub-operator'])) {
            return redirect()->back()->with('error', 'Invalid role specified.');
        }

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        RoleLabelSetting::removeCustomLabel($tenantId, $roleSlug);

        return redirect()->back()->with('success', 'Custom role label removed successfully.');
    }
}
