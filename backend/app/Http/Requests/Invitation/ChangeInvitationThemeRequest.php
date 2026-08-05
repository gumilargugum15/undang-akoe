<?php

namespace App\Http\Requests\Invitation;

use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;

class ChangeInvitationThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_id' => ['required', Theme::selectableRule($this->user()->isAdmin())],
        ];
    }

    public function messages(): array
    {
        return [
            'theme_id.required' => 'Tema wajib dipilih.',
            'theme_id.exists' => 'Tema yang dipilih tidak tersedia.',
        ];
    }
}
