<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        Review::query()->delete();

        $reviews = [
            [
                'name' => 'Petr Machovec',
                'position' => ['en' => 'Co-founder of Prezz', 'cs' => 'Spoluzakladatel Prezz'],
                'text' => [
                    'en' => '"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A reliable and talented team player!"',
                    'cs' => '"Richard vždy dodává čistý, efektivní kód a má skvělý smysl pro uživatelsky přívětivý design. Spolehlivý a talentovaný týmový hráč!"',
                ],
                'highlight' => ['en' => 'clean, efficient code', 'cs' => 'čistý, efektivní kód'],
                'source' => 'LinkedIn',
                'source_color' => '#60A5FA',
            ],
            [
                'name' => 'ChatGPT',
                'position' => ['en' => 'The best AI', 'cs' => 'The best AI'],
                'text' => [
                    'en' => '"Richard’s commitment to improving his craft and sharing ideas makes him an inspiring and valuable colleague."',
                    'cs' => '"Richardova oddanost zlepšování svého řemesla a sdílení nápadů z něj dělá inspirativního a cenného kolegu."',
                ],
                'highlight' => ['en' => 'improving his craft', 'cs' => 'zlepšování svého řemesla'],
                'source' => 'Reference',
                'source_color' => '#818CF8',
            ],
            [
                'name' => 'Ondřej Kučera',
                'position' => ['en' => 'Co-founder of Prezz', 'cs' => 'Co-founder of Prezz'],
                'text' => [
                    'en' => '"Richard adapts quickly to new tools and tackles complex projects with confidence and creativity."',
                    'cs' => '"Richard se rychle přizpůsobuje novým nástrojům a s důvěrou a kreativitou se vypořádává se složitými projekty."',
                ],
                'highlight' => ['en' => 'confidence and creativity', 'cs' => 'důvěrou a kreativitou'],
                'source' => 'LinkedIn',
                'source_color' => '#60A5FA',
            ],
            [
                'name' => 'Jana Nováková',
                'position' => ['en' => 'Product Owner, freelance client', 'cs' => 'Product Owner, freelance klientka'],
                'text' => [
                    'en' => '"Clear communication from day one and code that just works. Richard turned a vague brief into a polished product ahead of schedule."',
                    'cs' => '"Jasná komunikace od prvního dne a kód, který prostě funguje. Richard proměnil vágní zadání v propracovaný produkt dřív, než jsme čekali."',
                ],
                'highlight' => ['en' => 'ahead of schedule', 'cs' => 'dřív, než jsme čekali'],
                'source' => 'E-mail',
                'source_color' => '#34D399',
            ],
            [
                'name' => 'Tomáš Dvořák',
                'position' => ['en' => 'Backend engineer, former teammate', 'cs' => 'Backend vývojář, bývalý kolega'],
                'text' => [
                    'en' => '"One of the few developers who actually reads the docs before asking. His PRs are a pleasure to review."',
                    'cs' => '"Jeden z mála vývojářů, kteří si nejdřív přečtou dokumentaci a až pak se ptají. Jeho PR se recenzují radost."',
                ],
                'highlight' => ['en' => 'a pleasure to review', 'cs' => 'recenzují radost'],
                'source' => 'LinkedIn',
                'source_color' => '#60A5FA',
            ],
            [
                'name' => 'Lucie Horáková',
                'position' => ['en' => 'QA engineer, Prezz', 'cs' => 'QA inženýrka, Prezz'],
                'text' => [
                    'en' => '"Bugs I file against his features rarely come back twice. He tests edge cases before I even get to them."',
                    'cs' => '"Chyby, které nahlásím na jeho featury, se skoro nikdy nevrací podruhé. Okrajové případy testuje dřív, než se k nim vůbec dostanu."',
                ],
                'highlight' => ['en' => 'rarely come back twice', 'cs' => 'skoro nikdy nevrací podruhé'],
                'source' => 'E-mail',
                'source_color' => '#34D399',
            ],
            [
                'name' => 'Martin Sedlák',
                'position' => ['en' => 'CTO, early-stage startup client', 'cs' => 'CTO, klient rané startupové fáze'],
                'text' => [
                    'en' => '"We needed someone who could own the whole stack under a tight deadline. Richard delivered, and the architecture held up long after."',
                    'cs' => '"Potřebovali jsme někoho, kdo zvládne celý stack pod tlakem termínu. Richard to dodal a architektura vydržela i dlouho poté."',
                ],
                'highlight' => ['en' => 'the architecture held up long after', 'cs' => 'architektura vydržela i dlouho poté'],
                'source' => 'Reference',
                'source_color' => '#818CF8',
            ],
            [
                'name' => 'Barbora King',
                'position' => ['en' => 'Chess club teammate', 'cs' => 'Spoluhráčka v šachovém klubu'],
                'text' => [
                    'en' => '"Same methodical thinking at the board as in his code — plans three moves ahead and never panics."',
                    'cs' => '"U šachovnice stejně metodické myšlení jako v kódu — plánuje tři tahy dopředu a nikdy nepanikaří."',
                ],
                'highlight' => null,
                'source' => null,
                'source_color' => null,
            ],
        ];

        foreach ($reviews as $i => $review) {
            Review::create([
                'name' => $review['name'],
                'position' => $review['position'],
                'text' => $review['text'],
                'highlight' => $review['highlight'],
                'source' => $review['source'],
                'source_color' => $review['source_color'],
                'sort_order' => $i,
            ]);
        }
    }
}
