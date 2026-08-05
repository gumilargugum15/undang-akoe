<?php

namespace App\Http\Requests\Theme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_category_id' => ['sometimes', 'exists:theme_categories,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'thumbnail' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'banner_preview' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'version' => ['sometimes', 'nullable', 'string', 'max:20'],
            'author' => ['sometimes', 'nullable', 'string', 'max:150'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'type' => ['sometimes', Rule::in(['free', 'premium'])],
            'price' => ['sometimes', 'required_if:type,premium', 'nullable', 'numeric', 'min:0'],
            'supports_dark_mode' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],

            // If `config` is being replaced at all, still require the full shape — a partial
            // config would silently break tokens/fonts the frontend expects to always be present.
            'config' => ['sometimes', 'array'],
            'config.ornament' => ['required_with:config', 'string'],
            'config.reveal' => ['required_with:config', 'string'],
            'config.radius' => ['required_with:config', 'string'],
            'config.cardRadius' => ['required_with:config', 'string'],
            'config.shadow' => ['required_with:config', 'string'],
            'config.buttonShadow' => ['required_with:config', 'string'],
            'config.letterSpacing' => ['required_with:config', 'string'],
            'config.headWeight' => ['required_with:config', 'string'],
            'config.fonts' => ['required_with:config', 'array'],
            'config.fonts.head' => ['required_with:config', 'string'],
            'config.fonts.body' => ['required_with:config', 'string'],
            'config.fonts.script' => ['required_with:config', 'string'],
            'config.tokens' => ['required_with:config', 'array'],
            'config.tokens.bg' => ['required_with:config', 'string'],
            'config.tokens.bgAlt' => ['required_with:config', 'string'],
            'config.tokens.surface' => ['required_with:config', 'string'],
            'config.tokens.primary' => ['required_with:config', 'string'],
            'config.tokens.primaryFg' => ['required_with:config', 'string'],
            'config.tokens.secondary' => ['required_with:config', 'string'],
            'config.tokens.accent' => ['required_with:config', 'string'],
            'config.tokens.text' => ['required_with:config', 'string'],
            'config.tokens.muted' => ['required_with:config', 'string'],
            'config.tokens.border' => ['required_with:config', 'string'],
            'config.swatch' => ['required_with:config', 'array', 'min:1'],
            'config.swatch.*' => ['string'],
            'config.texture' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'theme_category_id.exists' => 'Kategori tema tidak ditemukan.',
            'price.required_if' => 'Harga wajib diisi untuk tema premium.',
            'config.*.required_with' => 'Konfigurasi tema tidak lengkap — beberapa properti wajib belum diisi.',
        ];
    }
}
