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
        Schema::table('invitations', function (Blueprint $table) {
            // Separate from `cover_photo` (shown on the opening gate) — lets a customer use a
            // different photo for the Home section header. Falls back to cover_photo when null,
            // see PublicInvitationResource/InvitationResource.
            $table->string('home_cover_photo')->nullable()->after('cover_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('home_cover_photo');
        });
    }
};
