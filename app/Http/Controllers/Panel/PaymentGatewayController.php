<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentGatewayRequest;
use App\Http\Requests\UpdatePaymentGatewayRequest;
use App\Http\Traits\HandlesCrudOperations;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    use HandlesCrudOperations;

    /**
     * Display a listing of payment gateways.
     */
    public function index(): View
    {
        $this->authorize('viewAny', PaymentGateway::class);

        $gateways = PaymentGateway::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(20);

        return view('panels.payment-gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new payment gateway.
     */
    public function create(): View
    {
        $this->authorize('create', PaymentGateway::class);

        return view('panels.payment-gateways.create');
    }

    /**
     * Store a newly created payment gateway.
     */
    public function store(StorePaymentGatewayRequest $request): RedirectResponse
    {
        return $this->handleCrudOperation(
            function () use ($request) {
                $data = $request->validated();
                $data['tenant_id'] = auth()->user()->tenant_id;

                // Encrypt sensitive configuration data
                if (isset($data['configuration'])) {
                    $data['configuration'] = $this->encryptSensitiveConfig($data['configuration']);
                }

                return PaymentGateway::create($data);
            },
            'Payment gateway created successfully',
            'PaymentGatewayController@store',
            'panel.payment-gateways.index'
        );
    }

    /**
     * Display the specified payment gateway.
     */
    public function show(PaymentGateway $gateway): View
    {
        $this->authorize('view', $gateway);

        // Decrypt configuration for display (mask sensitive data)
        $gateway->configuration = $this->maskSensitiveConfig($gateway->configuration);

        return view('panels.payment-gateways.show', compact('gateway'));
    }

    /**
     * Show the form for editing the specified payment gateway.
     */
    public function edit(PaymentGateway $gateway): View
    {
        $this->authorize('update', $gateway);

        // Decrypt configuration for editing (mask sensitive data)
        $gateway->configuration = $this->maskSensitiveConfig($gateway->configuration);

        return view('panels.payment-gateways.edit', compact('gateway'));
    }

    /**
     * Update the specified payment gateway.
     */
    public function update(UpdatePaymentGatewayRequest $request, PaymentGateway $gateway): RedirectResponse
    {
        return $this->handleCrudOperation(
            function () use ($request, $gateway) {
                $data = $request->validated();

                // Encrypt sensitive configuration data
                if (isset($data['configuration'])) {
                    $data['configuration'] = $this->encryptSensitiveConfig($data['configuration']);
                }

                $gateway->update($data);

                return $gateway;
            },
            'Payment gateway updated successfully',
            'PaymentGatewayController@update',
            'panel.payment-gateways.index'
        );
    }

    /**
     * Remove the specified payment gateway.
     */
    public function destroy(PaymentGateway $gateway): RedirectResponse
    {
        $this->authorize('delete', $gateway);

        return $this->handleDelete(
            function () use ($gateway) {
                $gateway->delete();
                return $gateway;
            },
            'Payment gateway deleted successfully',
            'PaymentGatewayController@destroy'
        );
    }

    /**
     * Toggle gateway active status.
     */
    public function toggle(PaymentGateway $gateway): RedirectResponse
    {
        $this->authorize('update', $gateway);

        return $this->handleCrudOperation(
            function () use ($gateway) {
                $gateway->is_active = !$gateway->is_active;
                $gateway->save();

                return $gateway;
            },
            $gateway->is_active ? 'Payment gateway activated' : 'Payment gateway deactivated',
            'PaymentGatewayController@toggle'
        );
    }

    /**
     * Test gateway connection.
     */
    public function test(PaymentGateway $gateway): RedirectResponse
    {
        $this->authorize('update', $gateway);

        return $this->handleCrudOperation(
            function () use ($gateway) {
                // Basic validation of gateway configuration
                $config = $gateway->configuration ?? [];
                
                // Check if required fields are present based on gateway type
                $requiredFields = match($gateway->type) {
                    'stripe' => ['api_key', 'api_secret'],
                    'bkash' => ['app_key', 'app_secret', 'username', 'password'],
                    'nagad' => ['merchant_id', 'merchant_key'],
                    'sslcommerz' => ['store_id', 'store_password'],
                    default => ['api_key'],
                };

                foreach ($requiredFields as $field) {
                    if (empty($config[$field])) {
                        throw new \Exception("Missing required field: {$field}");
                    }
                }

                // Note: Actual API connection testing would require gateway-specific implementations
                // This is a basic configuration validation
                return [
                    'status' => 'success',
                    'message' => 'Gateway configuration validated successfully',
                ];
            },
            'Gateway configuration validated successfully',
            'PaymentGatewayController@test'
        );
    }

    /**
     * Encrypt sensitive configuration fields.
     *
     * @param array $config
     * @return array
     */
    private function encryptSensitiveConfig(array $config): array
    {
        $sensitiveKeys = ['api_secret', 'private_key', 'webhook_secret'];

        foreach ($sensitiveKeys as $key) {
            if (isset($config[$key]) && !empty($config[$key])) {
                $config[$key] = encrypt($config[$key]);
            }
        }

        return $config;
    }

    /**
     * Mask sensitive configuration fields for display.
     *
     * @param array $config
     * @return array
     */
    private function maskSensitiveConfig(array $config): array
    {
        $sensitiveKeys = ['api_secret', 'private_key', 'webhook_secret'];

        foreach ($sensitiveKeys as $key) {
            if (isset($config[$key]) && !empty($config[$key])) {
                try {
                    // Try to decrypt first
                    $decrypted = decrypt($config[$key]);
                    // Mask with asterisks
                    $config[$key] = str_repeat('*', min(strlen($decrypted), 20));
                } catch (\Exception $e) {
                    // If decryption fails, just mask
                    $config[$key] = str_repeat('*', 20);
                }
            }
        }

        return $config;
    }
}
