<?php

namespace App\Services;

use App\Models\MikrotikVpnAccount;
use App\Models\VpnPool;
use App\Models\MikrotikRouter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VpnManagementService
{
    /**
     * Get VPN dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $totalAccounts = MikrotikVpnAccount::where('tenant_id', $tenantId)->count();
        $activeAccounts = MikrotikVpnAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
        $disabledAccounts = $totalAccounts - $activeAccounts;

        $pools = VpnPool::where('tenant_id', $tenantId)->get();
        $totalPools = $pools->count();

        // Protocol distribution
        $protocolDistribution = MikrotikVpnAccount::where('tenant_id', $tenantId)
            ->select('protocol', DB::raw('count(*) as count'))
            ->groupBy('protocol')
            ->get()
            ->pluck('count', 'protocol')
            ->toArray();

        // Get usage stats for today
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayUsage = $this->getUsageStats($todayStart, $todayEnd);

        return [
            'accounts' => [
                'total' => $totalAccounts,
                'active' => $activeAccounts,
                'disabled' => $disabledAccounts,
            ],
            'pools' => [
                'total' => $totalPools,
                'protocols' => $protocolDistribution,
            ],
            'today_usage' => $todayUsage,
        ];
    }

    /**
     * Get VPN usage statistics
     */
    public function getUsageStats(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Get all VPN accounts
        $accounts = MikrotikVpnAccount::where('tenant_id', $tenantId)->get();

        $totalSessions = 0;
        $totalUpload = 0;
        $totalDownload = 0;
        $totalDuration = 0;

        foreach ($accounts as $account) {
            // Get session data from accounting or monitoring
            // This would integrate with your existing radius/monitoring system
            $sessionData = $this->getAccountSessionData($account, $startDate, $endDate);
            
            $totalSessions += $sessionData['session_count'];
            $totalUpload += $sessionData['upload_bytes'];
            $totalDownload += $sessionData['download_bytes'];
            $totalDuration += $sessionData['duration_seconds'];
        }

        return [
            'total_sessions' => $totalSessions,
            'total_upload_gb' => round($totalUpload / 1024 / 1024 / 1024, 2),
            'total_download_gb' => round($totalDownload / 1024 / 1024 / 1024, 2),
            'total_traffic_gb' => round(($totalUpload + $totalDownload) / 1024 / 1024 / 1024, 2),
            'total_duration_hours' => round($totalDuration / 3600, 2),
            'average_session_duration_minutes' => $totalSessions > 0 ? round($totalDuration / $totalSessions / 60, 2) : 0,
        ];
    }

    /**
     * Generate VPN usage report
     */
    public function generateUsageReport(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        $accounts = MikrotikVpnAccount::where('tenant_id', $tenantId)
            ->with(['pool', 'user'])
            ->get();

        $accountReports = [];

        foreach ($accounts as $account) {
            $sessionData = $this->getAccountSessionData($account, $startDate, $endDate);
            
            $accountReports[] = [
                'username' => $account->username,
                'protocol' => $account->protocol,
                'pool_name' => $account->pool ? $account->pool->name : 'N/A',
                'user_name' => $account->user ? $account->user->name : 'N/A',
                'is_active' => $account->is_active,
                'sessions' => $sessionData['session_count'],
                'upload_gb' => round($sessionData['upload_bytes'] / 1024 / 1024 / 1024, 2),
                'download_gb' => round($sessionData['download_bytes'] / 1024 / 1024 / 1024, 2),
                'total_traffic_gb' => round(($sessionData['upload_bytes'] + $sessionData['download_bytes']) / 1024 / 1024 / 1024, 2),
                'duration_hours' => round($sessionData['duration_seconds'] / 3600, 2),
                'last_connection' => $sessionData['last_connection'],
            ];
        }

        // Sort by total traffic descending
        usort($accountReports, function ($a, $b) {
            return $b['total_traffic_gb'] <=> $a['total_traffic_gb'];
        });

        $totalStats = $this->getUsageStats($startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => $totalStats,
            'accounts' => $accountReports,
            'top_users' => array_slice($accountReports, 0, 10),
        ];
    }

    /**
     * Generate protocol performance report
     */
    public function generateProtocolReport(Carbon $startDate, Carbon $endDate): array
    {
        $tenantId = auth()->user()->tenant_id;

        $protocols = ['pptp', 'l2tp', 'sstp', 'openvpn', 'wireguard'];
        $protocolStats = [];

        foreach ($protocols as $protocol) {
            $accounts = MikrotikVpnAccount::where('tenant_id', $tenantId)
                ->where('protocol', $protocol)
                ->get();

            if ($accounts->isEmpty()) {
                continue;
            }

            $totalSessions = 0;
            $totalTraffic = 0;
            $totalDuration = 0;

            foreach ($accounts as $account) {
                $sessionData = $this->getAccountSessionData($account, $startDate, $endDate);
                $totalSessions += $sessionData['session_count'];
                $totalTraffic += $sessionData['upload_bytes'] + $sessionData['download_bytes'];
                $totalDuration += $sessionData['duration_seconds'];
            }

            $protocolStats[] = [
                'protocol' => strtoupper($protocol),
                'account_count' => $accounts->count(),
                'active_count' => $accounts->where('is_active', true)->count(),
                'total_sessions' => $totalSessions,
                'total_traffic_gb' => round($totalTraffic / 1024 / 1024 / 1024, 2),
                'total_duration_hours' => round($totalDuration / 3600, 2),
                'avg_traffic_per_account_gb' => $accounts->count() > 0 ? round($totalTraffic / $accounts->count() / 1024 / 1024 / 1024, 2) : 0,
            ];
        }

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'protocols' => $protocolStats,
        ];
    }

    /**
     * Get connection history
     */
    public function getConnectionHistory(int $accountId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $account = MikrotikVpnAccount::findOrFail($accountId);

        // This would query your radius accounting or monitoring tables
        // For now, return sample structure
        $sessions = [];

        return [
            'account' => [
                'id' => $account->id,
                'username' => $account->username,
                'protocol' => $account->protocol,
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'sessions' => $sessions,
            'total_sessions' => count($sessions),
        ];
    }

    /**
     * Get session data for an account (integrate with monitoring/radius)
     */
    private function getAccountSessionData(MikrotikVpnAccount $account, Carbon $startDate, Carbon $endDate): array
    {
        // This would query your radius_sessions or monitoring tables
        // For now, return sample data structure
        
        // In production, this would be something like:
        // $sessions = DB::table('radius_sessions')
        //     ->where('username', $account->username)
        //     ->whereBetween('start_time', [$startDate, $endDate])
        //     ->get();

        return [
            'session_count' => 0,
            'upload_bytes' => 0,
            'download_bytes' => 0,
            'duration_seconds' => 0,
            'last_connection' => null,
        ];
    }

    /**
     * Monitor VPN server health
     */
    public function monitorServerHealth(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $routers = MikrotikRouter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $serverHealth = [];

        foreach ($routers as $router) {
            $vpnAccounts = MikrotikVpnAccount::where('router_id', $router->id)->count();
            $activeVpnAccounts = MikrotikVpnAccount::where('router_id', $router->id)
                ->where('is_active', true)
                ->count();

            $serverHealth[] = [
                'router_id' => $router->id,
                'router_name' => $router->name,
                'router_ip' => $router->host,
                'is_online' => $router->is_online ?? true,
                'total_vpn_accounts' => $vpnAccounts,
                'active_vpn_accounts' => $activeVpnAccounts,
                'load_percentage' => $activeVpnAccounts > 0 && $vpnAccounts > 0 
                    ? round(($activeVpnAccounts / $vpnAccounts) * 100, 2) 
                    : 0,
            ];
        }

        return [
            'servers' => $serverHealth,
            'total_servers' => count($serverHealth),
            'online_servers' => collect($serverHealth)->where('is_online', true)->count(),
        ];
    }

    /**
     * Get bandwidth usage alerts
     */
    public function getBandwidthAlerts(float $thresholdGb = 10): array
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = now()->startOfDay();
        $endDate = now();

        $accounts = MikrotikVpnAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $alerts = [];

        foreach ($accounts as $account) {
            $sessionData = $this->getAccountSessionData($account, $startDate, $endDate);
            $trafficGb = ($sessionData['upload_bytes'] + $sessionData['download_bytes']) / 1024 / 1024 / 1024;

            if ($trafficGb >= $thresholdGb) {
                $alerts[] = [
                    'account_id' => $account->id,
                    'username' => $account->username,
                    'protocol' => $account->protocol,
                    'traffic_gb' => round($trafficGb, 2),
                    'threshold_gb' => $thresholdGb,
                    'exceeded_by_gb' => round($trafficGb - $thresholdGb, 2),
                ];
            }
        }

        return [
            'threshold_gb' => $thresholdGb,
            'alert_count' => count($alerts),
            'alerts' => $alerts,
        ];
    }
}
