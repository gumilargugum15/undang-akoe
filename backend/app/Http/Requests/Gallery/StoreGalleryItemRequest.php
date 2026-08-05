<?php

namespace App\Http\Requests\Gallery;

use App\Models\Gallery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Gallery::TYPES)],
            'file' => [
                Rule::requiredIf(in_array($this->input('type'), ['photo', 'video_mp4'], true)),
                'nullable',
                ...($this->input('type') === 'video_mp4'
                    ? ['mimes:mp4,mov', 'max:20480']
                    : ['image', 'max:5120']),
            ],
            'external_url' => [
                Rule::requiredIf($this->input('type') === 'video_youtube'),
                'nullable',
                'url',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'video_youtube' && $value
                        && ! preg_match('/(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)/i', $value)) {
                        $fail('Tautan harus berupa URL YouTube yang valid.');
                    }
                },
            ],
            'caption' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis galeri wajib dipilih.',
            'type.in' => 'Jenis galeri tidak valid.',
            'file.required' => 'File wajib diunggah untuk jenis ini.',
            'file.image' => 'File harus berupa gambar.',
            'file.mimes' => 'Video harus berformat MP4 atau MOV.',
            'file.max' => 'Ukuran file melebihi batas maksimal.',
            'external_url.required' => 'Tautan YouTube wajib diisi.',
            'external_url.url' => 'Tautan tidak valid.',
        ];
    }
}
