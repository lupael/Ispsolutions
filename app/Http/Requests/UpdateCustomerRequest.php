<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $userId = $this->route('customer') ?? $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'package_id' => 'required|exists:service_packages,id',
            'installation_date' => 'nullable|date',
            'billing_cycle' => 'required|in:monthly,quarterly,semi-annual,annual',
            'connection_type' => 'required|in:pppoe,hotspot,static_ip',
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
            'name.required' => 'Customer name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.required' => 'Phone number is required.',
            'address.required' => 'Address is required.',
            'package_id.required' => 'Please select a service package.',
            'package_id.exists' => 'The selected package is invalid.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'connection_type.required' => 'Please select a connection type.',
        ];
    }
}
