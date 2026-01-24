<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerCustomFieldController extends Controller
{
    /**
     * Display a listing of custom fields.
     */
    public function index(): View
    {
        $fields = CustomerCustomField::where('tenant_id', auth()->user()->tenant_id)
            ->ordered()
            ->get();
            
        return view('panels.admin.custom-fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new custom field.
     */
    public function create(): View
    {
        return view('panels.admin.custom-fields.create');
    }

    /**
     * Store a newly created custom field.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,select,checkbox,textarea',
            'required' => 'nullable|boolean',
            'options' => 'nullable|json',
            'category' => 'nullable|string|max:255',
            'visibility' => 'nullable|array',
            'visibility.*' => 'string',
        ]);
        
        // Convert options from JSON string to array if provided
        if (isset($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = json_decode($validated['options'], true);
        }
        
        $field = CustomerCustomField::create([
            ...$validated,
            'tenant_id' => auth()->user()->tenant_id,
            'order' => CustomerCustomField::where('tenant_id', auth()->user()->tenant_id)->max('order') + 1,
            'required' => $validated['required'] ?? false,
        ]);
        
        return redirect()->route('panel.admin.custom-fields.index')
            ->with('success', 'Custom field created successfully');
    }

    /**
     * Show the form for editing the specified custom field.
     */
    public function edit(CustomerCustomField $customField): View
    {
        // Ensure user can only edit fields from their tenant
        if ($customField->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
        
        return view('panels.admin.custom-fields.edit', compact('customField'));
    }

    /**
     * Update the specified custom field.
     */
    public function update(Request $request, CustomerCustomField $customField)
    {
        // Ensure user can only update fields from their tenant
        if ($customField->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,select,checkbox,textarea',
            'required' => 'nullable|boolean',
            'options' => 'nullable|json',
            'category' => 'nullable|string|max:255',
            'visibility' => 'nullable|array',
            'visibility.*' => 'string',
        ]);
        
        // Convert options from JSON string to array if provided
        if (isset($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = json_decode($validated['options'], true);
        }
        
        $customField->update([
            ...$validated,
            'required' => $validated['required'] ?? false,
        ]);
        
        return redirect()->route('panel.admin.custom-fields.index')
            ->with('success', 'Custom field updated successfully');
    }

    /**
     * Remove the specified custom field.
     */
    public function destroy(CustomerCustomField $customField)
    {
        // Ensure user can only delete fields from their tenant
        if ($customField->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
        
        $customField->delete();
        
        return redirect()->route('panel.admin.custom-fields.index')
            ->with('success', 'Custom field deleted successfully');
    }

    /**
     * Reorder custom fields via AJAX.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:customer_custom_fields,id',
        ]);
        
        foreach ($validated['order'] as $index => $fieldId) {
            CustomerCustomField::where('id', $fieldId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->update(['order' => $index]);
        }
        
        return response()->json(['success' => true, 'message' => 'Order updated successfully']);
    }
}
