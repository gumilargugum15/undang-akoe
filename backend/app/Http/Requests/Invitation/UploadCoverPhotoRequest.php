<?php

namespace App\Http\Requests\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class UploadCoverPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Foto sampul wajib diunggah.',
            'photo.image' => 'Foto sampul harus berupa gambar.',
            'photo.max' => 'Ukuran foto sampul maksimal 4MB.',
        ];
    }
}
