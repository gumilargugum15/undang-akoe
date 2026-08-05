<?php

namespace App\Repositories;

use App\Models\ThemeCategory;
use App\Repositories\Interfaces\ThemeCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ThemeCategoryRepository implements ThemeCategoryRepositoryInterface
{
    public function all(): Collection
    {
        return ThemeCategory::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function create(array $data): ThemeCategory
    {
        return ThemeCategory::create($data);
    }

    public function update(ThemeCategory $category, array $data): ThemeCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(ThemeCategory $category): void
    {
        $category->delete();
    }
}
