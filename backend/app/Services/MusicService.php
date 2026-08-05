<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Music;
use App\Repositories\Interfaces\MusicRepositoryInterface;
use Illuminate\Validation\ValidationException;

class MusicService
{
    public function __construct(
        private readonly MusicRepositoryInterface $music,
    ) {}

    public function getForInvitation(Invitation $invitation): ?Music
    {
        return $this->music->forInvitation($invitation);
    }

    /**
     * $newFilePath is the already-stored disk path for a freshly uploaded file (or null if no
     * new file was uploaded this request) — file storage/deletion is an I/O concern the
     * controller handles; this only decides which value belongs in which column.
     */
    public function upsert(Invitation $invitation, array $data, ?string $newFilePath): Music
    {
        $existing = $this->music->forInvitation($invitation);

        if ($data['source'] === 'upload') {
            $data['file_path'] = $newFilePath ?? $existing?->file_path;
            $data['external_url'] = null;

            if (! $data['file_path']) {
                throw ValidationException::withMessages([
                    'file' => ['File musik wajib diunggah untuk sumber "upload".'],
                ]);
            }
        } else {
            $data['file_path'] = null;
        }

        return $this->music->upsert($invitation, $data);
    }

    public function delete(Invitation $invitation): void
    {
        $this->music->delete($invitation);
    }
}
