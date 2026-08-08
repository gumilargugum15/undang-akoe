<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:packages,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'requires_payment' => ['nullable', 'boolean'],
            'auto_publish' => ['nullable', 'boolean'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'max_photos' => ['nullable', 'integer', 'min:1'],
            'max_guests' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'limits' => ['nullable', 'array'],
            'limits.max_active_invitations' => ['nullable', 'integer', 'min:1'],
            'limits.max_visitors' => ['nullable', 'integer', 'min:1'],
            'limits.watermark' => ['nullable', 'boolean'],
            'limits.music' => ['nullable', 'boolean'],
            'limits.video' => ['nullable', 'boolean'],
            'limits.qr_gift' => ['nullable', 'boolean'],
            'limits.template_scope' => ['nullable', 'string', 'in:free,all'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama paket wajib diisi.',
            'name.unique' => 'Nama paket sudah ada.',
            'price.required' => 'Harga paket wajib diisi.',
            'price.numeric' => 'Harga paket harus berupa angka.',
        ];
    }
}
