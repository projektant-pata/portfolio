<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('date')->index();
        });

        // Everything written before the blog existed was written to be read:
        // publish it on its own display date rather than hiding it.
        DB::table('articles')->update(['published_at' => DB::raw('date')]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
