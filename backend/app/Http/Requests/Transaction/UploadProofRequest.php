<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof' => ['required', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Unggah bukti pembayaran terlebih dahulu.',
            'proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }
}
