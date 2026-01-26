<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOperatorController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show change operator form
     */
    public function edit(User $customer)
    {
        $this->authorize('changeOperator', $customer);

        $operators = User::where('tenant_id', $customer->tenant_id)
            ->where('operator_level', '<=', 40)
            ->where('operator_level', '>', 0)
            ->where('id', '!=', $customer->id)
            ->orderBy('name')
            ->get();

        return view('panels.admin.customers.operator.change', compact('customer', 'operators'));
    }

    /**
     * Transfer customer to another operator
     */
    public function update(Request $request, User $customer)
    {
        $this->authorize('changeOperator', $customer);

        $validated = $request->validate([
            'new_operator_id' => 'required|exists:users,id',
            'transfer_invoices' => 'boolean',
            'transfer_payments' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $newOperator = User::findOrFail($validated['new_operator_id']);

        if ($newOperator->tenant_id !== $customer->tenant_id) {
            return back()->with('error', 'Cannot transfer to operator from different tenant.');
        }

        try {
            DB::beginTransaction();

            $oldOperatorId = $customer->created_by;

            // Update customer's operator
            $customer->update([
                'created_by' => $newOperator->id,
                'manager_id' => $newOperator->id,
            ]);

            // Transfer invoices if requested
            if ($validated['transfer_invoices'] ?? false) {
                \App\Models\Invoice::where('user_id', $customer->id)
                    ->update(['created_by' => $newOperator->id]);
            }

            // Transfer payments if requested
            if ($validated['transfer_payments'] ?? false) {
                \App\Models\Payment::where('user_id', $customer->id)
                    ->update(['collected_by' => $newOperator->id]);
            }

            $this->auditLogService->log(
                'operator_changed',
                'Changed customer operator',
                [
                    'customer_id' => $customer->id,
                    'old_operator_id' => $oldOperatorId,
                    'new_operator_id' => $newOperator->id,
                    'reason' => $validated['reason'] ?? null
                ]
            );

            DB::commit();

            return redirect()
                ->route('panel.admin.customers.show', $customer->id)
                ->with('success', "Customer transferred to {$newOperator->name} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to change operator: ' . $e->getMessage());
        }
    }
}
