<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\ServicePackage;
use App\Models\TempCustomer;
use App\Models\User;
use App\Models\Zone;
use App\Services\BillingService;
use App\Services\MikrotikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerWizardController extends Controller
{
    private const TOTAL_STEPS = 4;

    public function __construct(
        private MikrotikService $mikrotikService,
        private BillingService $billingService
    ) {
    }

    /**
     * Start or resume the wizard.
     */
    public function start(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->get('wizard_session_id');

        // Check if there's an existing session
        if ($sessionId) {
            $tempCustomer = TempCustomer::where('session_id', $sessionId)
                ->where('user_id', auth()->id())
                ->notExpired()
                ->first();

            if ($tempCustomer) {
                return redirect()->route('panel.admin.customers.wizard.step', ['step' => $tempCustomer->step]);
            }
        }

        // Create new session
        $sessionId = Str::uuid()->toString();
        $request->session()->put('wizard_session_id', $sessionId);

        // FIX: Include tenant_id from the authenticated admin
        $tempCustomer = TempCustomer::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'user_id'    => auth()->id(),
            'session_id' => $sessionId,
            'step'       => 1,
            'data'       => [],
        ]);

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 1]);
    }

    /**
     * Display a specific wizard step.
     */
    public function show(Request $request, int $step): View|RedirectResponse
    {
        if ($step < 1 || $step > self::TOTAL_STEPS) {
            return redirect()->route('panel.admin.customers.wizard.start');
        }

        $sessionId = $request->session()->get('wizard_session_id');
        if (!$sessionId) {
            return redirect()->route('panel.admin.customers.wizard.start');
        }

        $tempCustomer = TempCustomer::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->notExpired()
            ->firstOrFail();

        // Extend expiration on access
        $tempCustomer->extend();

        $data = $tempCustomer->getStepData($step);

        return match ($step) {
            1 => $this->showStep1($tempCustomer, $data),
            2 => $this->showStep2($tempCustomer, $data),
            3 => $this->showStep3($tempCustomer, $data),
            4 => $this->showStep4($tempCustomer, $data),
        };
    }

    /**
     * Process form submission for a step.
     */
    public function store(Request $request, int $step): RedirectResponse
    {
        $sessionId = $request->session()->get('wizard_session_id');
        if (!$sessionId) {
            return redirect()->route('panel.admin.customers.wizard.start');
        }

        $tempCustomer = TempCustomer::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->notExpired()
            ->firstOrFail();

        return match ($step) {
            1 => $this->processStep1($request, $tempCustomer),
            2 => $this->processStep2($request, $tempCustomer),
            3 => $this->processStep3($request, $tempCustomer),
            4 => $this->processStep4($request, $tempCustomer),
        };
    }

    /**
     * Cancel the wizard and clear temp data.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->get('wizard_session_id');
        if ($sessionId) {
            TempCustomer::where('session_id', $sessionId)
                ->where('user_id', auth()->id())
                ->delete();
            $request->session()->forget('wizard_session_id');
        }

        return redirect()->route('panel.admin.customers.index')
            ->with('success', 'Customer creation wizard cancelled.');
    }

    /**
     * Step 1: Basic Information
     */
    private function showStep1(TempCustomer $tempCustomer, array $data): View
    {
        return view('panels.shared.customers.wizard.step1', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'currentStep' => 1,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep1(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        $tempCustomer->setStepData(1, $validated);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 2]);
    }

    /**
     * Step 2: Connection Type
     */
    private function showStep2(TempCustomer $tempCustomer, array $data): View
    {
        return view('panels.shared.customers.wizard.step2', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'currentStep' => 2,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep2(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'connection_type' => 'required|in:pppoe,hotspot,static_ip,other',
            'pppoe_username' => 'required_if:connection_type,pppoe|nullable|string|max:255',
            'pppoe_password' => 'required_if:connection_type,pppoe|nullable|string|max:255',
            'pppoe_profile' => 'nullable|string|max:255',
            'hotspot_mac' => 'required_if:connection_type,hotspot|nullable|string|max:17',
            'hotspot_device_type' => 'nullable|string|max:100',
            'static_ip' => 'required_if:connection_type,static_ip|nullable|ip',
            'static_subnet' => 'nullable|string|max:100',
            'other_config' => 'required_if:connection_type,other|nullable|string',
        ]);

        $tempCustomer->setStepData(2, $validated);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 3]);
    }

    /**
     * Step 3: Package Selection
     */
    private function showStep3(TempCustomer $tempCustomer, array $data): View
    {
        $packages = ServicePackage::where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('panels.shared.customers.wizard.step3', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'packages' => $packages,
            'currentStep' => 3,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep3(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = ServicePackage::findOrFail($validated['package_id']);
        $validated['package_name'] = $package->name;
        $validated['package_price'] = $package->price;
        $validated['validity_days'] = $package->validity_days;

        $tempCustomer->setStepData(3, $validated);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 4]);
    }

    /**
     * Step 4: Address & Zone
     */
    private function showStep4(TempCustomer $tempCustomer, array $data): View
    {
        $zones = Zone::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('panels.shared.customers.wizard.step4', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'zones' => $zones,
            'currentStep' => 4,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep4(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $tempCustomer->setStepData(4, $validated);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        // Complete the customer creation process
        return $this->completeCustomerCreation($request, $tempCustomer);
    }

    /**
     * Complete customer creation with suspended status.
     */
    private function completeCustomerCreation(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $allData = $tempCustomer->getAllData();
            $tenantId = auth()->user()->tenant_id; // Current Admin's Tenant

            // Generate username and password
            $username = $allData['pppoe_username'] ?? $this->generateUsername($allData['name']);
            $password = $allData['pppoe_password'] ?? Str::random(10);

            // Create customer user with tenant_id
            $customer = User::create([
                'tenant_id' => $tenantId, // FIX: Multi-tenancy support
                'name' => $allData['name'],
                'email' => $allData['email'],
                'username' => $username,
                'password' => Hash::make($password),
                'radius_password' => $password,
                'phone' => $allData['mobile'],
                'address' => $allData['address'] ?? null,
                'city' => $allData['city'] ?? null,
                'state' => $allData['state'] ?? null,
                'postal_code' => $allData['postal_code'] ?? null,
                'country' => $allData['country'] ?? null,
                'operator_level' => User::OPERATOR_LEVEL_CUSTOMER,
                'is_active' => true,
                'is_subscriber' => true,
                'activated_at' => now(),
                'created_by' => auth()->id(),
                'service_package_id' => $allData['package_id'],
                'service_type' => $allData['connection_type'] ?? null,
                'connection_type' => $allData['connection_type'] ?? null,
                'status' => 'suspended',
                'zone_id' => $allData['zone_id'] ?? null,
            ]);

            $customer->assignRole('customer');

            // Generate first invoice with tenant_id
            $package = ServicePackage::findOrFail($allData['package_id']);
            $validityDays = $package->validity_days ?? 30;
            $startDate = now();
            $endDate = $startDate->copy()->addDays($validityDays);

            Invoice::create([
                'tenant_id' => $tenantId, // FIX: Multi-tenancy support
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $customer->id,
                'package_id' => $allData['package_id'],
                'amount' => $package->price,
                'tax_amount' => 0,
                'total_amount' => $package->price,
                'status' => 'pending',
                'billing_period_start' => $startDate,
                'billing_period_end' => $endDate,
                'due_date' => $startDate->copy()->addDays(7),
            ]);

            // Clean up
            $tempCustomer->delete();
            $request->session()->forget('wizard_session_id');

            DB::commit();

            return redirect()->route('panel.admin.customers.show', $customer)
                ->with('success', 'Customer created successfully!')
                ->with('info', "Username: {$username}. Service is suspended until payment.");
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Customer wizard failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create customer: ' . $e->getMessage()]);
        }
    }

    private function generateUsername(string $name): string
    {
        $base = strtolower(str_replace(' ', '', $name));
        $username = $base;
        $counter = 1;

        // Ensure check is scoped to the current tenant
        while (User::where('tenant_id', auth()->user()->tenant_id)
                   ->where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $date = now()->format('Ymd');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $date . '-' . $random;
    }
}
