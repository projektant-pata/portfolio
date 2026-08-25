<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');                          // proper noun — not translated
            $table->string('color')->nullable();             // brand colour of the 6px dot
            $table->string('frequency')->nullable();         // daily | often | sometimes | rarely
            $table->jsonb('group')->nullable();              // {en,cs} — column heading on /about-me
            $table->string('placement')->default('grid');    // grid | rotation | learning
            $table->boolean('show_on_home')->default(false); // grid tools only: the home 4×2 cells
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
