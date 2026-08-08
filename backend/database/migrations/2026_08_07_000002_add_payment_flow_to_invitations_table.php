<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // `waiting_payment` (paid packages, pending checkout) and `archived` (user-hidden,
            // distinct from the admin-only `suspended` and the job-driven `expired`) are new.
            // Laravel rebuilds the column natively on every driver here (including SQLite, used
            // in tests) — no raw per-driver SQL and no doctrine/dbal needed for this change.
            $table->enum('status', ['draft', 'waiting_payment', 'published', 'expired', 'archived', 'suspended'])
                ->default('draft')
                ->change();

            // Fast pointer to the active/last checkout attempt for this invitation, so the
            // customer dashboard doesn't need a separate query to show "Lanjutkan Pembayaran".
            $table->foreignId('current_transaction_id')->nullable()->after('package_id')
                ->constrained('transactions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_transaction_id');
            $table->enum('status', ['draft', 'published', 'expired', 'suspended'])
                ->default('draft')
                ->change();
        });
    }
};
