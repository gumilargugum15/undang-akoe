<?php

namespace App\Http\Requests\DigitalEnvelope;

use App\Models\DigitalEnvelope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDigitalEnvelopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['required', Rule::in(DigitalEnvelope::TYPES)],
            'provider_name' => [
                Rule::requiredIf($type !== 'qris'),
                'nullable',
                'string',
                'max:100',
                $type === 'ewallet' ? Rule::in(DigitalEnvelope::EWALLET_PROVIDERS) : 'nullable',
            ],
            'account_number' => [Rule::requiredIf(in_array($type, ['bank', 'ewallet'], true)), 'nullable', 'string', 'max:50'],
            'account_holder' => [Rule::requiredIf(in_array($type, ['bank', 'ewallet'], true)), 'nullable', 'string', 'max:150'],
            'qr_image' => [Rule::requiredIf($type === 'qris'), 'nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis amplop wajib dipilih.',
            'type.in' => 'Jenis amplop tidak valid.',
            'provider_name.required' => 'Nama bank/e-wallet wajib diisi.',
            'provider_name.in' => 'Pilih salah satu: Dana, OVO, GoPay, atau ShopeePay.',
            'account_number.required' => 'Nomor rekening/e-wallet wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
            'qr_image.required' => 'Gambar QRIS wajib diunggah.',
            'qr_image.image' => 'File QRIS harus berupa gambar.',
        ];
    }
}
