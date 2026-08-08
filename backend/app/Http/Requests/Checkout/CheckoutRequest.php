<?php

namespace App\Http\Requests\Checkout;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'payment_method' => ['required', 'string', Rule::in(Transaction::PAYMENT_METHODS)],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Pilih paket terlebih dahulu.',
            'package_id.exists' => 'Paket tidak ditemukan.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak dikenali.',
        ];
    }
}
