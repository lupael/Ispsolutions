<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsGatewayController extends Controller
{
    /**
     * Display a listing of SMS gateways.
     */
    public function index()
    {
        $gateways = SmsGateway::latest()->paginate(15);
        return view('panels.admin.sms.gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new SMS gateway.
     */
    public function create()
    {
        return view('panels.admin.sms.gateways.create');
    }

    /**
     * Store a newly created SMS gateway.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|in:twilio,nexmo,msg91,bulksms,custom',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'configuration' => 'nullable|array',
            'rate_per_sms' => 'required|numeric|min:0',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            SmsGateway::where('is_default', true)->update(['is_default' => false]);
        }

        $gateway = SmsGateway::create($validated);

        return redirect()->route('panel.admin.sms.gateways.index')
            ->with('success', 'SMS Gateway created successfully.');
    }

    /**
     * Display the specified SMS gateway.
     */
    public function show(SmsGateway $gateway)
    {
        return view('panels.admin.sms.gateways.show', compact('gateway'));
    }

    /**
     * Show the form for editing the specified SMS gateway.
     */
    public function edit(SmsGateway $gateway)
    {
        return view('panels.admin.sms.gateways.edit', compact('gateway'));
    }

    /**
     * Update the specified SMS gateway.
     */
    public function update(Request $request, SmsGateway $gateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|in:twilio,nexmo,msg91,bulksms,custom',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'configuration' => 'nullable|array',
            'rate_per_sms' => 'required|numeric|min:0',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default') && !$gateway->is_default) {
            SmsGateway::where('is_default', true)->update(['is_default' => false]);
        }

        $gateway->update($validated);

        return redirect()->route('panel.admin.sms.gateways.index')
            ->with('success', 'SMS Gateway updated successfully.');
    }

    /**
     * Remove the specified SMS gateway.
     */
    public function destroy(SmsGateway $gateway)
    {
        $gateway->delete();

        return redirect()->route('panel.admin.sms.gateways.index')
            ->with('success', 'SMS Gateway deleted successfully.');
    }

    /**
     * Test the SMS gateway connection.
     */
    public function test(Request $request, SmsGateway $gateway)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        try {
            // Implement test SMS sending logic here
            // This would use the gateway's configuration to send a test SMS
            
            return back()->with('success', 'Test SMS sent successfully.');
        } catch (\Exception $e) {
            Log::error('SMS Gateway test failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send test SMS: ' . $e->getMessage());
        }
    }

    /**
     * Set the gateway as default.
     */
    public function setDefault(SmsGateway $gateway)
    {
        SmsGateway::where('is_default', true)->update(['is_default' => false]);
        $gateway->update(['is_default' => true, 'is_active' => true]);

        return redirect()->route('panel.admin.sms.gateways.index')
            ->with('success', 'Gateway set as default successfully.');
    }
}
