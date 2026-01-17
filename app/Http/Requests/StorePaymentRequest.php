<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin') || 
               auth()->user()->hasRole('super-admin') ||
               auth()->id() === (int) $this->input('user_id');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'payment_gateway_id' => ['nullable', 'exists:payment_gateways,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:gateway,card,cash,bank_transfer'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Payment method must be one of: gateway, card, cash, or bank transfer.',
        ];
    }
}
