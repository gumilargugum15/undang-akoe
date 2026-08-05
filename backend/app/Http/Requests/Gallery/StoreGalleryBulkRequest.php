<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:20'],
            'photos.*' => ['image', 'max:5120'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Pilih minimal satu foto untuk diunggah.',
            'photos.max' => 'Maksimal 20 foto dapat diunggah sekaligus.',
            'photos.*.image' => 'Setiap file harus berupa gambar.',
            'photos.*.max' => 'Setiap foto maksimal berukuran 5MB.',
        ];
    }
}
