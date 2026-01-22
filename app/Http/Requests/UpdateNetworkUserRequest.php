<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNetworkUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'admin', 'manager', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $networkUserId = $this->route('network_user') ?? $this->route('id');
        
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('network_users', 'username')->ignore($networkUserId),
            ],
            'password' => 'nullable|string|min:6',
            'package_id' => 'required|exists:service_packages,id',
            'user_id' => 'required|exists:users,id',
            'service_type' => 'required|in:pppoe,hotspot,static_ip',
            'ip_address' => [
                'nullable',
                'ip',
                Rule::unique('network_users', 'ip_address')->ignore($networkUserId),
            ],
            'mac_address' => [
                'nullable',
                'string',
                'max:17',
                Rule::unique('network_users', 'mac_address')->ignore($networkUserId),
            ],
            'mikrotik_router_id' => 'nullable|exists:mikrotik_routers,id',
            'nas_id' => 'nullable|exists:nas,id',
            'is_active' => 'boolean',
            'auto_renew' => 'boolean',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.min' => 'Password must be at least 6 characters.',
            'package_id.required' => 'Please select a service package.',
            'package_id.exists' => 'The selected package is invalid.',
            'user_id.required' => 'Customer is required.',
            'user_id.exists' => 'The selected customer is invalid.',
            'service_type.required' => 'Service type is required.',
            'service_type.in' => 'Invalid service type selected.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'ip_address.unique' => 'This IP address is already assigned.',
            'mac_address.unique' => 'This MAC address is already registered.',
        ];
    }
}
