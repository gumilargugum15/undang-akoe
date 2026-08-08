<?php

namespace App\Http\Requests\PaymentSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banks' => ['nullable', 'array'],
            'banks.*.bank' => ['required_with:banks', 'string', 'max:100'],
            'banks.*.account_number' => ['required_with:banks', 'string', 'max:50'],
            'banks.*.account_name' => ['required_with:banks', 'string', 'max:150'],
            'dana' => ['nullable', 'array'],
            'dana.number' => ['nullable', 'string', 'max:30'],
            'dana.account_name' => ['nullable', 'string', 'max:150'],
            'gopay' => ['nullable', 'array'],
            'gopay.number' => ['nullable', 'string', 'max:30'],
            'gopay.account_name' => ['nullable', 'string', 'max:150'],
            'qris_merchant_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'banks.*.bank.required_with' => 'Nama bank wajib diisi.',
            'banks.*.account_number.required_with' => 'Nomor rekening wajib diisi.',
            'banks.*.account_name.required_with' => 'Nama pemilik rekening wajib diisi.',
        ];
    }
}
