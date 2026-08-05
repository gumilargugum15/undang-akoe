<?php

namespace App\Http\Requests\Theme;

use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_category_id' => ['required', 'exists:theme_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'banner_preview' => ['nullable', 'image', 'max:4096'],
            'version' => ['nullable', 'string', 'max:20'],
            'author' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'type' => ['required', Rule::in(['free', 'premium'])],
            'price' => ['required_if:type,premium', 'nullable', 'numeric', 'min:0'],
            'supports_dark_mode' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            // Mirrors the exact InvitationTheme shape the frontend already renders (see
            // frontend/src/lib/themes.ts and ThemeSeeder) — required here so a badly-shaped theme
            // can't be saved and only break the public invitation page's render later.
            'config' => ['required', 'array'],
            'config.ornament' => ['required', 'string'],
            'config.reveal' => ['required', 'string'],
            'config.radius' => ['required', 'string'],
            'config.cardRadius' => ['required', 'string'],
            'config.shadow' => ['required', 'string'],
            'config.buttonShadow' => ['required', 'string'],
            'config.letterSpacing' => ['required', 'string'],
            'config.headWeight' => ['required', 'string'],
            'config.fonts' => ['required', 'array'],
            'config.fonts.head' => ['required', 'string'],
            'config.fonts.body' => ['required', 'string'],
            'config.fonts.script' => ['required', 'string'],
            'config.tokens' => ['required', 'array'],
            'config.tokens.bg' => ['required', 'string'],
            'config.tokens.bgAlt' => ['required', 'string'],
            'config.tokens.surface' => ['required', 'string'],
            'config.tokens.primary' => ['required', 'string'],
            'config.tokens.primaryFg' => ['required', 'string'],
            'config.tokens.secondary' => ['required', 'string'],
            'config.tokens.accent' => ['required', 'string'],
            'config.tokens.text' => ['required', 'string'],
            'config.tokens.muted' => ['required', 'string'],
            'config.tokens.border' => ['required', 'string'],
            'config.swatch' => ['required', 'array', 'min:1'],
            'config.swatch.*' => ['string'],
            'config.texture' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'theme_category_id.required' => 'Kategori tema wajib dipilih.',
            'theme_category_id.exists' => 'Kategori tema tidak ditemukan.',
            'name.required' => 'Nama tema wajib diisi.',
            'type.required' => 'Tipe tema (free/premium) wajib dipilih.',
            'price.required_if' => 'Harga wajib diisi untuk tema premium.',
            'config.required' => 'Konfigurasi tampilan tema wajib diisi.',
            'config.*.required' => 'Konfigurasi tema tidak lengkap — beberapa properti wajib belum diisi.',
        ];
    }
}
