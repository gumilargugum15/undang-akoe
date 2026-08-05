<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Theme\StoreThemeRequest;
use App\Http\Requests\Theme\UpdateThemeRequest;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use App\Services\ImageProcessingService;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly ImageProcessingService $images,
    ) {}

    /**
     * Two different shapes behind one endpoint, same pattern as Dashboard:
     * a customer gets the simple cached catalog for the theme picker
     * (active + published only); an admin gets the full paginated,
     * filterable management list (including draft/inactive).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()?->isAdmin()) {
            $themes = $this->themeService->listForAdmin(
                $request->only(['theme_category_id', 'status', 'type', 'search', 'per_page']),
            );

            return response()->json([
                'data' => ThemeResource::collection($themes),
                'meta' => [
                    'current_page' => $themes->currentPage(),
                    'last_page' => $themes->lastPage(),
                    'per_page' => $themes->perPage(),
                    'total' => $themes->total(),
                ],
            ]);
        }

        // Themes only ever change via admin CRUD or the seeder, so a short TTL — not fully
        // event-based invalidation — is the pragmatic tradeoff here; ThemeService flushes both
        // this and the admin-scoped key on every mutation, so staleness tops out at 10 minutes.
        $themes = Cache::remember(
            'themes:index:public',
            now()->addMinutes(10),
            fn () => ThemeResource::collection(
                Theme::query()->with('category')->where('is_active', true)->where('status', 'published')
                    ->orderBy('sort_order')->get(),
            )->resolve(),
        );

        return response()->json(['data' => $themes]);
    }

    /**
     * Also mounted at GET /themes/preview/{theme:slug} — the spec's dedicated
     * preview endpoint is the same read, just bound by slug instead of id.
     */
    public function show(Theme $theme): JsonResponse
    {
        $theme->load('category');

        return response()->json([
            'data' => new ThemeResource($theme),
        ]);
    }

    public function store(StoreThemeRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['thumbnail', 'banner_preview']);
        $theme = $this->themeService->create($data);

        $theme = $this->attachUploads($request, $theme);

        return response()->json([
            'message' => 'Tema berhasil ditambahkan.',
            'data' => new ThemeResource($theme->load('category')),
        ], 201);
    }

    public function update(UpdateThemeRequest $request, Theme $theme): JsonResponse
    {
        $data = $request->safe()->except(['thumbnail', 'banner_preview']);
        $theme = $this->themeService->update($theme, $data);

        $theme = $this->attachUploads($request, $theme);

        return response()->json([
            'message' => 'Tema berhasil diperbarui.',
            'data' => new ThemeResource($theme->load('category')),
        ]);
    }

    public function destroy(Theme $theme): JsonResponse
    {
        $this->themeService->delete($theme);

        return response()->json([
            'message' => 'Tema berhasil dihapus.',
        ]);
    }

    public function publish(Theme $theme): JsonResponse
    {
        $theme = $this->themeService->publish($theme);

        return response()->json([
            'message' => 'Tema berhasil dipublikasikan.',
            'data' => new ThemeResource($theme),
        ]);
    }

    public function unpublish(Theme $theme): JsonResponse
    {
        $theme = $this->themeService->unpublish($theme);

        return response()->json([
            'message' => 'Tema dikembalikan ke status draft.',
            'data' => new ThemeResource($theme),
        ]);
    }

    public function duplicate(Theme $theme): JsonResponse
    {
        $copy = $this->themeService->duplicate($theme);

        return response()->json([
            'message' => 'Tema berhasil diduplikasi.',
            'data' => new ThemeResource($copy),
        ], 201);
    }

    private function attachUploads(Request $request, Theme $theme): Theme
    {
        $updates = [];

        if ($request->hasFile('thumbnail')) {
            if ($theme->thumbnail) {
                Storage::disk('public')->delete($theme->thumbnail);
            }
            $updates['thumbnail'] = $this->images->storePhoto($request->file('thumbnail'), "themes/{$theme->id}", maxWidth: 600);
        }

        if ($request->hasFile('banner_preview')) {
            if ($theme->banner_preview) {
                Storage::disk('public')->delete($theme->banner_preview);
            }
            $updates['banner_preview'] = $this->images->storePhoto($request->file('banner_preview'), "themes/{$theme->id}", maxWidth: 1600);
        }

        return $updates ? $this->themeService->update($theme, $updates) : $theme;
    }
}
