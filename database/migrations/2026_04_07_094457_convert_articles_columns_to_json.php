<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['header', 'description', 'content']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->json('header')->after('slug');
            $table->json('description')->nullable()->after('header');
            $table->json('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['header', 'description', 'content']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('header')->after('slug');
            $table->string('description')->nullable()->after('header');
            $table->longText('content')->nullable()->after('description');
        });
    }
};
