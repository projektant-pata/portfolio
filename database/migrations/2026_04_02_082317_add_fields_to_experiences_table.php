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
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('title')->after('type');
            $table->string('subtitle')->nullable()->after('title');
            $table->string('year')->nullable()->after('subtitle');
            $table->string('url')->nullable()->after('year');
            $table->string('image_path')->nullable()->after('url');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'year', 'url', 'image_path', 'sort_order']);
        });
    }
};
