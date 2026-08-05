<?php

namespace App\Http\Requests\LoveStory;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoveStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'story_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul cerita wajib diisi.',
            'story_date.date' => 'Format tanggal tidak valid.',
        ];
    }
}
