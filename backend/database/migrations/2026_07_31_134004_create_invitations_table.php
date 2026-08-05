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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained('themes')->restrictOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->enum('event_category', [
                'wedding', 'birthday', 'khitan', 'aqiqah', 'anniversary', 'corporate', 'graduation', 'custom',
            ])->default('wedding')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->enum('status', ['draft', 'published', 'expired', 'suspended'])->default('draft')->index();
            $table->boolean('is_active')->default(true);
            // Per-invitation override merged on top of the theme's base `config` at render time
            // (primary_color, font_head, ...) — this is what "Kustomisasi Warna/Font" in the
            // customer dashboard writes to, without ever touching the shared theme row.
            $table->json('theme_settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedBigInteger('view_count')->default(0)
                ->comment('denormalized counter, source of truth is invitation_visits');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
