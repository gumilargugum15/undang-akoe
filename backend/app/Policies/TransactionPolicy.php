<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Listing is always allowed — scoping to "own transactions only" for customers
     * happens in TransactionRepository::paginate(), same pattern as InvitationPolicy.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() || $transaction->user_id === $user->id;
    }

    /**
     * Only the customer who owns the transaction uploads their own proof of payment.
     */
    public function uploadProof(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    /**
     * "Batalkan Pembayaran" — owner only, same reasoning as uploadProof.
     */
    public function cancel(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    /**
     * Approving/rejecting a manually-uploaded proof is an admin verification action.
     */
    public function verify(User $user): bool
    {
        return $user->isAdmin();
    }
}
