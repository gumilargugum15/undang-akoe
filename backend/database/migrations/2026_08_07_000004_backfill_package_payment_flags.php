<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only migration: is_free/requires_payment/auto_publish are brand new columns, so every
 * package row (including ones already customized by an admin — name, price, limits, ...) sits
 * at the schema default (is_free=false, requires_payment=true) regardless of whether it's
 * actually a free tier. Backfill from `price` — the same signal the app implicitly relied on
 * before these columns existed — without touching any other column an admin may have edited.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('packages')->where('price', 0)->update([
            'is_free' => true,
            'requires_payment' => false,
            'auto_publish' => true,
        ]);

        DB::table('packages')->where('price', '>', 0)->update([
            'is_free' => false,
            'requires_payment' => true,
            'auto_publish' => false,
        ]);
    }

    public function down(): void
    {
        // Data backfill — nothing structural to reverse.
    }
};
