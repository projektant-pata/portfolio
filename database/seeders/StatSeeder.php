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
            [
                'value' => ['en' => '5+', 'cs' => '5+'],
                'text' => ['en' => 'Projects shipped', 'cs' => 'Vydaných projektů'],
                'tier' => 'hero',
                'note' => [
                    'en' => 'Client work, school projects and one platform running in production.',
                    'cs' => 'Klientské zakázky, školní projekty a jedna platforma běžící v produkci.',
                ],
            ],
            [
                'value' => null,
                'source' => 'years_experience',
                'text' => ['en' => 'Years writing code', 'cs' => 'Let u kódu'],
                'tier' => 'hero',
                'note' => [
                    'en' => 'Started in 2020 and have not found a reason to stop.',
                    'cs' => 'Začal jsem v roce 2020 a zatím nenašel důvod přestat.',
                ],
            ],
            ['value' => ['en' => 'Junior', 'cs' => 'Junior'], 'text' => ['en' => 'Professional Level', 'cs' => 'Profesionální úroveň']],
            ['value' => null, 'source' => 'age', 'text' => ['en' => 'Years old', 'cs' => 'Let věku']],
            ['value' => ['en' => '2', 'cs' => '2'], 'text' => ['en' => 'Countries Reached', 'cs' => 'Země dosaženy']],
            ['value' => ['en' => '18', 'cs' => '18'], 'value_id' => 'github-repos', 'text' => ['en' => 'GitHub repositories', 'cs' => 'GitHub repozitářů']],
            ['value' => ['en' => 'Loading..', 'cs' => 'Načítání..'], 'value_id' => 'elo', 'text' => ['en' => 'Highest chess elo', 'cs' => 'Nejvyšší šachové elo']],
            ['value' => ['en' => '4', 'cs' => '4'], 'text' => ['en' => 'Hackathons won', 'cs' => 'Vyhraných hackathonů']],
            ['value' => ['en' => '♞', 'cs' => '♞'], 'text' => ['en' => 'Favorite piece', 'cs' => 'Nejoblíbenější figura']],
            ['value' => ['en' => '∞', 'cs' => '∞'], 'text' => ['en' => 'Coffee consumed', 'cs' => 'Vypitých šálků kávy'], 'tier' => 'soft'],
            ['value' => ['en' => '404', 'cs' => '404'], 'text' => ['en' => 'Hours slept, not found', 'cs' => 'Hodin spánku, nenalezeno'], 'tier' => 'soft'],
        ];

        foreach ($stats as $i => $stat) {
            Stat::create([
                'value' => $stat['value'],
                'text' => $stat['text'],
                'value_id' => $stat['value_id'] ?? null,
                'source' => $stat['source'] ?? null,
                'tier' => $stat['tier'] ?? 'normal',
                'note' => $stat['note'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}
