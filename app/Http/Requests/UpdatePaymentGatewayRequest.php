<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentGatewayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $gatewayId = $this->route('gateway') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('payment_gateways')->ignore($gatewayId)],
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'configuration' => 'required|array',
            'configuration.api_key' => 'nullable|string|max:500',
            'configuration.api_secret' => 'nullable|string|max:500',
            'configuration.merchant_id' => 'nullable|string|max:255',
            'configuration.store_id' => 'nullable|string|max:255',
            'configuration.public_key' => 'nullable|string',
            'configuration.private_key' => 'nullable|string',
            'configuration.callback_url' => 'nullable|url|max:500',
            'configuration.webhook_secret' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The gateway name is required.',
            'slug.required' => 'The gateway slug is required.',
            'slug.unique' => 'This gateway slug is already in use.',
            'configuration.required' => 'Gateway configuration is required.',
            'configuration.callback_url.url' => 'Please enter a valid callback URL.',
        ];
    }
}
