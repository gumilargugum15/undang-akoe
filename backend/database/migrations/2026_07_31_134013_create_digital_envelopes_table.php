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
        Schema::create('digital_envelopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->enum('type', ['bank', 'ewallet', 'qris'])->default('bank');
            $table->string('provider_name')->comment('e.g. BCA, Mandiri, GoPay, OVO, Dana, ShopeePay');
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('qr_image')->nullable()->comment('used when type = qris');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_envelopes');
    }
};
