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
        Schema::create('guestbooks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            // RSVP (Konfirmasi Kehadiran) and Buku Tamu are the same public submission in the
            // frontend form (src/components/invitation/interactive.tsx) — one table serves both:
            // the "RSVP" views filter/aggregate by `attendance`, "Buku Tamu" is the moderated wall.
            $table->string('guest_name');
            $table->string('phone', 20)->nullable();
            $table->enum('attendance', ['hadir', 'tidak_hadir', 'ragu'])->default('ragu')->index();
            $table->unsignedInteger('guest_count')->default(1);
            $table->text('message')->nullable();
            $table->boolean('is_approved')->default(true)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invitation_id', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guestbooks');
    }
};
