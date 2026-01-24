<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\VatProfile;
use App\Models\VatCollection;
use Illuminate\Http\Request;

class VatManagementController extends Controller
{
    /**
     * Display all VAT profiles.
     */
    public function index()
    {
        $vatProfiles = VatProfile::orderBy('name')->get();

        return view('panel.vat.index', compact('vatProfiles'));
    }

    /**
     * Show form to create VAT profile.
     */
    public function create()
    {
        return view('panel.vat.create');
    }

    /**
     * Store a new VAT profile.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->input('is_default', false)) {
            VatProfile::where('is_default', true)->update(['is_default' => false]);
        }

        VatProfile::create($request->only([
            'name',
            'rate',
            'description',
            'is_default',
            'is_active',
        ]));

        return redirect()
            ->route('panel.vat.index')
            ->with('success', 'VAT profile created successfully.');
    }

    /**
     * Show form to edit VAT profile.
     */
    public function edit(VatProfile $vatProfile)
    {
        return view('panel.vat.edit', compact('vatProfile'));
    }

    /**
     * Update VAT profile.
     */
    public function update(Request $request, VatProfile $vatProfile)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->input('is_default', false)) {
            VatProfile::where('id', '!=', $vatProfile->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $vatProfile->update($request->only([
            'name',
            'rate',
            'description',
            'is_default',
            'is_active',
        ]));

        return redirect()
            ->route('panel.vat.index')
            ->with('success', 'VAT profile updated successfully.');
    }

    /**
     * Delete VAT profile.
     */
    public function destroy(VatProfile $vatProfile)
    {
        // Check if there are collections using this profile
        $collectionsCount = $vatProfile->collections()->count();

        if ($collectionsCount > 0) {
            return back()->withErrors(['error' => 'Cannot delete VAT profile with existing collections.']);
        }

        $vatProfile->delete();

        return redirect()
            ->route('panel.vat.index')
            ->with('success', 'VAT profile deleted successfully.');
    }

    /**
     * Display VAT collections.
     */
    public function collections(Request $request)
    {
        $query = VatCollection::with(['vatProfile', 'invoice', 'payment']);

        // Filter by tax period if provided
        if ($request->has('tax_period')) {
            $query->where('tax_period', $request->input('tax_period'));
        }

        // Filter by VAT profile if provided
        if ($request->has('vat_profile_id')) {
            $query->where('vat_profile_id', $request->input('vat_profile_id'));
        }

        $collections = $query->orderBy('collection_date', 'desc')->paginate(50);
        
        $vatProfiles = VatProfile::where('is_active', true)->get();

        return view('panel.vat.collections', compact('collections', 'vatProfiles'));
    }

    /**
     * Export VAT collections.
     */
    public function exportCollections(Request $request)
    {
        $query = VatCollection::with(['vatProfile', 'invoice', 'payment']);

        if ($request->has('tax_period')) {
            $query->where('tax_period', $request->input('tax_period'));
        }

        if ($request->has('vat_profile_id')) {
            $query->where('vat_profile_id', $request->input('vat_profile_id'));
        }

        $collections = $query->orderBy('collection_date')->get();

        // Generate CSV
        $filename = 'vat_collections_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($collections) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Invoice ID',
                'Payment ID',
                'VAT Profile',
                'Base Amount',
                'VAT Amount',
                'Total Amount',
                'Tax Period',
            ]);

            // Data rows
            foreach ($collections as $collection) {
                fputcsv($file, [
                    $collection->collection_date->format('Y-m-d'),
                    $collection->invoice_id ?? 'N/A',
                    $collection->payment_id ?? 'N/A',
                    $collection->vatProfile->name,
                    $collection->base_amount,
                    $collection->vat_amount,
                    $collection->total_amount,
                    $collection->tax_period,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
