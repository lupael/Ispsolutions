<?php

declare(strict_types=1);

namespace App\Http\Requests\HotspotSelfSignup;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
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
            'package_id' => [
                'required',
                'integer',
                'exists:packages,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required' => 'Mobile number is required',
            'mobile_number.regex' => 'Mobile number must be 10-15 digits',
            'package_id.required' => 'Please select a package',
            'package_id.exists' => 'Selected package is invalid',
        ];
    }

    public function prepareForValidation(): void
    {
        // Clean mobile number
        if ($this->mobile_number) {
            $cleaned = preg_replace('/[^0-9]/', '', $this->mobile_number);
            $this->merge(['mobile_number' => $cleaned]);
        }
    }
}
