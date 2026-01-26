<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RouterController extends Controller
{
    /**
     * Test router connection
     */
    public function testConnection(MikrotikRouter $router): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Test connectivity using the router's testConnectivity method
            $connected = $router->testConnectivity();
            
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($connected) {
                return response()->json([
                    'success' => true,
                    'latency' => $latency,
                    'message' => 'Connection successful'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to router'
            ], 503);
            
        } catch (\Exception $e) {
            Log::error('Router connection test failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reconnect to router
     */
    public function reconnect(MikrotikRouter $router): JsonResponse
    {
        try {
            // Disconnect first
            $router->disconnect();
            
            // Brief delay to allow proper disconnection (reduced from 2s)
            usleep(200000); // 200 ms instead of a full 2-second block
            
            // Attempt to reconnect
            $connected = $router->connect();
            
            if ($connected) {
                // Refresh stats after reconnecting
                $router->refreshStats();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Router reconnected successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reconnect to router'
            ], 503);
            
        } catch (\Exception $e) {
            Log::error('Router reconnection failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
