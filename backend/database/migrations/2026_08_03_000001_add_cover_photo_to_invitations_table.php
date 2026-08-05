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
            // Customer-uploaded hero background photo — shown full-bleed behind the cover
            // section instead of (or alongside) the theme's own texture/stock photo. Optional
            // and independent of event_category: any invitation can set one.
            $table->string('cover_photo')->nullable()->after('theme_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('cover_photo');
        });
    }
};
