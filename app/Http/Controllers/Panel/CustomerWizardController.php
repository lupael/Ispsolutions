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
use App\Services\MikrotikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerWizardController extends Controller
{
    private const TOTAL_STEPS = 7;

    public function __construct(
        private MikrotikService $mikrotikService
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
                ->where('tenant_id', getCurrentTenantId())
                ->notExpired()
                ->first();

            if ($tempCustomer) {
                return redirect()->route('panel.admin.customers.wizard.step', ['step' => $tempCustomer->step]);
            }
        }

        // Create new session
        $sessionId = Str::uuid()->toString();
        $request->session()->put('wizard_session_id', $sessionId);

        $tempCustomer = TempCustomer::create([
            'user_id' => auth()->id(),
            'tenant_id' => getCurrentTenantId(),
            'session_id' => $sessionId,
            'step' => 1,
            'data' => [],
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
            ->where('tenant_id', getCurrentTenantId())
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
            5 => $this->showStep5($tempCustomer, $data),
            6 => $this->showStep6($tempCustomer, $data),
            7 => $this->showStep7($tempCustomer, $data),
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
            ->where('tenant_id', getCurrentTenantId())
            ->notExpired()
            ->firstOrFail();

        return match ($step) {
            1 => $this->processStep1($request, $tempCustomer),
            2 => $this->processStep2($request, $tempCustomer),
            3 => $this->processStep3($request, $tempCustomer),
            4 => $this->processStep4($request, $tempCustomer),
            5 => $this->processStep5($request, $tempCustomer),
            6 => $this->processStep6($request, $tempCustomer),
            7 => $this->processStep7($request, $tempCustomer),
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

        return redirect()->route('panel.admin.customers')
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
        $packages = ServicePackage::where('tenant_id', getCurrentTenantId())
            ->where('is_active', true)
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
        $zones = Zone::where('tenant_id', getCurrentTenantId())
            ->where('is_active', true)
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

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 5]);
    }

    /**
     * Step 5: Custom Fields
     */
    private function showStep5(TempCustomer $tempCustomer, array $data): View
    {
        // Check if there are any custom fields configured
        // For now, we'll skip this step automatically
        return view('panels.shared.customers.wizard.step5', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'currentStep' => 5,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep5(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        // Process custom fields if any
        $customFields = $request->input('custom_fields', []);

        $tempCustomer->setStepData(5, ['custom_fields' => $customFields]);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 6]);
    }

    /**
     * Step 6: Initial Payment
     */
    private function showStep6(TempCustomer $tempCustomer, array $data): View
    {
        $allData = $tempCustomer->getAllData();
        $packagePrice = $allData['package_price'] ?? 0;

        return view('panels.shared.customers.wizard.step6', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'packagePrice' => $packagePrice,
            'currentStep' => 6,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep6(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,card,mobile_money,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        $tempCustomer->setStepData(6, $validated);
        $tempCustomer->save();

        if ($request->input('action') === 'save_draft') {
            return redirect()->back()->with('success', 'Draft saved successfully.');
        }

        return redirect()->route('panel.admin.customers.wizard.step', ['step' => 7]);
    }

    /**
     * Step 7: Review & Confirmation
     */
    private function showStep7(TempCustomer $tempCustomer, array $data): View
    {
        $allData = $tempCustomer->getAllData();

        return view('panels.shared.customers.wizard.step7', [
            'tempCustomer' => $tempCustomer,
            'data' => $data,
            'allData' => $allData,
            'currentStep' => 7,
            'totalSteps' => self::TOTAL_STEPS,
        ]);
    }

    private function processStep7(Request $request, TempCustomer $tempCustomer): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $allData = $tempCustomer->getAllData();

            // Generate username and password
            $username = $allData['pppoe_username'] ?? $this->generateUsername($allData['name']);
            $password = $allData['pppoe_password'] ?? Str::random(10);

            // Create customer user with network credentials
            $customer = User::create([
                'tenant_id' => getCurrentTenantId(),
                'name' => $allData['name'],
                'email' => $allData['email'],
                'username' => $username,
                'password' => Hash::make($password), // Hashed for app login
                'radius_password' => $password, // Plain text for RADIUS
                'phone' => $allData['mobile'],
                'address' => $allData['address'] ?? null,
                'city' => $allData['city'] ?? null,
                'state' => $allData['state'] ?? null,
                'postal_code' => $allData['postal_code'] ?? null,
                'country' => $allData['country'] ?? null,
                'operator_level' => 100, // Customer level
                'is_active' => true,
                'activated_at' => now(),
                'created_by' => auth()->id(),
                'service_package_id' => $allData['package_id'],
                // Network service fields
                'service_type' => $allData['connection_type'] ?? null,
                'connection_type' => $allData['connection_type'] ?? null,
                'status' => 'active',
                'zone_id' => $allData['zone_id'] ?? null,
            ]);

            // Assign customer role
            $customer->assignRole('customer');

            // Note: RADIUS provisioning now happens automatically via UserObserver
            // The observer will sync customer to RADIUS when created

            // Sync to MikroTik if PPPoE (optional, for direct router provisioning)
            if (isset($allData['connection_type']) && $allData['connection_type'] === 'pppoe') {
                try {
                    // MikroTik service may need to be updated to work with User model
                    // For now, we'll skip this or update MikrotikService later
                    // $this->mikrotikService->createPPPoEUser($customer);
                } catch (\Exception $e) {
                    // Log error but don't fail the transaction
                    logger()->error('Failed to sync PPPoE user to MikroTik: ' . $e->getMessage());
                }
            }

            // Generate first invoice
            $package = ServicePackage::findOrFail($allData['package_id']);
            $validityDays = $package->validity_days ?? 30;
            $startDate = now();
            $endDate = $startDate->copy()->addDays($validityDays);

            $invoice = Invoice::create([
                'tenant_id' => getCurrentTenantId(),
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

            // Create payment if amount > 0
            if (isset($allData['payment_amount']) && $allData['payment_amount'] > 0) {
                $payment = Payment::create([
                    'tenant_id' => getCurrentTenantId(),
                    'user_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allData['payment_amount'],
                    'payment_method' => $allData['payment_method'],
                    'payment_reference' => $allData['payment_reference'] ?? null,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'collected_by' => $allData['collected_by'] ?? auth()->id(),
                    'notes' => $allData['payment_notes'] ?? 'Initial payment via wizard',
                ]);

                // Update invoice status if fully paid
                if ($allData['payment_amount'] >= $package->price) {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                // Update customer wallet balance if overpaid
                if ($allData['payment_amount'] > $package->price) {
                    $customer->wallet_balance = $allData['payment_amount'] - $package->price;
                    $customer->save();
                }
            }

            // Set network user expiry date if it was created
            if (isset($networkUser) && $networkUser) {
                $networkUser->expiry_date = $endDate;
                $networkUser->save();
            }

            // Clean up temp customer data
            $tempCustomer->delete();
            $request->session()->forget('wizard_session_id');

            DB::commit();

            return redirect()->route('panel.admin.customers.show', $customer)
                ->with('success', 'Customer created successfully! Username: ' . $username);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Customer wizard completion failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate a unique username.
     */
    private function generateUsername(string $name): string
    {
        $base = strtolower(str_replace(' ', '', $name));
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $date = now()->format('Ymd');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $date . '-' . $random;
    }
}
