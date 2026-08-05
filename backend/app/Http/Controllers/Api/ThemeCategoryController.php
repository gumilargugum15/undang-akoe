<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThemeCategory\StoreThemeCategoryRequest;
use App\Http\Requests\ThemeCategory\UpdateThemeCategoryRequest;
use App\Models\ThemeCategory;
use App\Services\ThemeCategoryService;
use Illuminate\Http\JsonResponse;

class ThemeCategoryController extends Controller
{
    public function __construct(
        private readonly ThemeCategoryService $categories,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->categories->list()->map(fn (ThemeCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'sort_order' => $c->sort_order,
            ]),
        ]);
    }

    public function store(StoreThemeCategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->validated());

        return response()->json([
            'message' => 'Kategori tema berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    public function update(UpdateThemeCategoryRequest $request, ThemeCategory $themeCategory): JsonResponse
    {
        $category = $this->categories->update($themeCategory, $request->validated());

        return response()->json([
            'message' => 'Kategori tema berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    public function destroy(ThemeCategory $themeCategory): JsonResponse
    {
        $this->categories->delete($themeCategory);

        return response()->json([
            'message' => 'Kategori tema berhasil dihapus.',
        ]);
    }
}
