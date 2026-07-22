<?php

namespace Database\Seeders;

use App\Models\Stat;
use Illuminate\Database\Seeder;

class StatSeeder extends Seeder
{
    public function run(): void
    {
        Stat::query()->delete();

        $stats = [
            ['value' => ['en' => 'Junior', 'cs' => 'Junior'], 'text' => ['en' => 'Professional Level', 'cs' => 'Profesionální úroveň']],
            ['value' => ['en' => '5+', 'cs' => '5+'], 'text' => ['en' => 'Projects Completed', 'cs' => 'Projektů dokončeno']],
            ['value' => null, 'source' => 'years_experience', 'text' => ['en' => 'Years of experience', 'cs' => 'Roky zkušeností']],
            ['value' => ['en' => '2', 'cs' => '2'], 'text' => ['en' => 'Countries Reached', 'cs' => 'Země dosaženy']],
            ['value' => null, 'source' => 'age', 'text' => ['en' => 'Years old', 'cs' => 'Let věku']],
            ['value' => ['en' => 'Loading..', 'cs' => 'Načítání..'], 'value_id' => 'elo', 'text' => ['en' => 'Highest chess elo', 'cs' => 'Nejvyšší šachové elo']],
            ['value' => ['en' => '♞', 'cs' => '♞'], 'text' => ['en' => 'Favorite piece', 'cs' => 'Nejoblíbenější figura']],
            ['value' => ['en' => '∞', 'cs' => '∞'], 'text' => ['en' => 'Coffee consumed', 'cs' => 'Vypitých šálků kávy']],
            ['value' => ['en' => '4', 'cs' => '4'], 'text' => ['en' => 'Hackathons won', 'cs' => 'Vyhraný hackathon']],
            ['value' => ['en' => '18', 'cs' => '18'], 'value_id' => 'github-repos', 'text' => ['en' => 'GitHub repositories', 'cs' => 'GitHub repozitářů']],
            ['value' => ['en' => '404', 'cs' => '404'], 'text' => ['en' => 'Hours slept', 'cs' => 'Hodin spánku']],
        ];

        foreach ($stats as $i => $stat) {
            Stat::create([
                'value' => $stat['value'],
                'text' => $stat['text'],
                'value_id' => $stat['value_id'] ?? null,
                'source' => $stat['source'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}
