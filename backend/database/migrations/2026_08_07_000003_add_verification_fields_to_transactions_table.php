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
        Schema::table('transactions', function (Blueprint $table) {
            // When the customer uploaded proof_image — lets the admin verification queue sort
            // "awaiting review" (proof uploaded, still pending) ahead of "awaiting proof" (pending, none yet).
            $table->timestamp('proof_uploaded_at')->nullable()->after('proof_image');
            $table->foreignId('verified_by')->nullable()->after('paid_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['proof_uploaded_at', 'verified_at']);
        });
    }
};
