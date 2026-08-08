<?php

namespace App\Http\Requests\PaymentSetting;

use Illuminate\Foundation\Http\FormRequest;

class UploadQrisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qris' => ['required', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'qris.required' => 'Gambar QRIS wajib diunggah.',
            'qris.image' => 'Gambar QRIS harus berupa file gambar.',
            'qris.max' => 'Ukuran gambar QRIS maksimal 2MB.',
        ];
    }
}
