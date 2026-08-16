<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // links.alt has carried a stray NOT NULL constraint on Postgres since
        // create_links_table: the 2026_04_07 jsonb conversion only changed the
        // column's type there, it never dropped the constraint (the sqlite/mysql
        // branch of that same migration recreated the column as nullable, so
        // only pgsql was left with the bug). The app has always written null for
        // "no alt" via `?: null`, so this has been a live footgun; fixing it here
        // since we're already altering this table.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE links ALTER COLUMN alt DROP NOT NULL');
        }
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
