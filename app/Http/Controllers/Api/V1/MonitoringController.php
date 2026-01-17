<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\MonitoringServiceInterface;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly MonitoringServiceInterface $monitoringService
    ) {}

    /**
     * Get status of all monitored devices
     */
    public function getAllStatuses(): JsonResponse
    {
        try {
            $statuses = $this->monitoringService->getAllDeviceStatuses();
            return response()->json($statuses);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch device statuses',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status of a specific device
     */
    public function getDeviceStatus(Request $request, string $type, int $id): JsonResponse
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:router,olt,onu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid device type',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = $this->monitoringService->getDeviceStatus($type, $id);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch device status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Monitor a specific device
     */
    public function monitorDevice(Request $request, string $type, int $id): JsonResponse
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:router,olt,onu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid device type',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $metrics = $this->monitoringService->monitorDevice($type, $id);
            return response()->json([
                'message' => 'Device monitored successfully',
                'metrics' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to monitor device',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record bandwidth usage for a device
     */
    public function recordBandwidth(Request $request, string $type, int $id): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['type' => $type]), [
            'type' => 'required|in:router,olt,onu',
            'upload_bytes' => 'required|integer|min:0',
            'download_bytes' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $success = $this->monitoringService->recordBandwidthUsage(
                $type,
                $id,
                $request->input('upload_bytes'),
                $request->input('download_bytes')
            );

            if ($success) {
                return response()->json([
                    'message' => 'Bandwidth usage recorded successfully',
                ]);
            }

            return response()->json([
                'error' => 'Failed to record bandwidth usage',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to record bandwidth usage',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bandwidth usage for a device
     */
    public function getBandwidthUsage(Request $request, string $type, int $id): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['type' => $type]), [
            'type' => 'required|in:router,olt,onu',
            'period' => 'required|in:raw,hourly,daily,weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startDate = $request->has('start_date') 
                ? Carbon::parse($request->input('start_date'))
                : null;

            $endDate = $request->has('end_date')
                ? Carbon::parse($request->input('end_date'))
                : null;

            $usage = $this->monitoringService->getBandwidthUsage(
                $type,
                $id,
                $request->input('period'),
                $startDate,
                $endDate
            );

            return response()->json($usage);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch bandwidth usage',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bandwidth graph data for a device
     */
    public function getBandwidthGraph(Request $request, string $type, int $id): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['type' => $type]), [
            'type' => 'required|in:router,olt,onu',
            'period' => 'required|in:hourly,daily,weekly,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $graphData = $this->monitoringService->getBandwidthGraph(
                $type,
                $id,
                $request->input('period')
            );

            return response()->json($graphData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate bandwidth graph',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
