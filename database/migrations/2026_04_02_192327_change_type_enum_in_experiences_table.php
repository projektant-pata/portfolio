<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE experiences DROP CONSTRAINT IF EXISTS experiences_type_check");
        }

        DB::statement("UPDATE experiences SET type = 'life' WHERE type = 'edu'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE experiences ADD CONSTRAINT experiences_type_check CHECK (type IN ('work', 'life'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE experiences DROP CONSTRAINT IF EXISTS experiences_type_check");
        }

        DB::statement("UPDATE experiences SET type = 'edu' WHERE type = 'life'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE experiences ADD CONSTRAINT experiences_type_check CHECK (type IN ('work', 'edu'))");
        }
    }
};
