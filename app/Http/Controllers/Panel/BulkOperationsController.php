<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkActionRequest;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Traits\HandlesFormValidation;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkOperationsController extends Controller
{
    use HandlesFormValidation;

    /**
     * Bulk delete customers (User model with operator_level = 100).
     * Note: Migrated from NetworkUser to User model.
     */
    public function bulkDeleteNetworkUsers(BulkDeleteRequest $request): RedirectResponse
    {
        return $this->handleBulkOperation(
            $request->validated('ids'),
            function ($id) {
                $user = User::where('operator_level', 100)->findOrFail($id);
                $this->authorize('delete', $user);
                $user->delete();
            },
            '%d customers deleted successfully.',
            'Bulk delete customers'
        );
    }

    /**
     * Bulk action on customers (User model with operator_level = 100).
     * Note: Migrated from NetworkUser to User model.
     */
    public function bulkActionNetworkUsers(BulkActionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $action = $validated['action'];
        $ids = $validated['ids'];

        return match ($action) {
            'activate' => $this->bulkActivateUsers($ids),
            'deactivate' => $this->bulkDeactivateUsers($ids),
            'suspend' => $this->bulkSuspendUsers($ids),
            'delete' => $this->bulkDeleteUsers($ids),
            'generate_invoice' => $this->bulkGenerateInvoices($ids),
            default => back()->with('error', 'Invalid action selected.'),
        };
    }

    /**
     * Bulk activate users.
     */
    protected function bulkActivateUsers(array $ids): RedirectResponse
    {
        return $this->handleBulkOperation(
            $ids,
            function ($id) {
                $user = User::where('operator_level', 100)->findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['is_active' => true, 'status' => 'active']);
            },
            '%d customers activated successfully.',
            'Bulk activate customers'
        );
    }

    /**
     * Bulk deactivate users.
     */
    protected function bulkDeactivateUsers(array $ids): RedirectResponse
    {
        return $this->handleBulkOperation(
            $ids,
            function ($id) {
                $user = User::where('operator_level', 100)->findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['is_active' => false, 'status' => 'inactive']);
            },
            '%d customers deactivated successfully.',
            'Bulk deactivate customers'
        );
    }

    /**
     * Bulk suspend users.
     */
    protected function bulkSuspendUsers(array $ids): RedirectResponse
    {
        return $this->handleBulkOperation(
            $ids,
            function ($id) {
                $user = User::where('operator_level', 100)->findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['status' => 'suspended']);
            },
            '%d customers suspended successfully.',
            'Bulk suspend customers'
        );
    }

    /**
     * Bulk delete users.
     */
    protected function bulkDeleteUsers(array $ids): RedirectResponse
    {
        return $this->handleBulkOperation(
            $ids,
            function ($id) {
                $user = User::where('operator_level', 100)->findOrFail($id);
                $this->authorize('delete', $user);
                $user->delete();
            },
            '%d customers deleted successfully.',
            'Bulk delete customers'
        );
    }

    /**
     * Bulk generate invoices.
     */
    protected function bulkGenerateInvoices(array $ids): RedirectResponse
    {
        try {
            $successCount = 0;
            $failedCount = 0;

            DB::transaction(function () use ($ids, &$successCount, &$failedCount) {
                foreach ($ids as $id) {
                    try {
                        $customer = User::where('operator_level', 100)
                            ->with('servicePackage')->findOrFail($id);

                        if (! $customer->servicePackage) {
                            $failedCount++;

                            continue;
                        }

                        // Check if customer already has a pending invoice
                        $existingInvoice = Invoice::where('user_id', $customer->id)
                            ->whereIn('status', ['pending', 'overdue'])
                            ->exists();

                        if ($existingInvoice) {
                            $failedCount++;

                            continue;
                        }

                        // Generate invoice
                        $invoice = Invoice::create([
                            'tenant_id' => $customer->tenant_id,
                            'user_id' => $customer->id,
                            'package_id' => $customer->service_package_id,
                            'invoice_number' => $this->generateInvoiceNumber(),
                            'amount' => $customer->servicePackage->price_monthly,
                            'tax_amount' => $customer->servicePackage->price_monthly * 0.15, // 15% tax
                            'total_amount' => $customer->servicePackage->price_monthly * 1.15,
                            'billing_period_start' => now(),
                            'billing_period_end' => now()->addMonth(),
                            'due_date' => now()->addDays(7),
                            'status' => 'pending',
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        Log::error("Failed to generate invoice for customer ID {$id}: " . $e->getMessage());
                        $failedCount++;
                    }
                }
            });

            if ($failedCount === 0) {
                return back()->with('success', "{$successCount} invoices generated successfully.");
            } elseif ($successCount === 0) {
                return back()->with('error', 'Failed to generate any invoices. Check logs for details.');
            } else {
                return back()->with('warning', "Partial success: {$successCount} invoices generated, {$failedCount} failed.");
            }
        } catch (\Exception $e) {
            Log::error('Bulk invoice generation failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to generate invoices. Please try again.');
        }
    }

    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Bulk lock/unlock users.
     */
    public function bulkLockUsers(BulkActionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ids = $validated['ids'];
        $action = $validated['action'];

        $isLock = $action === 'lock';

        return $this->handleBulkOperation(
            $ids,
            function ($id) use ($isLock) {
                $user = User::findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['is_locked' => $isLock]);
            },
            $isLock ? '%d users locked successfully.' : '%d users unlocked successfully.',
            'Bulk lock/unlock users'
        );
    }
}
