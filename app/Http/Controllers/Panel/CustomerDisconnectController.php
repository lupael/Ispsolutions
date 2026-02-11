<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RadAcct;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerDisconnectController extends Controller
{
    protected MikrotikService $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Disconnect customer's active session.
     */
    public function disconnect(Request $request, $id): JsonResponse
    {
        $customer = User::findOrFail($id);
        $this->authorize('disconnect', $customer);

        try {
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'No network user found',
                ], 404);
            }

            $disconnected = false;

            switch ($customer->service_type) {
                case 'pppoe':
                    $disconnected = $this->disconnectPppoe($customer);
                    break;
                case 'hotspot':
                    $disconnected = $this->disconnectHotspot($customer);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported service type',
                    ], 400);
            }

            if ($disconnected) {
                // Log action
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'customer_disconnected',
                    'model' => 'User',
                    'model_id' => $customer->id,
                    'details' => "Customer {$customer->username} disconnected from network",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Customer disconnected successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No active sessions found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to disconnect customer', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect PPPoE user.
     */
    protected function disconnectPppoe(User $customer): bool
    {
        // Find active sessions
        $activeSessions = RadAcct::where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->get();

        if ($activeSessions->isEmpty()) {
            return false;
        }

        $disconnected = false;
        foreach ($activeSessions as $session) {
            // Try to disconnect via MikroTik API
            if ($session->nasipaddress) {
                try {
                    $router = \App\Models\MikrotikRouter::where('ip_address', $session->nasipaddress)->first();
                    if ($router) {
                        $this->mikrotikService->connect(
                            $router->ip_address,
                            $router->username,
                            $router->password,
                            $router->port ?? 8728
                        );

                        // Find and remove PPP session
                        $pppSessions = $this->mikrotikService->query('/ppp/active/print', [
                            '?name' => $customer->username,
                        ]);

                        foreach ($pppSessions as $pppSession) {
                            $this->mikrotikService->query('/ppp/active/remove', [
                                '.id' => $pppSession['.id'],
                            ]);
                            $disconnected = true;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to disconnect PPPoE user via API', [
                        'username' => $customer->username,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $disconnected;
    }

    /**
     * Disconnect hotspot user.
     */
    protected function disconnectHotspot(NetworkUser $networkUser): bool
    {
        // Find active hotspot sessions
        $activeSessions = RadAcct::where('username', $networkUser->username)
            ->whereNull('acctstoptime')
            ->get();

        if ($activeSessions->isEmpty()) {
            return false;
        }

        $disconnected = false;
        foreach ($activeSessions as $session) {
            if ($session->nasipaddress) {
                try {
                    $router = \App\Models\MikrotikRouter::where('ip_address', $session->nasipaddress)->first();
                    if ($router) {
                        $this->mikrotikService->connect(
                            $router->ip_address,
                            $router->username,
                            $router->password,
                            $router->port ?? 8728
                        );

                        // Remove hotspot active session
                        $hotspotSessions = $this->mikrotikService->query('/ip/hotspot/active/print', [
                            '?user' => $networkUser->username,
                        ]);

                        foreach ($hotspotSessions as $hotspotSession) {
                            $this->mikrotikService->query('/ip/hotspot/active/remove', [
                                '.id' => $hotspotSession['.id'],
                            ]);
                            $disconnected = true;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to disconnect hotspot user', [
                        'username' => $networkUser->username,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $disconnected;
    }
}
