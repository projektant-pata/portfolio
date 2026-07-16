<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE badges SET name = json_build_object('en', name)::jsonb WHERE name IS NOT NULL");
            DB::statement('ALTER TABLE badges ALTER COLUMN name TYPE jsonb USING name::jsonb');
        } else {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('name');
            });
            Schema::table('badges', function (Blueprint $table) {
                $table->json('name')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE badges SET name = name->>'en' WHERE name IS NOT NULL");
            DB::statement("ALTER TABLE badges ALTER COLUMN name TYPE varchar(255) USING name->>'en'");
        } else {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('name');
            });
            Schema::table('badges', function (Blueprint $table) {
                $table->string('name');
            });
        }
    }
};
