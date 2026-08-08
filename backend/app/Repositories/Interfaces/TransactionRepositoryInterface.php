<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(User $user, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;

    public function invoiceNumberExists(string $invoiceNumber): bool;
}
