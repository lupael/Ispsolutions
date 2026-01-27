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
     * Searches by: username, email, invoice number, or name.
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
    private function searchCustomers(string $escapedQuery, $user)
    {
        $customerQuery = User::query();

        // Apply tenant and ownership filters based on user role
        $customerQuery = $this->applyPermissionFilters($customerQuery, $user);

        // Apply search conditions (username, email, name)
        $customerQuery->where(function ($q) use ($escapedQuery) {
            $q->where('email', 'like', "%{$escapedQuery}%")
                ->orWhere('name', 'like', "%{$escapedQuery}%")
                ->orWhere('username', 'like', "%{$escapedQuery}%");
        });

        // Scope to customers only (operator_level = 100)
        $customerQuery->where('operator_level', 100);

        return $customerQuery
            ->with(['tenant:id,company_name', 'currentPackage:id,name'])
            ->select('id', 'name', 'email', 'username', 'tenant_id', 'is_active', 'service_package_id', 'created_by')
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Search invoices with permission filtering.
     */
    private function searchInvoices(string $escapedQuery, $user)
    {
        $invoiceQuery = Invoice::query();

        // Apply tenant and ownership filters based on user role
        $invoiceQuery = $this->applyInvoicePermissionFilters($invoiceQuery, $user);

        // Search by invoice number or user details
        $invoiceQuery->where(function ($q) use ($escapedQuery) {
            $q->where('invoice_number', 'like', "%{$escapedQuery}%")
                ->orWhereHas('user', function ($userQuery) use ($escapedQuery) {
                    $userQuery->where('name', 'like', "%{$escapedQuery}%")
                        ->orWhere('email', 'like', "%{$escapedQuery}%")
                        ->orWhere('username', 'like', "%{$escapedQuery}%");
                });
        });

        return $invoiceQuery
            ->with(['user:id,name,email,username', 'package:id,name'])
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

        // Developer: Access all tenants
        if ($operatorLevel === 0) {
            return $query; // No restrictions
        }

        // Super Admin, Admin, Manager, Accountant: Access their tenant only
        if (in_array($operatorLevel, [10, 20, 50, 70])) {
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

        // Developer: Access all tenants
        if ($operatorLevel === 0) {
            return $query; // No restrictions
        }

        // Super Admin, Admin, Manager, Accountant: Access their tenant only
        if (in_array($operatorLevel, [10, 20, 50, 70])) {
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
