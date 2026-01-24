<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Display all expense categories.
     */
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('panel.expenses.categories.index', compact('categories'));
    }

    /**
     * Show form to create category.
     */
    public function create()
    {
        return view('panel.expenses.categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        ExpenseCategory::create($request->only([
            'name',
            'description',
            'color',
            'is_active',
        ]));

        return redirect()
            ->route('panel.expenses.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show form to edit category.
     */
    public function edit(ExpenseCategory $category)
    {
        return view('panel.expenses.categories.edit', compact('category'));
    }

    /**
     * Update category.
     */
    public function update(Request $request, ExpenseCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $category->update($request->only([
            'name',
            'description',
            'color',
            'is_active',
        ]));

        return redirect()
            ->route('panel.expenses.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category.
     */
    public function destroy(ExpenseCategory $category)
    {
        // Check if category has expenses
        $expensesCount = $category->expenses()->count();

        if ($expensesCount > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with existing expenses.']);
        }

        $category->delete();

        return redirect()
            ->route('panel.expenses.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
