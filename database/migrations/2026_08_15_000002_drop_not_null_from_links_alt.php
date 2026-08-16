<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // links.alt has carried a stray NOT NULL constraint on Postgres since
        // create_links_table: the 2026_04_07 jsonb conversion only changed the
        // column's type there, it never dropped the constraint (the sqlite/mysql
        // branch of that same migration recreated the column as nullable, so
        // only pgsql was left with the bug). The app has always written null for
        // "no alt" via `?: null`, so this has been a live footgun.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE links ALTER COLUMN alt DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Deliberately not re-imposing the NOT NULL constraint: it was a bug,
        // not a feature, and by the time this could roll back there may be
        // rows with a null alt that the constraint would then reject.
    }
};
