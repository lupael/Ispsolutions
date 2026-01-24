<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseManagementController extends Controller
{
    /**
     * Display all expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'subcategory', 'recordedBy']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('expense_category_id', $request->input('category_id'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('expense_date', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('expense_date', '<=', $request->input('end_date'));
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
        $categories = ExpenseCategory::where('is_active', true)->get();

        // Calculate total
        $total = $query->sum('amount');

        return view('panel.expenses.index', compact('expenses', 'categories', 'total'));
    }

    /**
     * Show form to create expense.
     */
    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->with('subcategories')->get();

        return view('panel.expenses.create', compact('categories'));
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_subcategory_id' => 'nullable|exists:expense_subcategories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('attachment');
        $data['recorded_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expenses', 'public');
            $data['attachment_path'] = $path;
        }

        Expense::create($data);

        return redirect()
            ->route('panel.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display a specific expense.
     */
    public function show(Expense $expense)
    {
        return view('panel.expenses.show', compact('expense'));
    }

    /**
     * Show form to edit expense.
     */
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->with('subcategories')->get();

        return view('panel.expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update expense.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_subcategory_id' => 'nullable|exists:expense_subcategories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except('attachment');

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }

            $path = $request->file('attachment')->store('expenses', 'public');
            $data['attachment_path'] = $path;
        }

        $expense->update($data);

        return redirect()
            ->route('panel.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Delete expense.
     */
    public function destroy(Expense $expense)
    {
        // Delete attachment file if exists
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $expense->delete();

        return redirect()
            ->route('panel.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Get subcategories for a category (AJAX).
     */
    public function getSubcategories(ExpenseCategory $category)
    {
        $subcategories = $category->subcategories()
            ->where('is_active', true)
            ->get();

        return response()->json($subcategories);
    }
}
