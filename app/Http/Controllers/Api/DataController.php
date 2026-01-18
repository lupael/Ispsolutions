<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\NetworkUser;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Get users data (for Admin panel)
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::with(['role', 'tenant'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate($request->get('per_page', 20));

        return response()->json($users);
    }

    /**
     * Get network users data
     */
    public function getNetworkUsers(Request $request): JsonResponse
    {
        $query = NetworkUser::with(['user', 'package'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $networkUsers = $query->paginate($request->get('per_page', 20));

        return response()->json($networkUsers);
    }

    /**
     * Get invoices data
     */
    public function getInvoices(Request $request): JsonResponse
    {
        $query = Invoice::with(['user', 'package'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($invoices);
    }

    /**
     * Get payments data
     */
    public function getPayments(Request $request): JsonResponse
    {
        $query = Payment::with(['user', 'invoice', 'paymentGateway'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by method
        if ($request->has('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('paid_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('paid_at', '<=', $request->to_date);
        }

        $payments = $query->latest('paid_at')->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }

    /**
     * Get packages data
     */
    public function getPackages(Request $request): JsonResponse
    {
        $query = Package::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('billing_type')) {
            $query->where('billing_type', $request->billing_type);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $packages = $query->paginate($request->get('per_page', 20));

        return response()->json($packages);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'users' => [
                'total' => User::where('tenant_id', $tenantId)->count(),
                'active' => User::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            ],
            'invoices' => [
                'total' => Invoice::where('tenant_id', $tenantId)->count(),
                'pending' => Invoice::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
                'overdue' => Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count(),
                'paid' => Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->count(),
            ],
            'revenue' => [
                'today' => Payment::where('tenant_id', $tenantId)
                    ->whereDate('paid_at', today())
                    ->where('status', 'completed')
                    ->sum('amount'),
                'this_month' => Payment::where('tenant_id', $tenantId)
                    ->whereYear('paid_at', now()->year)
                    ->whereMonth('paid_at', now()->month)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'this_year' => Payment::where('tenant_id', $tenantId)
                    ->whereYear('paid_at', now()->year)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ],
            'network_users' => [
                'total' => NetworkUser::where('tenant_id', $tenantId)->count(),
                'active' => NetworkUser::where('tenant_id', $tenantId)->where('status', 'active')->count(),
                'suspended' => NetworkUser::where('tenant_id', $tenantId)->where('status', 'suspended')->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $limit = $request->get('limit', 10);

        $activities = collect();

        // Recent payments
        $recentPayments = Payment::where('tenant_id', $tenantId)
            ->with('user')
            ->latest('paid_at')
            ->limit($limit)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'message' => "Payment received from {$payment->user->name}",
                    'amount' => $payment->amount,
                    'timestamp' => $payment->paid_at,
                ];
            });

        // Recent invoices
        $recentInvoices = Invoice::where('tenant_id', $tenantId)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'message' => "Invoice generated for {$invoice->user->name}",
                    'amount' => $invoice->total_amount,
                    'timestamp' => $invoice->created_at,
                ];
            });

        $activities = $activities->merge($recentPayments)->merge($recentInvoices)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

        return response()->json($activities);
    }
}
