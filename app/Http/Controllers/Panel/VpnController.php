<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikVpnAccount;
use App\Models\VpnPool;
use App\Services\VpnManagementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VpnController extends Controller
{
    public function __construct(private VpnManagementService $vpnService) {}

    /**
     * Display VPN dashboard
     */
    public function dashboard(): View
    {
        $stats = $this->vpnService->getDashboardStats();
        $serverHealth = $this->vpnService->monitorServerHealth();

        return view('panels.isp.vpn.dashboard', compact('stats', 'serverHealth'));
    }

    /**
     * Display VPN accounts listing
     */
    public function index(Request $request): View
    {
        $query = MikrotikVpnAccount::with(['pool', 'user', 'router'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('remote_address', 'like', '%' . $search . '%');
            });
        }

        // Filter by protocol
        if ($request->filled('protocol')) {
            $query->where('protocol', $request->protocol);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $accounts = $query->latest()->paginate(20);
        $pools = VpnPool::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('panels.isp.vpn.index', compact('accounts', 'pools'));
    }

    /**
     * Display usage reports
     */
    public function reports(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now();

        $usageReport = $this->vpnService->generateUsageReport($startDate, $endDate);
        $protocolReport = $this->vpnService->generateProtocolReport($startDate, $endDate);

        return view('panels.isp.vpn.reports', compact('usageReport', 'protocolReport', 'startDate', 'endDate'));
    }

    /**
     * Get usage statistics (AJAX)
     */
    public function getUsageStats(Request $request): JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now();

        $stats = $this->vpnService->getUsageStats($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Get bandwidth alerts (AJAX)
     */
    public function getBandwidthAlerts(Request $request): JsonResponse
    {
        $threshold = $request->input('threshold', 10);
        $alerts = $this->vpnService->getBandwidthAlerts($threshold);

        return response()->json($alerts);
    }

    /**
     * Get connection history for an account
     */
    public function connectionHistory(Request $request, int $accountId): JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now();

        $history = $this->vpnService->getConnectionHistory($accountId, $startDate, $endDate);

        return response()->json($history);
    }

    /**
     * Export usage report
     */
    public function exportReport(Request $request)
    {
        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)
                : now()->subDays(30);

            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)
                : now();

            $report = $this->vpnService->generateUsageReport($startDate, $endDate);

            // Return CSV download
            $filename = 'vpn_usage_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($report) {
                try {
                    $file = fopen('php://output', 'w');

                    if ($file === false) {
                        throw new \RuntimeException('Failed to open output stream');
                    }

                    // Headers
                    fputcsv($file, [
                        'Username',
                        'Protocol',
                        'Pool',
                        'User',
                        'Status',
                        'Sessions',
                        'Upload (GB)',
                        'Download (GB)',
                        'Total Traffic (GB)',
                        'Duration (Hours)',
                        'Last Connection',
                    ]);

                    // Data
                    foreach ($report['accounts'] as $account) {
                        fputcsv($file, [
                            $account['username'],
                            $account['protocol'],
                            $account['pool_name'],
                            $account['user_name'],
                            $account['is_active'] ? 'Active' : 'Disabled',
                            $account['sessions'],
                            $account['upload_gb'],
                            $account['download_gb'],
                            $account['total_traffic_gb'],
                            $account['duration_hours'],
                            $account['last_connection'] ?? 'N/A',
                        ]);
                    }

                    fclose($file);
                } catch (\Exception $e) {
                    Log::error('VPN export failed during streaming', [
                        'error' => $e->getMessage(),
                    ]);
                    echo 'Error generating export: ' . $e->getMessage();
                }
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('VPN export failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to generate export: ' . $e->getMessage());
        }
    }
}
