<?php

namespace App\Http\Requests\Invitation;

use App\Models\Invitation;
use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'theme_id' => ['required', Theme::selectableRule($this->user()->isAdmin())],
            'package_id' => [
                'nullable',
                Rule::exists('packages', 'id')->where('is_active', true),
            ],
            'event_category' => ['required', Rule::in(Invitation::EVENT_CATEGORIES)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul undangan wajib diisi.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'theme_id.required' => 'Tema wajib dipilih.',
            'theme_id.exists' => 'Tema yang dipilih tidak tersedia.',
            'package_id.exists' => 'Paket yang dipilih tidak tersedia.',
            'event_category.required' => 'Kategori acara wajib dipilih.',
            'event_category.in' => 'Kategori acara tidak valid.',
        ];
    }
}
