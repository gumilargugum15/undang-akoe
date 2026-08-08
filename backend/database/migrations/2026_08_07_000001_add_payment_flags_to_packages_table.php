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
        Schema::table('packages', function (Blueprint $table) {
            // Explicit flags instead of inferring "free" from price == 0 — lets a Rp 0
            // promo on a normally-paid package exist without being treated as the FREE tier.
            $table->boolean('is_free')->default(false)->after('price');
            $table->boolean('requires_payment')->default(true)->after('is_free');
            $table->boolean('auto_publish')->default(false)->after('requires_payment');
            // Granular feature/limit toggles that don't warrant their own column
            // (max_photos/max_guests stay as dedicated columns since they're queried directly).
            $table->json('limits')->nullable()->after('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['is_free', 'requires_payment', 'auto_publish', 'limits']);
        });
    }
};
