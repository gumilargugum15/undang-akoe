<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\RejectTransactionRequest;
use App\Http\Requests\Transaction\UploadProofRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Transaction::class);

        $filters = $request->only(['status', 'awaiting_verification', 'per_page']);
        $transactions = $this->transactions->list($request->user(), $filters);

        return response()->json([
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        $transaction->load(['invitation', 'package', 'user']);

        return response()->json(['data' => new TransactionResource($transaction)]);
    }

    public function uploadProof(UploadProofRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('uploadProof', $transaction);

        $transaction = $this->transactions->uploadProof($transaction, $request->file('proof'));

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function approve(Transaction $transaction): JsonResponse
    {
        $this->authorize('verify', Transaction::class);

        $transaction = $this->transactions->approve($transaction, request()->user());

        return response()->json([
            'message' => 'Pembayaran disetujui — undangan berhasil dipublikasikan.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function reject(RejectTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('verify', Transaction::class);

        $transaction = $this->transactions->reject($transaction, $request->user(), $request->validated('reason'));

        return response()->json([
            'message' => 'Pembayaran ditolak.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function cancel(Transaction $transaction): JsonResponse
    {
        $this->authorize('cancel', $transaction);

        $transaction = $this->transactions->cancel($transaction);

        return response()->json([
            'message' => 'Pembayaran dibatalkan. Undangan dikembalikan ke status draft.',
            'data' => new TransactionResource($transaction),
        ]);
    }
}
