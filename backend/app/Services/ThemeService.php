<?php

namespace App\Services;

use App\Models\Theme;
use App\Repositories\Interfaces\ThemeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ThemeService
{
    public function __construct(
        private readonly ThemeRepositoryInterface $themes,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->themes->paginateForAdmin($filters, (int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): Theme
    {
        $theme = $this->themes->create($data);
        $this->flushCache();

        return $theme;
    }

    public function update(Theme $theme, array $data): Theme
    {
        $theme = $this->themes->update($theme, $data);
        $this->flushCache();

        return $theme;
    }

    public function delete(Theme $theme): void
    {
        if ($theme->invitations()->exists()) {
            throw ValidationException::withMessages([
                'theme' => ['Tema tidak dapat dihapus karena masih dipakai oleh satu atau lebih undangan. Nonaktifkan saja jika ingin menyembunyikannya dari pilihan customer.'],
            ]);
        }

        $this->themes->delete($theme);
        $this->flushCache();
    }

    public function publish(Theme $theme): Theme
    {
        $theme = $this->themes->update($theme, ['status' => 'published']);
        $this->flushCache();

        return $theme;
    }

    public function unpublish(Theme $theme): Theme
    {
        $theme = $this->themes->update($theme, ['status' => 'draft']);
        $this->flushCache();

        return $theme;
    }

    public function duplicate(Theme $theme): Theme
    {
        $copy = $this->themes->duplicate($theme);
        $this->flushCache();

        return $copy;
    }

    /**
     * See Phase 14's ThemeController::index() note: mutating endpoints must
     * invalidate both cached listings now that they actually exist.
     */
    private function flushCache(): void
    {
        Cache::forget('themes:index:admin');
        Cache::forget('themes:index:public');
    }
}
