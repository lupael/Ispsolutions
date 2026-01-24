<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use App\Services\RrdGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphController extends Controller
{
    public function __construct(
        private readonly RrdGraphService $rrdService
    ) {
    }
    
    /**
     * Get hourly bandwidth graph for a customer
     */
    public function hourly(Request $request, int $id): JsonResponse
    {
        return $this->getGraph($id, 'hourly');
    }
    
    /**
     * Get daily bandwidth graph for a customer
     */
    public function daily(Request $request, int $id): JsonResponse
    {
        return $this->getGraph($id, 'daily');
    }
    
    /**
     * Get weekly bandwidth graph for a customer
     */
    public function weekly(Request $request, int $id): JsonResponse
    {
        return $this->getGraph($id, 'weekly');
    }
    
    /**
     * Get monthly bandwidth graph for a customer
     */
    public function monthly(Request $request, int $id): JsonResponse
    {
        return $this->getGraph($id, 'monthly');
    }
    
    /**
     * Get graph for customer by timeframe
     */
    private function getGraph(int $customerId, string $timeframe): JsonResponse
    {
        try {
            // Verify customer exists and user has access
            $customer = NetworkUser::findOrFail($customerId);
            
            // Check tenant isolation
            if ($customer->tenant_id !== auth()->user()->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to customer data',
                ], 403);
            }
            
            // Generate graph
            $graphData = $this->rrdService->getCustomerGraph($customerId, $timeframe);
            
            if ($graphData === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate graph',
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'customer_id' => $customerId,
                    'timeframe' => $timeframe,
                    'graph' => $graphData,
                    'format' => 'base64_png',
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to generate bandwidth graph', [
                'customer_id' => $customerId,
                'timeframe' => $timeframe,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the graph',
            ], 500);
        }
    }
}
