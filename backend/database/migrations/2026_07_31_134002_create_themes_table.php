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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('theme_category_id')->constrained('theme_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner_preview')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->enum('type', ['free', 'premium'])->default('free')->index();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('supports_dark_mode')->default(false);
            // Mirrors the frontend InvitationTheme shape (src/lib/themes.ts): ornament, reveal,
            // radius, shadow, letter-spacing, font trio and the ThemeTokens color palette — kept as
            // one JSON blob so the Theme Engine can add new visual knobs without a migration.
            $table->json('config');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
