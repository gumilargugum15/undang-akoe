<?php

namespace App\Console\Commands;

use App\Services\TransactionService;
use Illuminate\Console\Command;

/**
 * Marks pending manual-payment transactions past their expired_at window as Expired. The
 * invitation stays at Waiting Payment (not reverted to Draft) — see TransactionService::expireOverduePending().
 */
class ExpirePendingTransactions extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Expire pending manual-payment transactions past their expiry window';

    public function handle(TransactionService $transactions): int
    {
        $count = $transactions->expireOverduePending();

        $this->info("{$count} transaksi pending dipindahkan ke status expired.");

        return self::SUCCESS;
    }
}
