<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMikrotikRouterRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'api_port' => 'nullable|integer|min:1|max:65535',
            'is_active' => 'boolean',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Router name is required.',
            'host.required' => 'Router host/IP is required.',
            'port.required' => 'Port number is required.',
            'port.min' => 'Port must be between 1 and 65535.',
            'port.max' => 'Port must be between 1 and 65535.',
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ];
    }
}
