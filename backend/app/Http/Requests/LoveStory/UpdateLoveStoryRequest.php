<?php

namespace App\Http\Requests\LoveStory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoveStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:150'],
            'story_date' => ['sometimes', 'nullable', 'date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'photo' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'story_date.date' => 'Format tanggal tidak valid.',
        ];
    }
}
