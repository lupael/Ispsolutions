<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateCardsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'denomination' => ['required', 'numeric', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.max' => 'You can generate a maximum of 1000 cards at once.',
            'denomination.min' => 'Card denomination must be at least 1.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }
}
