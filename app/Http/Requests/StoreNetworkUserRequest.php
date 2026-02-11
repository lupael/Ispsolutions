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
        return $this->user()->can('manage-customers');
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
            'customer_email' => 'required|email|max:255|unique:users,email',
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
            'customer_email.required' => 'Customer email is required.',
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
     * mikrotik_router_id, notes, and start_date are validated but not stored in users table.
     * 
     * These fields should be:
     * 1. Stored in a related customer_details/customer_metadata table, or
     * 2. Handled by custom controller logic after user creation, or
     * 3. Stored in a JSON meta field on the User model if needed
     * 
     * For now, access them via getAdditionalFields() method and handle in the controller.
     *
     * Security Note: radius_password is stored as plain-text for RADIUS compatibility.
     * Most RADIUS servers require cleartext or specific hash formats (PAP, CHAP, MS-CHAP).
     * If your RADIUS server supports hashed passwords, consider implementing encryption
     * at rest or using RADIUS-compatible hashing algorithms.
     *
     * @return array<string, mixed>
     */
    public function transformForUserModel(): array
    {
        $validated = $this->validated();
        
        return [
            // Map customer fields to User model fields
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'],
            
            // Network authentication fields
            'username' => $validated['username'],
            'password' => $validated['password'], // Laravel will hash via User::casts()
            'radius_password' => $validated['password'], // Store plain for RADIUS
            'service_type' => $validated['service_type'],
            'service_package_id' => $validated['package_id'],
            
            // Network service fields (from User migration)
            'ip_address' => $validated['ip_address'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            
            // Zone assignment (can be set by controller if needed based on business logic)
            // For example: based on area, router assignment, or package requirements
            'zone_id' => null,
            
            // Set as customer (is_subscriber = true)
            'is_subscriber' => true,
            'operator_level' => null,
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
