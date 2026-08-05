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
        Schema::create('musics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->enum('source', ['upload', 'spotify', 'youtube_music'])->default('upload');
            $table->string('title')->nullable();
            $table->string('artist')->nullable();
            $table->string('file_path')->nullable()->comment('used when source = upload');
            $table->string('external_url')->nullable()->comment('used when source = spotify/youtube_music');
            $table->boolean('autoplay')->default(true);
            $table->boolean('is_loop')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['invitation_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('musics');
    }
};
