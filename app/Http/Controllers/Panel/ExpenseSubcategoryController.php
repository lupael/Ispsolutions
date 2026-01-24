<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use Illuminate\Http\Request;

class ExpenseSubcategoryController extends Controller
{
    /**
     * Display subcategories for a category.
     */
    public function index(ExpenseCategory $category)
    {
        $subcategories = $category->subcategories()
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('panel.expenses.subcategories.index', compact('category', 'subcategories'));
    }

    /**
     * Show form to create subcategory.
     */
    public function create(ExpenseCategory $category)
    {
        return view('panel.expenses.subcategories.create', compact('category'));
    }

    /**
     * Store a new subcategory.
     */
    public function store(Request $request, ExpenseCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->subcategories()->create($request->only([
            'name',
            'description',
            'is_active',
        ]));

        return redirect()
            ->route('panel.expenses.categories.subcategories.index', $category)
            ->with('success', 'Subcategory created successfully.');
    }

    /**
     * Show form to edit subcategory.
     */
    public function edit(ExpenseCategory $category, ExpenseSubcategory $subcategory)
    {
        return view('panel.expenses.subcategories.edit', compact('category', 'subcategory'));
    }

    /**
     * Update subcategory.
     */
    public function update(Request $request, ExpenseCategory $category, ExpenseSubcategory $subcategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subcategory->update($request->only([
            'name',
            'description',
            'is_active',
        ]));

        return redirect()
            ->route('panel.expenses.categories.subcategories.index', $category)
            ->with('success', 'Subcategory updated successfully.');
    }

    /**
     * Delete subcategory.
     */
    public function destroy(ExpenseCategory $category, ExpenseSubcategory $subcategory)
    {
        // Check if subcategory has expenses
        $expensesCount = $subcategory->expenses()->count();

        if ($expensesCount > 0) {
            return back()->withErrors(['error' => 'Cannot delete subcategory with existing expenses.']);
        }

        $subcategory->delete();

        return redirect()
            ->route('panel.expenses.categories.subcategories.index', $category)
            ->with('success', 'Subcategory deleted successfully.');
    }
}
