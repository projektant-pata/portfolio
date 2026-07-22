<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('value')->nullable();   // {en,cs} — shown unless `source` computes it
            $table->jsonb('text')->nullable();    // {en,cs} caption
            $table->string('value_id')->nullable(); // DOM id for client-side fills (elo, github-repos)
            $table->string('source')->nullable();   // age|years_experience — computed server-side
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
