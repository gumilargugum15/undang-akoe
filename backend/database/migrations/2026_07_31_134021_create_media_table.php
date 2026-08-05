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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            // Admin's generic "Kelola Media" library — platform-level assets (logos, banner images,
            // theme thumbnails). Per-invitation uploads (gallery, couple photo, music, ...) store
            // their own `path` column directly and don't go through here.
            $table->nullableMorphs('mediable');
            $table->string('collection')->default('default')->comment('avatar, banner, theme_thumbnail, document, other');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable()->comment('bytes');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
