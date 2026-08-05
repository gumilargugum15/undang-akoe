<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\StoreFaqRequest;
use App\Http\Requests\Faq\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqService $faqs,
    ) {}

    /**
     * An admin managing FAQs sees every one (including inactive drafts);
     * the public FAQ page only ever sees active ones.
     */
    public function index(Request $request): JsonResponse
    {
        $activeOnly = ! $request->user()?->isAdmin();

        return response()->json([
            'data' => FaqResource::collection($this->faqs->list($activeOnly)),
        ]);
    }

    /**
     * Guest-facing — no auth. Mounted at GET /public/faqs.
     */
    public function publicIndex(): JsonResponse
    {
        return response()->json([
            'data' => FaqResource::collection($this->faqs->list(true)),
        ]);
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = $this->faqs->create($request->validated());

        return response()->json([
            'message' => 'FAQ berhasil ditambahkan.',
            'data' => new FaqResource($faq),
        ], 201);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq = $this->faqs->update($faq, $request->validated());

        return response()->json([
            'message' => 'FAQ berhasil diperbarui.',
            'data' => new FaqResource($faq),
        ]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->faqs->delete($faq);

        return response()->json([
            'message' => 'FAQ berhasil dihapus.',
        ]);
    }
}
