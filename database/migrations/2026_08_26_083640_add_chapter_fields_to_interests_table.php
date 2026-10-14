<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->jsonb('label')->nullable();  // {en,cs} — chapter label, e.g. "Chess"
            $table->jsonb('pull')->nullable();   // {en,cs} — optional pull line
            $table->jsonb('facts')->nullable();  // {"en": [{"dt": …, "dd": …}], "cs": [...]}
        });
    }

    public function down(): void
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->dropColumn(['label', 'pull', 'facts']);
        });
    }
};
