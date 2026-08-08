<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly InvitationRepositoryInterface $invitations,
    ) {}

    /**
     * Starts (or restarts, for "Ganti Paket") a manual-payment checkout. Only reachable for
     * packages with requires_payment=true — a FREE/no-payment package is published directly
     * via InvitationService::publish(), it never goes through checkout at all.
     */
    public function checkout(Invitation $invitation, Package $package, User $user, string $paymentMethod): Transaction
    {
        if (! in_array($invitation->status, [Invitation::STATUS_DRAFT, Invitation::STATUS_WAITING_PAYMENT], true)) {
            throw ValidationException::withMessages([
                'invitation' => ['Checkout hanya bisa dilakukan selagi undangan berstatus draft atau menunggu pembayaran.'],
            ]);
        }

        if (! $package->requiresPayment()) {
            throw ValidationException::withMessages([
                'package' => ['Paket ini tidak memerlukan pembayaran — publish undangan secara langsung.'],
            ]);
        }

        if (! in_array($paymentMethod, Transaction::PAYMENT_METHODS, true)) {
            throw ValidationException::withMessages([
                'payment_method' => ['Metode pembayaran tidak dikenali.'],
            ]);
        }

        return DB::transaction(function () use ($invitation, $package, $user, $paymentMethod) {
            // "Ganti Paket" while already Waiting Payment: the previous attempt never got
            // verified either way, so it's superseded rather than left dangling as pending.
            if ($invitation->currentTransaction?->isPending()) {
                $this->transactions->update($invitation->currentTransaction, [
                    'status' => Transaction::STATUS_FAILED,
                    'notes' => 'Digantikan oleh transaksi checkout baru.',
                ]);
            }

            $transaction = $this->transactions->create([
                'invoice_number' => $this->uniqueInvoiceNumber(),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name_snapshot' => $package->name,
                'invitation_id' => $invitation->id,
                'amount' => $package->price,
                'payment_method' => $paymentMethod,
                'payment_channel' => match ($paymentMethod) {
                    'qris' => 'QRIS',
                    'dana' => 'DANA',
                    'gopay' => 'GoPay',
                    default => null,
                },
                'status' => Transaction::STATUS_PENDING,
                'expired_at' => now()->addHours((int) config('payment.pending_expiry_hours')),
            ]);

            $this->invitations->update($invitation, [
                'package_id' => $package->id,
                'status' => Invitation::STATUS_WAITING_PAYMENT,
                'current_transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    private function uniqueInvoiceNumber(): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while ($this->transactions->invoiceNumberExists($candidate));

        return $candidate;
    }
}
