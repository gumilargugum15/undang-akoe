<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly InvitationService $invitationService,
        private readonly ImageProcessingService $images,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        return $this->transactions->paginate($user, $filters, (int) ($filters['per_page'] ?? 15));
    }

    public function uploadProof(Transaction $transaction, UploadedFile $file): Transaction
    {
        if (! $transaction->isPending()) {
            throw ValidationException::withMessages([
                'transaction' => ['Bukti pembayaran hanya bisa diunggah selagi transaksi masih pending.'],
            ]);
        }

        if ($transaction->proof_image) {
            Storage::disk('public')->delete($transaction->proof_image);
        }

        $path = $this->images->storePhoto($file, "transactions/{$transaction->id}/proof", maxWidth: 1600);

        return $this->transactions->update($transaction, [
            'proof_image' => $path,
            'proof_uploaded_at' => now(),
        ]);
    }

    /**
     * Admin confirms the manual transfer/QRIS payment actually arrived — the only path that
     * turns a Transaction into `paid` and, in the same DB transaction, activates the invitation.
     */
    public function approve(Transaction $transaction, User $admin): Transaction
    {
        if (! $transaction->isPending()) {
            throw ValidationException::withMessages([
                'transaction' => ['Hanya transaksi berstatus pending yang dapat disetujui.'],
            ]);
        }

        return DB::transaction(function () use ($transaction, $admin) {
            $transaction = $this->transactions->update($transaction, [
                'status' => Transaction::STATUS_PAID,
                'paid_at' => now(),
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            $this->invitationService->activate($transaction->invitation);

            return $transaction->fresh(['invitation']);
        });
    }

    /**
     * Admin rejects (proof unreadable, amount mismatch, ...). The invitation stays Waiting
     * Payment — the customer retries via a fresh checkout (CheckoutService), keeping this
     * transaction as a permanent record of the failed attempt.
     */
    public function reject(Transaction $transaction, User $admin, ?string $reason): Transaction
    {
        if (! $transaction->isPending()) {
            throw ValidationException::withMessages([
                'transaction' => ['Hanya transaksi berstatus pending yang dapat ditolak.'],
            ]);
        }

        return $this->transactions->update($transaction, [
            'status' => Transaction::STATUS_FAILED,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Customer-initiated "Batalkan Pembayaran" — returns the invitation to Draft so they can
     * pick a different package (or a free one) from scratch.
     */
    public function cancel(Transaction $transaction): Transaction
    {
        if (! $transaction->isPending()) {
            throw ValidationException::withMessages([
                'transaction' => ['Transaksi yang sudah diproses tidak dapat dibatalkan.'],
            ]);
        }

        return DB::transaction(function () use ($transaction) {
            $transaction = $this->transactions->update($transaction, [
                'status' => Transaction::STATUS_FAILED,
                'notes' => 'Dibatalkan oleh pengguna.',
            ]);

            $this->invitationService->returnToDraft($transaction->invitation);

            return $transaction;
        });
    }

    /**
     * Marks pending transactions past their `expired_at` window as Expired — run by the
     * `payments:expire-pending` scheduled command. The invitation is left at Waiting Payment
     * (not reverted to Draft) so the customer can still see it needed action and retry.
     */
    public function expireOverduePending(): int
    {
        $overdue = Transaction::query()
            ->where('status', Transaction::STATUS_PENDING)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($overdue as $transaction) {
            $this->transactions->update($transaction, ['status' => Transaction::STATUS_EXPIRED]);
        }

        return $overdue->count();
    }
}
