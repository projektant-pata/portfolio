<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The toolbox dropped its four-step frequency scale for two states: `using`
 * is what gets built with right now, `experience` is what has shipped before.
 * The group heads also gained a human note ("where I actually live"), which
 * rides on the tool rows themselves so it stays admin-editable per locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->renameColumn('frequency', 'state');
            $table->jsonb('group_note')->nullable();
        });

        DB::table('tools')->whereIn('state', ['daily', 'often'])->update(['state' => 'using']);
        DB::table('tools')->whereIn('state', ['sometimes', 'rarely'])->update(['state' => 'experience']);
    }

    public function down(): void
    {
        DB::table('tools')->where('state', 'using')->update(['state' => 'daily']);
        DB::table('tools')->where('state', 'experience')->update(['state' => 'sometimes']);

        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn('group_note');
            $table->renameColumn('state', 'frequency');
        });
    }
};
