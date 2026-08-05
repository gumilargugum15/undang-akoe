<?php

namespace App\Services;

use App\Models\ThemeCategory;
use App\Repositories\Interfaces\ThemeCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ThemeCategoryService
{
    public function __construct(
        private readonly ThemeCategoryRepositoryInterface $categories,
    ) {}

    /**
     * @return Collection<int, ThemeCategory>
     */
    public function list(): Collection
    {
        return $this->categories->all();
    }

    public function create(array $data): ThemeCategory
    {
        return $this->categories->create($data);
    }

    public function update(ThemeCategory $category, array $data): ThemeCategory
    {
        return $this->categories->update($category, $data);
    }

    public function delete(ThemeCategory $category): void
    {
        if ($category->themes()->withTrashed()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Kategori tidak dapat dihapus karena masih dipakai oleh satu atau lebih tema.'],
            ]);
        }

        $this->categories->delete($category);
    }
}
