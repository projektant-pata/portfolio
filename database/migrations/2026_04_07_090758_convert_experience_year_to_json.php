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
            DB::statement("UPDATE experiences SET year = json_build_object('en', year)::jsonb WHERE year IS NOT NULL");
            DB::statement('ALTER TABLE experiences ALTER COLUMN year TYPE jsonb USING year::jsonb');
        } else {
            Schema::table('experiences', function (Blueprint $table) {
                $table->dropColumn('year');
            });
            Schema::table('experiences', function (Blueprint $table) {
                $table->json('year')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE experiences SET year = year->>'en' WHERE year IS NOT NULL");
            DB::statement("ALTER TABLE experiences ALTER COLUMN year TYPE varchar(50) USING year->>'en'");
        } else {
            Schema::table('experiences', function (Blueprint $table) {
                $table->dropColumn('year');
            });
            Schema::table('experiences', function (Blueprint $table) {
                $table->string('year', 50)->nullable();
            });
        }
    }
};
