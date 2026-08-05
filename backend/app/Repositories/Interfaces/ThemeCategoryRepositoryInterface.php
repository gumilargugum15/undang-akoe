<?php

namespace App\Repositories\Interfaces;

use App\Models\ThemeCategory;
use Illuminate\Database\Eloquent\Collection;

interface ThemeCategoryRepositoryInterface
{
    /**
     * @return Collection<int, ThemeCategory>
     */
    public function all(): Collection;

    public function create(array $data): ThemeCategory;

    public function update(ThemeCategory $category, array $data): ThemeCategory;

    public function delete(ThemeCategory $category): void;
}
