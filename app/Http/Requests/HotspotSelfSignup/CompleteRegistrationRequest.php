<?php

declare(strict_types=1);

namespace App\Http\Requests\HotspotSelfSignup;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public access
    }

    public function rules(): array
    {
        return [
            'mobile_number' => [
                'required',
                'string',
                'regex:/^[0-9]{10,15}$/',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required' => 'Mobile number is required',
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.email' => 'Please enter a valid email address',
        ];
    }
}
