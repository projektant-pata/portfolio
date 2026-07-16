<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->index('type');
            $table->index('sort_order');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
        });
    }
};
