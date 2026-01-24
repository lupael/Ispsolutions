<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WidgetCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    protected WidgetCacheService $widgetCacheService;

    public function __construct(WidgetCacheService $widgetCacheService)
    {
        $this->widgetCacheService = $widgetCacheService;
    }

    /**
     * Refresh widget data.
     * POST /api/v1/widgets/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'widgets' => 'nullable|array',
            'widgets.*' => 'string|in:suspension_forecast,collection_target,sms_usage',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $widgetsToRefresh = $request->input('widgets', []);

        // If no specific widgets requested, refresh all
        if (empty($widgetsToRefresh)) {
            $this->widgetCacheService->refreshAllWidgets($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'All widgets refreshed successfully',
                'data' => [
                    'suspension_forecast' => $this->widgetCacheService->getSuspensionForecast($tenantId),
                    'collection_target' => $this->widgetCacheService->getCollectionTarget($tenantId),
                    'sms_usage' => $this->widgetCacheService->getSmsUsage($tenantId),
                ],
            ]);
        }

        // Refresh specific widgets
        $refreshedData = [];
        foreach ($widgetsToRefresh as $widget) {
            $data = $this->widgetCacheService->refreshWidget($tenantId, $widget);
            if ($data !== null) {
                $refreshedData[$widget] = $data;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Widgets refreshed successfully',
            'data' => $refreshedData,
        ]);
    }

    /**
     * Get suspension forecast widget data.
     * GET /api/v1/widgets/suspension-forecast
     */
    public function suspensionForecast(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $refresh = $request->boolean('refresh', false);

        $data = $this->widgetCacheService->getSuspensionForecast($tenantId, $refresh);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get collection target widget data.
     * GET /api/v1/widgets/collection-target
     */
    public function collectionTarget(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $refresh = $request->boolean('refresh', false);

        $data = $this->widgetCacheService->getCollectionTarget($tenantId, $refresh);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get SMS usage widget data.
     * GET /api/v1/widgets/sms-usage
     */
    public function smsUsage(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $refresh = $request->boolean('refresh', false);

        $data = $this->widgetCacheService->getSmsUsage($tenantId, $refresh);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
