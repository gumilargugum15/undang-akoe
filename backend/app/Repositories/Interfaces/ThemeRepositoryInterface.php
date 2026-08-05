<?php

namespace App\Repositories\Interfaces;

use App\Models\Theme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ThemeRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Theme;

    public function update(Theme $theme, array $data): Theme;

    public function delete(Theme $theme): void;

    public function duplicate(Theme $theme): Theme;
}
