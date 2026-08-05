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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('package_name_snapshot')->comment('preserves the paid-for name if the package changes/is removed later');
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable()->comment('bank_transfer, qris, ewallet, ...');
            $table->string('payment_channel')->nullable()->comment('e.g. BCA, Midtrans, Xendit');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('proof_image')->nullable()->comment('manual transfer proof upload');
            $table->string('gateway_reference')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
