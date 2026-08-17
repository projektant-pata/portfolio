<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->string('tier')->default('normal'); // hero|normal|soft — bento weight
            $table->jsonb('note')->nullable();         // {en,cs} — hero tiles' supporting line
        });
    }

    public function down(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn(['tier', 'note']);
        });
    }
};
