<?php

namespace App\Http\Requests\Honoree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHonoreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_label' => ['sometimes', 'string', 'max:100'],
            'nickname' => ['sometimes', 'string', 'max:100'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'parent_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'instagram_handle' => ['sometimes', 'nullable', 'string', 'max:100'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'meta' => ['sometimes', 'nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_label.string' => 'Label peran tidak valid.',
            'nickname.string' => 'Nama panggilan tidak valid.',
        ];
    }
}
