<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('about_cards');
    }

    /**
     * Recreates the empty shell only. The card copy lived here alone and now
     * lives in path_nodes, interests, experiences and the about_* settings —
     * rolling back does not bring the prose back.
     */
    public function down(): void
    {
        Schema::create('about_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('title')->nullable();
            $table->jsonb('text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
