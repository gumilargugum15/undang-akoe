<?php

namespace App\Http\Requests\Honoree;

use Illuminate\Foundation\Http\FormRequest;

class StoreHonoreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_label' => ['required', 'string', 'max:100'],
            'nickname' => ['required', 'string', 'max:100'],
            'full_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'instagram_handle' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_label.required' => 'Label peran wajib diisi.',
            'nickname.required' => 'Nama panggilan wajib diisi.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
        ];
    }
}
