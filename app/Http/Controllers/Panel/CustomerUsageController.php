<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RadAcct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerUsageController extends Controller
{
    /**
     * Check real-time usage (AJAX endpoint)
     */
    public function checkUsage(User $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        try {
            $username = $customer->username ?? $customer->email;

            // Get active session
            $activeSession = RadAcct::where('username', $username)
                ->whereNull('acctstoptime')
                ->orderBy('acctstarttime', 'desc')
                ->first();

            if (!$activeSession) {
                return response()->json([
                    'success' => true,
                    'online' => false,
                    'message' => 'Customer is currently offline',
                ]);
            }

            // Calculate session duration
            $sessionStart = $activeSession->acctstarttime;
            $duration = now()->diffInSeconds($sessionStart);

            // Format data
            $downloadMB = round($activeSession->acctinputoctets / 1048576, 2);
            $uploadMB = round($activeSession->acctoutputoctets / 1048576, 2);
            $totalMB = $downloadMB + $uploadMB;

            return response()->json([
                'success' => true,
                'online' => true,
                'session' => [
                    'session_id' => $activeSession->acctsessionid,
                    'start_time' => $sessionStart->format('Y-m-d H:i:s'),
                    'duration_seconds' => $duration,
                    'duration_formatted' => $this->formatDuration($duration),
                    'ip_address' => $activeSession->framedipaddress,
                    'nas_ip' => $activeSession->nasipaddress,
                    'download_mb' => $downloadMB,
                    'upload_mb' => $uploadMB,
                    'total_mb' => $totalMB,
                    'download_formatted' => $this->formatBytes($activeSession->acctinputoctets),
                    'upload_formatted' => $this->formatBytes($activeSession->acctoutputoctets),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check usage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format duration in human readable format
     */
    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
