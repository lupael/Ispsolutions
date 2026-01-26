<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BulkCustomerController extends Controller
{
    /**
     * Execute bulk action on selected customers
     */
    public function executeBulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'required|integer|exists:network_users,id',
            'action' => 'required|string|in:change_package,change_operator,suspend,activate,update_expiry',
            'package_id' => 'required_if:action,change_package|exists:packages,id',
            'operator_id' => 'required_if:action,change_operator|exists:users,id',
            'expiry_date' => 'required_if:action,update_expiry|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customerIds = $request->input('customer_ids');
        $action = $request->input('action');
        
        // Authorization: Check if user can update these customers
        $tenantId = auth()->user()->tenant_id;
        $customers = NetworkUser::whereIn('id', $customerIds)
            ->where('tenant_id', $tenantId)
            ->get();

        if ($customers->count() !== count($customerIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some customers not found or you do not have permission to update them'
            ], 403);
        }

        // Per-customer authorization check
        foreach ($customers as $customer) {
            if (!auth()->user()->can('update', $customer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update all selected customers'
                ], 403);
            }
        }

        // Validate operator is in same tenant if changing operator
        if ($action === 'change_operator') {
            $operatorId = $request->input('operator_id');
            $operator = User::where('id', $operatorId)
                ->where('tenant_id', $tenantId)
                ->first();
            
            if (!$operator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid operator or operator not in your tenant'
                ], 403);
            }
        }

        // Execute the bulk action
        try {
            DB::beginTransaction();

            $result = match ($action) {
                'change_package' => $this->changePackage($customers, $request->input('package_id')),
                'change_operator' => $this->changeOperator($customers, $request->input('operator_id')),
                'suspend' => $this->suspendCustomers($customers),
                'activate' => $this->activateCustomers($customers),
                'update_expiry' => $this->updateExpiry($customers, $request->input('expiry_date')),
                default => ['success' => false, 'message' => 'Invalid action']
            };

            if ($result['success']) {
                DB::commit();
                
                // Log the bulk action
                Log::info('Bulk customer action executed', [
                    'user_id' => auth()->id(),
                    'action' => $action,
                    'customer_count' => count($customerIds),
                    'customer_ids' => $customerIds
                ]);
            } else {
                DB::rollBack();
            }

            return response()->json($result);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk customer action failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'customer_ids' => $customerIds
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while executing the bulk action.'
            ], 500);
        }
    }

    /**
     * Change package for multiple customers
     */
    protected function changePackage($customers, int $packageId): array
    {
        $package = Package::find($packageId);
        if (!$package) {
            return ['success' => false, 'message' => 'Package not found'];
        }

        $this->authorize('view', $package);

        $updatedCount = 0;
        foreach ($customers as $customer) {
            $customer->package_id = $packageId;
            if ($customer->save()) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully updated package for {$updatedCount} customer(s) to {$package->name}",
            'updated_count' => $updatedCount
        ];
    }

    /**
     * Change operator for multiple customers
     */
    protected function changeOperator($customers, int $operatorId): array
    {
        $operator = User::find($operatorId);
        if (!$operator) {
            return ['success' => false, 'message' => 'Operator not found'];
        }

        $updatedCount = 0;
        foreach ($customers as $customer) {
            $customer->user_id = $operatorId;
            if ($customer->save()) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully reassigned {$updatedCount} customer(s) to operator {$operator->name}",
            'updated_count' => $updatedCount
        ];
    }

    /**
     * Suspend multiple customers
     */
    protected function suspendCustomers($customers): array
    {
        $updatedCount = 0;
        foreach ($customers as $customer) {
            $customer->status = 'suspended';
            $customer->is_active = false;
            if ($customer->save()) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully suspended {$updatedCount} customer(s)",
            'updated_count' => $updatedCount
        ];
    }

    /**
     * Activate multiple customers
     */
    protected function activateCustomers($customers): array
    {
        $updatedCount = 0;
        foreach ($customers as $customer) {
            $customer->status = 'active';
            $customer->is_active = true;
            if ($customer->save()) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully activated {$updatedCount} customer(s)",
            'updated_count' => $updatedCount
        ];
    }

    /**
     * Update expiry date for multiple customers
     */
    protected function updateExpiry($customers, string $expiryDate): array
    {
        $updatedCount = 0;
        foreach ($customers as $customer) {
            $customer->expiry_date = $expiryDate;
            if ($customer->save()) {
                $updatedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Successfully updated expiry date for {$updatedCount} customer(s)",
            'updated_count' => $updatedCount
        ];
    }
}
