<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('title')->nullable();      // {en,cs}
            $table->jsonb('highlight')->nullable();  // {en,cs} — phrase rendered gold inside the title
            $table->jsonb('body')->nullable();       // {en,cs}
            $table->jsonb('meta')->nullable();       // {en,cs} — monospace footer line
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
