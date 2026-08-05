<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Client-generated (e.g. a UUID persisted in localStorage) so repeat visits within the
            // same browser count as one unique visitor. Falls back to a random one if omitted.
            'session_id' => ['nullable', 'string', 'max:100'],
            // From document.referrer client-side — more reliable than the Referer header, which
            // privacy-conscious browsers often strip on cross-origin navigation.
            'referrer' => ['nullable', 'string', 'max:500'],
        ];
    }
}
