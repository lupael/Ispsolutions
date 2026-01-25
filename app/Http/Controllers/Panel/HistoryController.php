<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SmsLog;
use App\Models\RadAcct;
use App\Models\PackageChangeRequest;
use App\Models\NetworkUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Display payment history.
     */
    public function paymentHistory(Request $request): View
    {
        $user = auth()->user();

        $query = Payment::where('user_id', $user->id)
            ->with(['invoice', 'collectedBy']);

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest('payment_date')->paginate(20);

        $totalPaid = Payment::where('user_id', $user->id)->sum('amount');

        return view('panels.customer.history.payments', compact('payments', 'totalPaid'));
    }

    /**
     * Display SMS history.
     */
    public function smsHistory(Request $request): View
    {
        $user = auth()->user();

        $query = SmsLog::where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($user) {
                $q->where('phone_number', $user->company_phone)
                  ->orWhere('user_id', $user->id);
            });

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $smsLogs = $query->latest()->paginate(20);

        return view('panels.customer.history.sms', compact('smsLogs'));
    }

    /**
     * Display session history from RADIUS.
     */
    public function sessionHistory(Request $request): View
    {
        $user = auth()->user();
        $networkUser = NetworkUser::where('user_id', $user->id)->first();

        $sessions = collect();
        $totalUpload = 0;
        $totalDownload = 0;

        if ($networkUser) {
            $query = RadAcct::where('username', $networkUser->username);

            if ($request->filled('start_date')) {
                $query->whereDate('acctstarttime', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('acctstarttime', '<=', $request->end_date);
            }

            $sessions = $query->orderBy('acctstarttime', 'desc')->paginate(20);

            // Calculate totals
            $totals = RadAcct::where('username', $networkUser->username)
                ->selectRaw('SUM(acctinputoctets) as total_upload, SUM(acctoutputoctets) as total_download')
                ->first();

            $totalUpload = $totals->total_upload ?? 0;
            $totalDownload = $totals->total_download ?? 0;
        }

        return view('panels.customer.history.sessions', compact('sessions', 'totalUpload', 'totalDownload'));
    }

    /**
     * Display service change history (package upgrades/downgrades).
     */
    public function serviceChangeHistory(Request $request): View
    {
        $user = auth()->user();

        $query = PackageChangeRequest::where('user_id', $user->id)
            ->with(['currentPackage', 'requestedPackage', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        $requests = $query->latest()->paginate(20);

        return view('panels.customer.history.service-changes', compact('requests'));
    }
}
