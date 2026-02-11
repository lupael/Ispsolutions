<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
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

        $customer->load('servicePackage.fup');
        
        // Get current data usage for the billing cycle
        $usage = $this->getCurrentUsage($customer);
        
        // Get FUP configuration from package
        $fupConfig = null;
        $fupActive = false;
        $fupSpeed = null;
        
        if ($customer && $customer->servicePackage && $customer->servicePackage->fup) {
            $fupConfig = $customer->servicePackage->fup;
            
            // Check if FUP is currently active (speed is reduced)
            $radReply = RadReply::where('username', $customer->username)
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
            $customer->load('servicePackage.fup');

            // Check if package has FUP configured
            if (!$customer->servicePackage || !$customer->servicePackage->fup) {
                return response()->json([
                    'success' => false,
                    'message' => 'FUP is not configured for this customer\'s package.',
                ], 400);
            }

            $fupConfig = $customer->servicePackage->fup;

            // Get current usage
            $usage = $this->getCurrentUsage($customer);

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
                ['username' => $customer->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Disconnect customer to apply new speed
            try {
                $router = MikrotikRouter::where('is_active', true)->first();
                
                if ($router && $mikrotikService->connectRouter($router->id)) {
                    $sessions = $mikrotikService->getActiveSessions($router->id);
                    
                    foreach ($sessions as $session) {
                        if (isset($session['name']) && $session['name'] === $customer->username) {
                            $mikrotikService->disconnectSession($session['id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect customer for FUP activation', [
                    'username' => $customer->username,
                    'error' => $e->getMessage(),
                ]);
            }

            // Audit logging
            $auditLogService->log(
                'fup_activated',
                $customer,
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
            $customer->load('servicePackage');

            if (!$customer->servicePackage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer has no package assigned.',
                ], 400);
            }

            // Restore package default speed
            $rateLimit = sprintf(
                '%dk/%dk',
                $customer->servicePackage->bandwidth_upload,
                $customer->servicePackage->bandwidth_download
            );

            RadReply::updateOrCreate(
                ['username' => $customer->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $rateLimit]
            );

            // Disconnect customer to apply new speed
            try {
                $router = MikrotikRouter::where('is_active', true)->first();
                
                if ($router && $mikrotikService->connectRouter($router->id)) {
                    $sessions = $mikrotikService->getActiveSessions($router->id);
                    
                    foreach ($sessions as $session) {
                        if (isset($session['name']) && $session['name'] === $customer->username) {
                            $mikrotikService->disconnectSession($session['id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to disconnect customer for FUP deactivation', [
                    'username' => $customer->username,
                    'error' => $e->getMessage(),
                ]);
            }

            // Audit logging
            $auditLogService->log(
                'fup_deactivated',
                $customer,
                [],
                [
                    'upload_speed' => $customer->servicePackage->bandwidth_upload,
                    'download_speed' => $customer->servicePackage->bandwidth_download,
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
            $customer->load('servicePackage.fup');

            if (!$customer->servicePackage || !$customer->servicePackage->fup) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'FUP is not configured for this customer\'s package.',
                ], 400);
            }

            $fupConfig = $customer->servicePackage->fup;
            
            // Calculate the reset period start date
            $startDate = match($fupConfig->reset_period) {
                'daily' => now()->startOfDay(),
                'weekly' => now()->startOfWeek(),
                'monthly' => now()->startOfMonth(),
                default => now()->subDays(30)
            };

            // Delete accounting records for the current period to reset usage
            $deletedCount = RadAcct::where('username', $customer->username)
                ->where('acctstarttime', '>=', $startDate)
                ->delete();

            Log::info('FUP usage counter reset', [
                'username' => $customer->username,
                'records_deleted' => $deletedCount,
                'reset_period' => $fupConfig->reset_period,
                'start_date' => $startDate->toDateTimeString(),
            ]);

            // Audit logging
            $auditLogService->log(
                'fup_reset',
                $customer,
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
    protected function getCurrentUsage(?User $customer): array
    {
        if (!$customer || !$customer->username) {
            return [
                'total_mb' => 0,
                'upload_mb' => 0,
                'download_mb' => 0,
                'sessions' => 0,
            ];
        }

        try {
            // Get FUP configuration to determine reset period
            $fupConfig = $customer->servicePackage?->fup;
            
            // Calculate start date based on FUP reset period
            $startDate = $fupConfig && $fupConfig->reset_period 
                ? match($fupConfig->reset_period) {
                    'daily' => now()->startOfDay(),
                    'weekly' => now()->startOfWeek(),
                    'monthly' => now()->startOfMonth(),
                    default => now()->subDays(30)
                }
                : now()->subDays(30);

            $usage = RadAcct::where('username', $customer->username)
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
                'username' => $customer->username,
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
