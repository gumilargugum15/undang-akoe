<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginate(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::query()->with(['user:id,name,email', 'package:id,name', 'invitation:id,title,slug']);

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['awaiting_verification'])) {
            $query->where('status', Transaction::STATUS_PENDING)->whereNotNull('proof_uploaded_at');
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh(['user', 'package', 'invitation']);
    }

    public function invoiceNumberExists(string $invoiceNumber): bool
    {
        return Transaction::withTrashed()->where('invoice_number', $invoiceNumber)->exists();
    }
}
