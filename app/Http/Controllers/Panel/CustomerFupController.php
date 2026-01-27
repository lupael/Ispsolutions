<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\PackageFup;
use App\Models\RadAcct;
use App\Models\RadReply;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CustomerFupController extends Controller
{
    /**
     * Display FUP status for a customer.
     */
    public function show(User $customer): View
    {
        $this->authorize('activateFup', $customer);

        $networkUser = NetworkUser::with('package.fup')->where('user_id', $customer->id)->first();
        
        // Get current data usage for the billing cycle
        $usage = $this->getCurrentUsage($networkUser);
        
        // Get FUP configuration from package
        $fupConfig = null;
        $fupActive = false;
        $fupSpeed = null;
        
        if ($networkUser && $networkUser->package && $networkUser->package->fup) {
            $fupConfig = $networkUser->package->fup;
            
            // Check if FUP is currently active (speed is reduced)
            $radReply = RadReply::where('username', $networkUser->username)
                ->where('attribute', 'Mikrotik-Rate-Limit')
                ->first();
            
            if ($radReply && $fupConfig) {
                $fupRateLimit = $fupConfig->reduced_speed;
                
                if ($radReply->value === $fupRateLimit) {
                    $fupActive = true;
                    $fupSpeed = $fupRateLimit;
                }
            }
        }

        return view('panel.customers.fup.show', compact(
            'customer',
            'networkUser',
            'usage',
            'fupConfig',
            'fupActive',
            'fupSpeed'
        ));
    }

    /**
     * Activate FUP (reduce speed) for a customer.
     */
    public function activate(
        User $customer,
        MikrotikService $mikrotikService,
        AuditLogService $auditLogService
    ): JsonResponse {
        $this->authorize('activateFup', $customer);

        DB::beginTransaction();
        try {
            $networkUser = NetworkUser::with('package.fup')->where('user_id', $customer->id)->firstOrFail();

            // Check if package has FUP configured
            if (!$networkUser->package || !$networkUser->package->fup) {
                return response()->json([
                    'success' => false,
                    'message' => 'FUP is not configured for this customer\'s package.',
                ], 400);
            }

            $fupConfig = $networkUser->package->fup;

            // Get current usage
            $usage = $this->getCurrentUsage($networkUser);

            // Check if threshold is exceeded (convert bytes to MB)
            $thresholdMB = $fupConfig->data_limit_bytes / (1024 * 1024);
            if ($usage['total_mb'] < $thresholdMB) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'FUP threshold not exceeded. Current usage: %.2f GB, Threshold: %.2f GB',
                        $usage['total_mb'] / 1024,
                        $thresholdMB / 1024
                    ),
                ], 400);
            }

            // Apply reduced speed
            $rateLimit = $fupConfig->reduced_speed;

            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Disconnect customer to apply new speed
            try {
                $router = MikrotikRouter::where('is_active', true)->first();
                
                if ($router && $mikrotikService->connectRouter($router->id)) {
                    $sessions = $mikrotikService->getActiveSessions($router->id);
                    
                    foreach ($sessions as $session) {
                        if (isset($session['name']) && $session['name'] === $networkUser->username) {
                            $mikrotikService->disconnectSession($session['id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect customer for FUP activation', [
                    'username' => $networkUser->username,
                    'error' => $e->getMessage(),
                ]);
            }

            // Audit logging
            $auditLogService->log(
                'fup_activated',
                $networkUser,
                [],
                [
                    'reduced_speed' => $fupConfig->reduced_speed,
                    'usage_mb' => $usage['total_mb'],
                    'threshold_mb' => $thresholdMB,
                ],
                []
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FUP activated successfully. Speed has been reduced.',
                'fup_speed' => $fupConfig->reduced_speed,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate FUP', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate FUP. Please try again.',
            ], 500);
        }
    }

    /**
     * Deactivate FUP (restore normal speed) for a customer.
     */
    public function deactivate(
        User $customer,
        MikrotikService $mikrotikService,
        AuditLogService $auditLogService
    ): JsonResponse {
        $this->authorize('activateFup', $customer);

        DB::beginTransaction();
        try {
            $networkUser = NetworkUser::with('package')->where('user_id', $customer->id)->firstOrFail();

            if (!$networkUser->package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer has no package assigned.',
                ], 400);
            }

            // Restore package default speed
            $rateLimit = sprintf(
                '%dk/%dk',
                $networkUser->package->bandwidth_upload,
                $networkUser->package->bandwidth_download
            );

            RadReply::updateOrCreate(
                ['username' => $networkUser->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Disconnect customer to apply new speed
            try {
                $router = MikrotikRouter::where('is_active', true)->first();
                
                if ($router && $mikrotikService->connectRouter($router->id)) {
                    $sessions = $mikrotikService->getActiveSessions($router->id);
                    
                    foreach ($sessions as $session) {
                        if (isset($session['name']) && $session['name'] === $networkUser->username) {
                            $mikrotikService->disconnectSession($session['id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect customer for FUP deactivation', [
                    'username' => $networkUser->username,
                    'error' => $e->getMessage(),
                ]);
            }

            // Audit logging
            $auditLogService->log(
                'fup_deactivated',
                $networkUser,
                [],
                [
                    'upload_speed' => $networkUser->package->bandwidth_upload,
                    'download_speed' => $networkUser->package->bandwidth_download,
                ],
                []
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FUP deactivated successfully. Normal speed restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate FUP', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate FUP. Please try again.',
            ], 500);
        }
    }

    /**
     * Reset FUP (clear usage counter).
     */
    public function reset(
        User $customer,
        AuditLogService $auditLogService
    ): JsonResponse {
        $this->authorize('activateFup', $customer);

        DB::beginTransaction();
        try {
            $networkUser = NetworkUser::with('package.fup')->where('user_id', $customer->id)->firstOrFail();

            if (!$networkUser->package || !$networkUser->package->fup) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'FUP is not configured for this customer\'s package.',
                ], 400);
            }

            $fupConfig = $networkUser->package->fup;
            
            // Calculate the reset period start date
            $startDate = match($fupConfig->reset_period) {
                'daily' => now()->startOfDay(),
                'weekly' => now()->startOfWeek(),
                'monthly' => now()->startOfMonth(),
                default => now()->subDays(30)
            };

            // Delete accounting records for the current period to reset usage
            $deletedCount = RadAcct::where('username', $networkUser->username)
                ->where('acctstarttime', '>=', $startDate)
                ->delete();

            Log::info('FUP usage counter reset', [
                'username' => $networkUser->username,
                'records_deleted' => $deletedCount,
                'reset_period' => $fupConfig->reset_period,
                'start_date' => $startDate->toDateTimeString(),
            ]);

            // Audit logging
            $auditLogService->log(
                'fup_reset',
                $networkUser,
                [],
                [
                    'records_deleted' => $deletedCount,
                    'reset_period' => $fupConfig->reset_period,
                ],
                []
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FUP usage counter reset successfully.',
                'records_deleted' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reset FUP', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset FUP. Please try again.',
            ], 500);
        }
    }

    /**
     * Get current data usage for a customer.
     */
    protected function getCurrentUsage(?NetworkUser $networkUser): array
    {
        if (!$networkUser || !$networkUser->username) {
            return [
                'total_mb' => 0,
                'upload_mb' => 0,
                'download_mb' => 0,
                'sessions' => 0,
            ];
        }

        try {
            // Get FUP configuration to determine reset period
            $fupConfig = $networkUser->package?->fup;
            
            // Calculate start date based on FUP reset period
            $startDate = $fupConfig && $fupConfig->reset_period 
                ? match($fupConfig->reset_period) {
                    'daily' => now()->startOfDay(),
                    'weekly' => now()->startOfWeek(),
                    'monthly' => now()->startOfMonth(),
                    default => now()->subDays(30)
                }
                : now()->subDays(30);

            $usage = RadAcct::where('username', $networkUser->username)
                ->where('acctstarttime', '>=', $startDate)
                ->selectRaw('
                    SUM(acctinputoctets) as total_upload,
                    SUM(acctoutputoctets) as total_download,
                    COUNT(*) as session_count
                ')
                ->first();

            $uploadMb = $usage && $usage->total_upload ? $usage->total_upload / (1024 * 1024) : 0;
            $downloadMb = $usage && $usage->total_download ? $usage->total_download / (1024 * 1024) : 0;

            return [
                'total_mb' => $uploadMb + $downloadMb,
                'upload_mb' => $uploadMb,
                'download_mb' => $downloadMb,
                'sessions' => $usage ? $usage->session_count : 0,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get current usage', [
                'username' => $networkUser->username,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_mb' => 0,
                'upload_mb' => 0,
                'download_mb' => 0,
                'sessions' => 0,
            ];
        }
    }
}
