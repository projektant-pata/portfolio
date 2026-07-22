<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('title')->nullable(); // {en,cs}
            $table->jsonb('text')->nullable();  // {en,cs} (HTML)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_cards');
    }
};
