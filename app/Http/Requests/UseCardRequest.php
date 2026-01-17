<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UseCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'card_number' => ['required', 'string', 'regex:/^RC-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/'],
            'pin' => ['required', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.regex' => 'Invalid card number format.',
            'pin.digits' => 'PIN must be exactly 4 digits.',
        ];
    }
}
