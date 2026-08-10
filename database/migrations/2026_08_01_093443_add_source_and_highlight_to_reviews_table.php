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
        Schema::table('reviews', function (Blueprint $table) {
            $table->jsonb('highlight')->nullable()->after('text'); // {en,cs} — phrase inside `text` to accent
            $table->string('source')->nullable()->after('highlight'); // e.g. "LinkedIn", "E-mail"
            $table->string('source_color')->nullable()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['highlight', 'source', 'source_color']);
        });
    }
};
