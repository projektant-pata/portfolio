<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['header', 'description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->json('header')->after('slug');
            $table->json('description')->nullable()->after('header');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['header', 'description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('header')->after('slug');
            $table->text('description')->nullable()->after('header');
        });
    }
};
