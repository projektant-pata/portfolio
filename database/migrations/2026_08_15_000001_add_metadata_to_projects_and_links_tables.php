<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('kind', 20)->default('personal')->after('year');
            $table->string('client')->nullable()->after('kind');
            $table->string('status', 20)->nullable()->after('client');
            $table->json('role')->nullable()->after('description');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->string('kind', 20)->default('live')->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['kind', 'client', 'status', 'role']);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
