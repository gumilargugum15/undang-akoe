<?php

namespace App\Http\Requests\Music;

use App\Models\Music;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertMusicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', Rule::in(Music::SOURCES)],
            'title' => ['nullable', 'string', 'max:150'],
            'artist' => ['nullable', 'string', 'max:150'],
            'file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'],
            'external_url' => ['nullable', 'url', 'max:1000', 'required_if:source,spotify,youtube_music'],
            'autoplay' => ['nullable', 'boolean'],
            'is_loop' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Sumber musik wajib dipilih.',
            'file.mimes' => 'Format file harus mp3, wav, ogg, atau m4a.',
            'file.max' => 'Ukuran file musik maksimal 10MB.',
            'external_url.required_if' => 'Tautan lagu wajib diisi untuk sumber Spotify/YouTube Music.',
            'external_url.url' => 'Tautan lagu tidak valid.',
        ];
    }
}
