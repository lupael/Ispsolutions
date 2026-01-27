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
            // Network authentication fields
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'package_id' => 'required|exists:packages,id',
            'service_type' => 'required|in:pppoe,hotspot,static_ip',
            
            // Customer information fields (mapped to User model)
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255|unique:users,email',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            
            // Network service fields
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
            'customer_email.unique' => 'This email is already registered.',
            'customer_phone.required' => 'Customer phone number is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'mac_address.regex' => 'Please enter a valid MAC address (e.g., 00:11:22:33:44:55).',
            'expiry_date.after_or_equal' => 'Expiry date must be after or equal to start date.',
        ];
    }

    /**
     * Transform the validated data for use with User model.
     * Maps customer_* fields to standard User fields.
     *
     * Note: Fields like customer_phone, customer_address, installation_address, 
     * mikrotik_router_id, and notes are validated but not stored in users table.
     * They should be handled separately (e.g., in a related customer_details table)
     * or stored via custom logic in the controller.
     *
     * @return array<string, mixed>
     */
    public function transformForUserModel(): array
    {
        $validated = $this->validated();
        
        return [
            // Map customer fields to User model fields
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'] ?? null,
            
            // Network authentication fields
            'username' => $validated['username'],
            'radius_password' => $validated['password'],
            'service_type' => $validated['service_type'],
            'service_package_id' => $validated['package_id'],
            
            // Network service fields (from User migration)
            'ip_address' => $validated['ip_address'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            
            // Zone assignment
            'zone_id' => null, // Can be set later if needed
            
            // Set as customer (operator_level = 100)
            'operator_level' => 100,
            'status' => ($validated['is_active'] ?? true) ? 'active' : 'inactive',
        ];
    }
    
    /**
     * Get additional fields that are validated but not in User model.
     * These should be handled separately by the controller.
     *
     * @return array<string, mixed>
     */
    public function getAdditionalFields(): array
    {
        $validated = $this->validated();
        
        return [
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'] ?? null,
            'installation_address' => $validated['installation_address'] ?? null,
            'mikrotik_router_id' => $validated['mikrotik_router_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
        ];
    }
}
