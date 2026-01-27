<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Global search for customers and invoices.
     * Searches by: mobile no, username, email, invoice number, or name.
     * Results respect permissions and ownership.
     */
    public function search(Request $request): View
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $query = trim($request->input('query', ''));
        $user = auth()->user();
        $customers = collect();
        $invoices = collect();

        if (!empty($query)) {
            // Escape special LIKE characters to prevent unintended wildcard matching
            $escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

            // Search Customers based on permissions
            $customers = $this->searchCustomers($escapedQuery, $user);

            // Search Invoices based on permissions
            $invoices = $this->searchInvoices($escapedQuery, $user);
        }

        return view('panels.search.results', compact('customers', 'invoices', 'query'));
    }

    /**
     * Search customers with permission filtering.
     */
    private function searchCustomers(string $query, $user)
    {
        $customerQuery = User::query();

        // Apply tenant and ownership filters based on user role
        $customerQuery = $this->applyPermissionFilters($customerQuery, $user);

        // Apply search conditions (mobile, username, email, name)
        $customerQuery->where(function ($q) use ($query) {
            $q->where('email', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->orWhere('mobile', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%");
        });

        // Scope to customers only (operator_level = 100)
        $customerQuery->where('operator_level', 100);

        return $customerQuery
            ->with(['tenant:id,company_name', 'currentPackage:id,name'])
            ->select('id', 'name', 'email', 'username', 'mobile', 'phone', 'tenant_id', 'is_active', 'service_package_id', 'created_by')
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Search invoices with permission filtering.
     */
    private function searchInvoices(string $query, $user)
    {
        $invoiceQuery = Invoice::query();

        // Apply tenant and ownership filters based on user role
        $invoiceQuery = $this->applyInvoicePermissionFilters($invoiceQuery, $user);

        // Search by invoice number or user details
        $invoiceQuery->where(function ($q) use ($query) {
            $q->where('invoice_number', 'like', "%{$query}%")
                ->orWhereHas('user', function ($userQuery) use ($query) {
                    $userQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('mobile', 'like', "%{$query}%");
                });
        });

        return $invoiceQuery
            ->with(['user:id,name,email,username,mobile', 'package:id,name'])
            ->select('id', 'invoice_number', 'user_id', 'package_id', 'total_amount', 'status', 'due_date', 'tenant_id', 'created_by')
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Apply permission filters for customer search based on user role.
     */
    private function applyPermissionFilters($query, $user)
    {
        $operatorLevel = $user->operator_level;

        // Super Admin and Developer: Access all tenants
        if (in_array($operatorLevel, [0, 10])) {
            return $query; // No restrictions
        }

        // Admin, Manager, Accountant: Access their tenant only
        if (in_array($operatorLevel, [20, 50, 70])) {
            return $query->where('tenant_id', $user->tenant_id);
        }

        // Operator, Sub-Operator, Staff: Access only their created customers
        if (in_array($operatorLevel, [30, 40, 80])) {
            return $query->where('tenant_id', $user->tenant_id)
                ->where('created_by', $user->id);
        }

        // Customer: No access to search other customers
        return $query->whereRaw('1 = 0'); // Return empty result
    }

    /**
     * Apply permission filters for invoice search based on user role.
     */
    private function applyInvoicePermissionFilters($query, $user)
    {
        $operatorLevel = $user->operator_level;

        // Super Admin and Developer: Access all tenants
        if (in_array($operatorLevel, [0, 10])) {
            return $query; // No restrictions
        }

        // Admin, Manager, Accountant: Access their tenant only
        if (in_array($operatorLevel, [20, 50, 70])) {
            return $query->where('tenant_id', $user->tenant_id);
        }

        // Operator, Sub-Operator, Staff: Access only their created invoices
        if (in_array($operatorLevel, [30, 40, 80])) {
            return $query->where('tenant_id', $user->tenant_id)
                ->where('created_by', $user->id);
        }

        // Customer: Access only their own invoices
        if ($operatorLevel === 100) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0'); // Return empty result
    }
}
