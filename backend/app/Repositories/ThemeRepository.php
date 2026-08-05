<?php

namespace App\Repositories;

use App\Models\Theme;
use App\Repositories\Interfaces\ThemeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ThemeRepository implements ThemeRepositoryInterface
{
    public function paginateForAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Theme::query()->with('category');

        if (! empty($filters['theme_category_id'])) {
            $query->where('theme_category_id', $filters['theme_category_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    public function create(array $data): Theme
    {
        return Theme::create($data);
    }

    public function update(Theme $theme, array $data): Theme
    {
        $theme->update($data);

        return $theme->fresh(['category']);
    }

    public function delete(Theme $theme): void
    {
        $theme->delete();
    }

    public function duplicate(Theme $theme): Theme
    {
        $copy = $theme->replicate(['uuid', 'slug']);
        $copy->name = "{$theme->name} (Copy)";
        $copy->slug = Str::slug($copy->name).'-'.Str::random(6);
        $copy->status = 'draft';
        $copy->save();

        return $copy->fresh(['category']);
    }
}
