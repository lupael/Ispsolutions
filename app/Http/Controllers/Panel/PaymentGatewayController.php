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
                
                // Validate gateway type
                $validTypes = [
                    PaymentGateway::TYPE_STRIPE,
                    PaymentGateway::TYPE_BKASH,
                    PaymentGateway::TYPE_NAGAD,
                    PaymentGateway::TYPE_SSLCOMMERZ,
                    PaymentGateway::TYPE_PAYPAL,
                    PaymentGateway::TYPE_RAZORPAY,
                ];
                
                if (!in_array($gateway->type, $validTypes, true)) {
                    throw new \Exception("Invalid gateway type: {$gateway->type}");
                }
                
                // Check if required fields are present based on gateway type
                $requiredFields = match($gateway->type) {
                    PaymentGateway::TYPE_STRIPE => ['api_key', 'api_secret'],
                    PaymentGateway::TYPE_BKASH => ['app_key', 'app_secret', 'username', 'password'],
                    PaymentGateway::TYPE_NAGAD => ['merchant_id', 'merchant_key'],
                    PaymentGateway::TYPE_SSLCOMMERZ => ['store_id', 'store_password'],
                    default => ['api_key'],
                };

                foreach ($requiredFields as $field) {
                    // Check field exists and is not empty
                    if (!array_key_exists($field, $config)) {
                        throw new \Exception("Missing required field: {$field}");
                    }
                    
                    $value = $config[$field];
                    
                    // Validate type
                    if (!is_string($value)) {
                        throw new \Exception("Invalid type for field: {$field}");
                    }
                    
                    // Trim and validate not empty
                    $value = trim($value);
                    if ($value === '') {
                        throw new \Exception("Missing required field: {$field}");
                    }
                    
                    // Basic length validation
                    $len = strlen($value);
                    if ($len < 4 || $len > 255) {
                        throw new \Exception("Invalid value length for field: {$field}");
                    }
                    
                    // Format validation based on gateway and field
                    $this->validateFieldFormat($gateway->type, $field, $value);
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

    /**
     * Validate field format based on gateway type and field name.
     *
     * @param string $gatewayType
     * @param string $field
     * @param string $value
     * @throws \Exception
     */
    private function validateFieldFormat(string $gatewayType, string $field, string $value): void
    {
        switch ($gatewayType) {
            case PaymentGateway::TYPE_STRIPE:
                if (in_array($field, ['api_key', 'api_secret'], true)) {
                    if (!preg_match('/^[A-Za-z0-9_\-]{8,255}$/', $value)) {
                        throw new \Exception("Invalid format for Stripe {$field}");
                    }
                }
                break;
            
            case PaymentGateway::TYPE_BKASH:
                if (in_array($field, ['app_key', 'app_secret', 'username', 'password'], true)) {
                    if (!preg_match('/^[\S]{4,255}$/', $value)) {
                        throw new \Exception("Invalid format for bKash {$field}");
                    }
                }
                break;
            
            case PaymentGateway::TYPE_NAGAD:
                if ($field === 'merchant_id' && !preg_match('/^[0-9]{4,30}$/', $value)) {
                    throw new \Exception("Invalid format for Nagad merchant_id");
                }
                if ($field === 'merchant_key' && !preg_match('/^[A-Za-z0-9]{8,255}$/', $value)) {
                    throw new \Exception("Invalid format for Nagad merchant_key");
                }
                break;
            
            case PaymentGateway::TYPE_SSLCOMMERZ:
                if ($field === 'store_id' && !preg_match('/^[A-Za-z0-9_\-]{4,100}$/', $value)) {
                    throw new \Exception("Invalid format for SSLCommerz store_id");
                }
                if ($field === 'store_password' && !preg_match('/^[\S]{4,255}$/', $value)) {
                    throw new \Exception("Invalid format for SSLCommerz store_password");
                }
                break;
            
            default:
                // Generic API key validation for other gateways
                if ($field === 'api_key' && !preg_match('/^[A-Za-z0-9_\-]{8,255}$/', $value)) {
                    throw new \Exception("Invalid format for api_key");
                }
                break;
        }
    }
}
