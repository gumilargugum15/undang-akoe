<?php

namespace App\Http\Requests\DigitalEnvelope;

use App\Models\DigitalEnvelope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDigitalEnvelopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('envelope')?->type);

        return [
            'type' => ['sometimes', Rule::in(DigitalEnvelope::TYPES)],
            'provider_name' => [
                'sometimes', 'nullable', 'string', 'max:100',
                $type === 'ewallet' ? Rule::in(DigitalEnvelope::EWALLET_PROVIDERS) : 'nullable',
            ],
            'account_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'account_holder' => ['sometimes', 'nullable', 'string', 'max:150'],
            'qr_image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Jenis amplop tidak valid.',
            'provider_name.in' => 'Pilih salah satu: Dana, OVO, GoPay, atau ShopeePay.',
            'qr_image.image' => 'File QRIS harus berupa gambar.',
        ];
    }
}
