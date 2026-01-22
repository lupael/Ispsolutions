<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNetworkUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'admin', 'manager', 'staff', 'operator', 'sub-operator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:network_users,username',
            'password' => 'required|string|min:6',
            'package_id' => 'required|exists:packages,id',
            'service_type' => 'required|in:pppoe,hotspot,static_ip',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'installation_address' => 'nullable|string|max:500',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'mikrotik_router_id' => 'nullable|exists:mikrotik_routers,id',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'The username field is required.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'The selected package is invalid.',
            'service_type.required' => 'Please select a service type.',
            'customer_name.required' => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone number is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'mac_address.regex' => 'Please enter a valid MAC address (e.g., 00:11:22:33:44:55).',
            'expiry_date.after_or_equal' => 'Expiry date must be after or equal to start date.',
        ];
    }
}
