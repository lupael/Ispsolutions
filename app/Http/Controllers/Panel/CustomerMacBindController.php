<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerMacAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerMacBindController extends Controller
{
    /**
     * Display MAC addresses for a customer.
     */
    public function index(User $customer)
    {
        $macAddresses = $customer->macAddresses()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('panel.customers.mac-binding.index', compact('customer', 'macAddresses'));
    }

    /**
     * Store a new MAC address binding.
     */
    public function store(Request $request, User $customer)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $macAddress = $request->input('mac_address');
        
        // Format and validate MAC address
        $formattedMac = CustomerMacAddress::formatMacAddress($macAddress);
        
        if (!CustomerMacAddress::isValidMacAddress($macAddress)) {
            return back()->withErrors(['mac_address' => 'Invalid MAC address format.']);
        }

        // Check if MAC address already exists for this customer
        $exists = $customer->macAddresses()
            ->where('mac_address', $formattedMac)
            ->exists();

        if ($exists) {
            return back()->withErrors(['mac_address' => 'This MAC address is already bound to this customer.']);
        }

        CustomerMacAddress::create([
            'user_id' => $customer->id,
            'mac_address' => $formattedMac,
            'device_name' => $request->input('device_name'),
            'notes' => $request->input('notes'),
            'status' => 'active',
            'first_seen_at' => now(),
            'added_by' => Auth::id(),
        ]);

        return back()->with('success', 'MAC address bound successfully.');
    }

    /**
     * Update MAC address status.
     */
    public function update(Request $request, User $customer, CustomerMacAddress $macAddress)
    {
        $request->validate([
            'status' => 'required|in:active,blocked',
            'device_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $macAddress->update($request->only(['status', 'device_name', 'notes']));

        return back()->with('success', 'MAC address updated successfully.');
    }

    /**
     * Remove MAC address binding.
     */
    public function destroy(User $customer, CustomerMacAddress $macAddress)
    {
        $macAddress->delete();

        return back()->with('success', 'MAC address binding removed successfully.');
    }

    /**
     * Bulk MAC binding from file upload.
     */
    public function bulkImport(Request $request, User $customer)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        
        $imported = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $formattedMac = CustomerMacAddress::formatMacAddress($line);
            
            if (!CustomerMacAddress::isValidMacAddress($line)) {
                $errors[] = "Invalid MAC address: $line";
                continue;
            }

            // Check if already exists
            $exists = $customer->macAddresses()
                ->where('mac_address', $formattedMac)
                ->exists();

            if ($exists) {
                continue;
            }

            CustomerMacAddress::create([
                'user_id' => $customer->id,
                'mac_address' => $formattedMac,
                'status' => 'active',
                'first_seen_at' => now(),
                'added_by' => Auth::id(),
            ]);

            $imported++;
        }

        $message = "Imported $imported MAC addresses successfully.";
        if (!empty($errors)) {
            $message .= ' Some entries were skipped due to errors.';
        }

        return back()->with('success', $message);
    }
}
