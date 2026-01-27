<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerMacAddress;
use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\RadCheck;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function store(Request $request, User $customer, AuditLogService $auditLogService)
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

        DB::beginTransaction();
        try {
            $macBinding = CustomerMacAddress::create([
                'user_id' => $customer->id,
                'mac_address' => $formattedMac,
                'device_name' => $request->input('device_name'),
                'notes' => $request->input('notes'),
                'status' => 'active',
                'first_seen_at' => now(),
                'added_by' => Auth::id(),
            ]);

            // Integrate with RADIUS MAC authentication
            $networkUser = NetworkUser::where('user_id', $customer->id)->first();
            if ($networkUser && $networkUser->username) {
                try {
                    // Add MAC to RADIUS radcheck table for MAC authentication
                    RadCheck::updateOrCreate(
                        [
                            'username' => $networkUser->username,
                            'attribute' => 'Calling-Station-Id',
                            'value' => $formattedMac,
                        ],
                        [
                            'op' => '==',
                        ]
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to add MAC to RADIUS', [
                        'username' => $networkUser->username,
                        'mac' => $formattedMac,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Audit logging
            $auditLogService->logCreated($macBinding, $macBinding->toArray());

            DB::commit();
            return back()->with('success', 'MAC address bound successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add MAC binding', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to add MAC binding.']);
        }
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
    public function destroy(
        User $customer,
        CustomerMacAddress $macAddress,
        MikrotikService $mikrotikService,
        AuditLogService $auditLogService
    ) {
        DB::beginTransaction();
        try {
            $macAddressValue = $macAddress->mac_address;

            // Get network user for RADIUS integration
            $networkUser = NetworkUser::where('user_id', $customer->id)->first();

            // Integrate with RADIUS MAC authentication
            if ($networkUser && $networkUser->username) {
                try {
                    // Remove MAC from RADIUS radcheck table
                    RadCheck::where('username', $networkUser->username)
                        ->where('attribute', 'Calling-Station-Id')
                        ->where('value', $macAddressValue)
                        ->delete();
                } catch (\Exception $e) {
                    Log::warning('Failed to remove MAC from RADIUS', [
                        'username' => $networkUser->username,
                        'mac' => $macAddressValue,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Clear MikroTik MAC binding if applicable
            if ($networkUser) {
                try {
                    $router = MikrotikRouter::where('is_active', true)->first();
                    
                    if ($router && $mikrotikService->connectRouter($router->id)) {
                        // Disconnect any active sessions with this MAC
                        $sessions = $mikrotikService->getActiveSessions($router->id);
                        
                        foreach ($sessions as $session) {
                            if (isset($session['caller-id']) && strtolower($session['caller-id']) === strtolower($macAddressValue)) {
                                $mikrotikService->disconnectSession($session['id']);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to clear MikroTik MAC binding', [
                        'mac' => $macAddressValue,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Delete the MAC address binding
            $macAddress->delete();

            // Audit logging
            $auditLogService->logDeleted($macAddress, [
                'mac_address' => $macAddressValue,
                'customer_id' => $customer->id,
            ]);

            DB::commit();
            return back()->with('success', 'MAC address binding removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove MAC binding', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to remove MAC binding.']);
        }
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
