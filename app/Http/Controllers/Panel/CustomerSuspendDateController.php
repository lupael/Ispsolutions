<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerSuspendDateController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show edit suspend date form
     */
    public function edit(User $customer)
    {
        $this->authorize('editSuspendDate', $customer);

        return view('panels.admin.customers.suspend-date.edit', compact('customer'));
    }

    /**
     * Update suspend date
     */
    public function update(Request $request, User $customer)
    {
        $this->authorize('editSuspendDate', $customer);

        $validated = $request->validate([
            'suspend_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'auto_suspend' => 'boolean',
            'send_reminder' => 'boolean',
            'reminder_days' => 'nullable|integer|min:1|max:30',
        ]);

        try {
            DB::beginTransaction();

            $customer->update([
                'suspend_date' => $validated['suspend_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'auto_suspend' => $validated['auto_suspend'] ?? false,
            ]);

            $this->auditLogService->log(
                'suspend_date_updated',
                'Updated customer suspend/expiry dates',
                [
                    'customer_id' => $customer->id,
                    'suspend_date' => $validated['suspend_date'] ?? null,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                ]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', 'Suspend date updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update suspend date: ' . $e->getMessage());
        }
    }
}
