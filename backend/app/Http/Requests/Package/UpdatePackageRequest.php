<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150', Rule::unique('packages', 'name')->ignore($this->route('package'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_free' => ['sometimes', 'boolean'],
            'requires_payment' => ['sometimes', 'boolean'],
            'auto_publish' => ['sometimes', 'boolean'],
            'duration_days' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_photos' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_guests' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'features' => ['sometimes', 'nullable', 'array'],
            'features.*' => ['string'],
            'limits' => ['sometimes', 'nullable', 'array'],
            'limits.max_active_invitations' => ['nullable', 'integer', 'min:1'],
            'limits.max_visitors' => ['nullable', 'integer', 'min:1'],
            'limits.watermark' => ['nullable', 'boolean'],
            'limits.music' => ['nullable', 'boolean'],
            'limits.video' => ['nullable', 'boolean'],
            'limits.qr_gift' => ['nullable', 'boolean'],
            'limits.template_scope' => ['nullable', 'string', 'in:free,all'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama paket sudah ada.',
            'price.numeric' => 'Harga paket harus berupa angka.',
        ];
    }
}
