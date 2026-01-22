<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkActionRequest;
use App\Http\Requests\BulkDeleteRequest;
use App\Http\Traits\HandlesFormValidation;
use App\Models\NetworkUser;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkOperationsController extends Controller
{
    use HandlesFormValidation;

    /**
     * Bulk delete network users.
     */
    public function bulkDeleteNetworkUsers(BulkDeleteRequest $request): RedirectResponse
    {
        return $this->handleBulkOperation(
            $request->validated('ids'),
            function ($id) {
                $user = NetworkUser::findOrFail($id);
                $this->authorize('delete', $user);
                $user->delete();
            },
            '%d network users deleted successfully.',
            'Bulk delete network users'
        );
    }

    /**
     * Bulk action on network users.
     */
    public function bulkActionNetworkUsers(BulkActionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $action = $validated['action'];
        $ids = $validated['ids'];

        return match($action) {
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
                $user = NetworkUser::findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['is_active' => true, 'status' => 'active']);
            },
            '%d users activated successfully.',
            'Bulk activate users'
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
                $user = NetworkUser::findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['is_active' => false, 'status' => 'inactive']);
            },
            '%d users deactivated successfully.',
            'Bulk deactivate users'
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
                $user = NetworkUser::findOrFail($id);
                $this->authorize('update', $user);
                $user->update(['status' => 'suspended']);
            },
            '%d users suspended successfully.',
            'Bulk suspend users'
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
                $user = NetworkUser::findOrFail($id);
                $this->authorize('delete', $user);
                $user->delete();
            },
            '%d users deleted successfully.',
            'Bulk delete users'
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
                        $user = NetworkUser::with('package')->findOrFail($id);
                        
                        if (!$user->package) {
                            $failedCount++;
                            continue;
                        }

                        // Check if user already has a pending invoice
                        $existingInvoice = Invoice::where('user_id', $user->user_id)
                            ->whereIn('status', ['pending', 'overdue'])
                            ->exists();

                        if ($existingInvoice) {
                            $failedCount++;
                            continue;
                        }

                        // Generate invoice
                        $invoice = Invoice::create([
                            'tenant_id' => $user->tenant_id,
                            'user_id' => $user->user_id,
                            'package_id' => $user->package_id,
                            'invoice_number' => $this->generateInvoiceNumber(),
                            'amount' => $user->package->price_monthly,
                            'tax_amount' => $user->package->price_monthly * 0.15, // 15% tax
                            'total_amount' => $user->package->price_monthly * 1.15,
                            'billing_period_start' => now(),
                            'billing_period_end' => now()->addMonth(),
                            'due_date' => now()->addDays(7),
                            'status' => 'pending',
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        Log::error("Failed to generate invoice for user ID {$id}: " . $e->getMessage());
                        $failedCount++;
                    }
                }
            });

            if ($failedCount === 0) {
                return back()->with('success', "{$successCount} invoices generated successfully.");
            } elseif ($successCount === 0) {
                return back()->with('error', "Failed to generate any invoices. Check logs for details.");
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
