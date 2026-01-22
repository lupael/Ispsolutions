<?php

declare(strict_types=1);

namespace App\Http\Requests\HotspotSelfSignup;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'otp_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required' => 'Mobile number is required',
            'otp_code.required' => 'OTP code is required',
            'otp_code.size' => 'OTP must be 6 digits',
            'otp_code.regex' => 'OTP must contain only numbers',
        ];
    }
}
