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
            DB::statement("UPDATE links SET alt = json_build_object('en', alt)::jsonb WHERE alt IS NOT NULL AND alt != ''");
            DB::statement('ALTER TABLE links ALTER COLUMN alt TYPE jsonb USING alt::jsonb');
        } else {
            Schema::table('links', function (Blueprint $table) {
                $table->dropColumn('alt');
            });
            Schema::table('links', function (Blueprint $table) {
                $table->json('alt')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE links SET alt = alt->>'en' WHERE alt IS NOT NULL");
            DB::statement("ALTER TABLE links ALTER COLUMN alt TYPE varchar(255) USING (alt->>'en')");
        } else {
            Schema::table('links', function (Blueprint $table) {
                $table->dropColumn('alt');
            });
            Schema::table('links', function (Blueprint $table) {
                $table->string('alt')->nullable();
            });
        }
    }
};
