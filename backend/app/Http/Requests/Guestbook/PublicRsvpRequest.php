<?php

namespace App\Http\Requests\Guestbook;

use App\Models\Guestbook;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'attendance' => ['required', Rule::in(Guestbook::ATTENDANCES)],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'Nama wajib diisi.',
            'attendance.required' => 'Konfirmasi kehadiran wajib dipilih.',
            'attendance.in' => 'Pilihan kehadiran tidak valid.',
            'guest_count.min' => 'Jumlah tamu minimal 1 orang.',
            'guest_count.max' => 'Jumlah tamu maksimal 10 orang.',
            'message.required' => 'Ucapan & doa wajib diisi.',
        ];
    }
}
