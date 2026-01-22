<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotspotUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('hotspotUser'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hotspotUserId = $this->route('hotspotUser')->id ?? null;
        
        return [
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('hotspot_users', 'phone_number')->ignore($hotspotUserId),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('hotspot_users', 'username')->ignore($hotspotUserId),
            ],
            'password' => 'nullable|string|min:6|max:50',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,suspended,expired,pending',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.unique' => 'This phone number is already registered.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.min' => 'Password must be at least 6 characters.',
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'The selected package is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}
