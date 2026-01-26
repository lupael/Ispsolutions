<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RadAcct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerHistoryController extends Controller
{
    /**
     * Show internet history page
     */
    public function internetHistory(User $customer)
    {
        $this->authorize('view', $customer);

        // Validate query parameters
        $validated = request()->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'session_type' => ['nullable', 'string'],
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? now()->format('Y-m-d');
        $sessionType = $validated['session_type'] ?? 'all';

        $query = RadAcct::where('username', $customer->username ?? $customer->email)
            ->whereBetween('acctstarttime', [$startDate, $endDate]);

        if ($sessionType !== 'all') {
            $query->where('nasporttype', $sessionType);
        }

        $sessions = $query->orderBy('acctstarttime', 'desc')->paginate(50);

        // Calculate totals
        $totals = RadAcct::where('username', $customer->username ?? $customer->email)
            ->whereBetween('acctstarttime', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(acctsessiontime) as total_time,
                SUM(acctinputoctets) as total_download,
                SUM(acctoutputoctets) as total_upload
            ')
            ->first();

        return view('panels.admin.customers.history.internet-history', compact(
            'customer',
            'sessions',
            'totals',
            'startDate',
            'endDate',
            'sessionType'
        ));
    }

    /**
     * Export internet history
     */
    public function exportHistory(Request $request, User $customer)
    {
        $this->authorize('view', $customer);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,excel',
        ]);

        $sessions = RadAcct::where('username', $customer->username ?? $customer->email)
            ->whereBetween('acctstarttime', [$validated['start_date'], $validated['end_date']])
            ->orderBy('acctstarttime', 'desc')
            ->get();

        $filename = "internet_history_{$customer->username}_{$validated['start_date']}_to_{$validated['end_date']}";

        if ($validated['format'] === 'csv') {
            return $this->exportToCsv($sessions, $filename);
        }

        // For Excel, you'd use a package like maatwebsite/excel
        return back()->with('error', 'Excel export not yet implemented');
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($sessions, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($sessions) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Session ID',
                'Start Time',
                'Stop Time',
                'Duration (min)',
                'Download (MB)',
                'Upload (MB)',
                'Total (MB)',
                'IP Address',
                'NAS IP',
                'Terminate Cause'
            ]);

            // Data
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->acctsessionid,
                    $session->acctstarttime,
                    $session->acctstoptime,
                    round($session->acctsessiontime / 60, 2),
                    round($session->acctinputoctets / 1048576, 2),
                    round($session->acctoutputoctets / 1048576, 2),
                    round(($session->acctinputoctets + $session->acctoutputoctets) / 1048576, 2),
                    $session->framedipaddress,
                    $session->nasipaddress,
                    $session->acctterminatecause
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
