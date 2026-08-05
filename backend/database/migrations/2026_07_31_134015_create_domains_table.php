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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('domain_name')->unique()->comment('custom domain upgrade; the free subdomain lives on invitations.subdomain');
            $table->enum('status', ['pending', 'verifying', 'verified', 'active', 'failed'])->default('pending')->index();
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('ssl_status', ['none', 'pending', 'issued', 'failed'])->default('none');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
