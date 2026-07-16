<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $colors = [
        'competetion' => '#EAB308', // Gold — achievement/competition
        'work'        => '#60A5FA', // Blue — professional work
        'certificate' => '#34D399', // Emerald — validated credential
        'java'        => '#F97316', // Orange — Java brand adjacent
        'php'         => '#A78BFA', // Violet — PHP brand purple
        'education'   => '#38BDF8', // Sky — learning
        'hardware'    => '#F59E0B', // Amber — engineering/electronics
        'python'      => '#2DD4BF', // Teal — Python modern vibe
        'it'          => '#818CF8', // Indigo — general tech
    ];

    public function up(): void
    {
        foreach ($this->colors as $slug => $color) {
            DB::table('badges')->where('slug', $slug)->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        DB::table('badges')->whereIn('slug', array_keys($this->colors))->update(['color' => null]);
    }
};
