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
            ],
            [
                'name' => 'ChatGPT',
                'position' => ['en' => 'The best AI', 'cs' => 'The best AI'],
                'text' => [
                    'en' => '"Richard’s commitment to improving his craft and sharing ideas makes him an inspiring and valuable colleague."',
                    'cs' => '"Richardova oddanost zlepšování svého řemesla a sdílení nápadů z něj dělá inspirativního a cenného kolegu."',
                ],
            ],
            [
                'name' => 'Ondřej Kučera',
                'position' => ['en' => 'Co-founder of Prezz', 'cs' => 'Co-founder of Prezz'],
                'text' => [
                    'en' => '"Richard adapts quickly to new tools and tackles complex projects with confidence and creativity."',
                    'cs' => '"Richard se rychle přizpůsobuje novým nástrojům a s důvěrou a kreativitou se vypořádává se složitými projekty."',
                ],
            ],
        ];

        foreach ($reviews as $i => $review) {
            Review::create([
                'name' => $review['name'],
                'position' => $review['position'],
                'text' => $review['text'],
                'sort_order' => $i,
            ]);
        }
    }
}
