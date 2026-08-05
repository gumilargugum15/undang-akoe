<?php

namespace App\Http\Requests\Invitation;

use App\Models\Invitation;
use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'theme_id' => ['sometimes', Theme::selectableRule($this->user()->isAdmin())],
            'package_id' => [
                'sometimes', 'nullable',
                Rule::exists('packages', 'id')->where('is_active', true),
            ],
            'event_category' => ['sometimes', Rule::in(Invitation::EVENT_CATEGORIES)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'theme_id.exists' => 'Tema yang dipilih tidak tersedia.',
            'package_id.exists' => 'Paket yang dipilih tidak tersedia.',
            'event_category.in' => 'Kategori acara tidak valid.',
        ];
    }
}
