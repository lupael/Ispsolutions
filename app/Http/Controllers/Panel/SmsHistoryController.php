<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Http\Request;

class SmsHistoryController extends Controller
{
    /**
     * Display SMS history for all customers.
     */
    public function index(Request $request)
    {
        $query = SmsLog::with(['user']);

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by phone or message
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $smsLogs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('panel.sms.history.index', compact('smsLogs'));
    }

    /**
     * Display SMS history for a specific customer.
     */
    public function customer(User $customer)
    {
        $smsLogs = SmsLog::where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('panel.sms.history.customer', compact('customer', 'smsLogs'));
    }

    /**
     * Display detailed view of a specific SMS.
     */
    public function show(SmsLog $smsLog)
    {
        return view('panel.sms.history.show', compact('smsLog'));
    }
}
