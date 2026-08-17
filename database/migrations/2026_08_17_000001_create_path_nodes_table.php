<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('path_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('year')->nullable();   // {en,cs} — "2019" but also "Now"/"Teď"
            $table->jsonb('title')->nullable();  // {en,cs}
            $table->jsonb('body')->nullable();   // {en,cs}
            $table->boolean('is_now')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('path_nodes');
    }
};
