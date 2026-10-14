<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

/**
 * Canonical badge set. Slugs of the category badges match the original
 * seed_badge_colors migration; tech badges use brand-adjacent colors.
 * Idempotent: keyed on slug via updateOrCreate.
 */
class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // category
            ['competition', 'Competition', 'Soutěž',      '#EAB308'], // gold
            ['work',        'Work',        'Práce',        '#60A5FA'], // blue
            ['certificate', 'Certificate', 'Certifikát',   '#34D399'], // emerald
            ['education',   'Education',   'Vzdělání',      '#38BDF8'], // sky
            ['hardware',    'Hardware',    'Hardware',      '#F59E0B'], // amber
            ['it',          'IT',          'IT',            '#818CF8'], // indigo
            ['hackathon',   'Hackathon',   'Hackathon',     '#F472B6'], // pink
            ['non-profit',  'Non-profit',  'Nezisková organizace', '#4ADE80'], // green
            // tech
            ['java',        'Java',        'Java',          '#F97316'], // orange
            ['php',         'PHP',         'PHP',           '#A78BFA'], // violet
            ['python',      'Python',      'Python',        '#2DD4BF'], // teal
            ['laravel',     'Laravel',     'Laravel',       '#FF2D20'], // Laravel red
            ['symfony',     'Symfony',     'Symfony',       '#64748B'], // slate (brand black)
            ['spring-boot', 'Spring Boot', 'Spring Boot',   '#6DB33F'], // Spring green
            ['javascript',  'JavaScript',  'JavaScript',    '#F7DF1E'], // JS yellow
            ['typescript',  'TypeScript',  'TypeScript',    '#3178C6'], // TS blue
            ['html',        'HTML',        'HTML',          '#E34F26'], // HTML orange
            ['css',         'CSS',         'CSS',           '#2965F1'], // CSS blue
            ['blockchain',  'Blockchain',  'Blockchain',    '#22D3EE'], // cyan
        ];

        foreach ($badges as [$slug, $en, $cs, $color]) {
            Badge::updateOrCreate(
                ['slug' => $slug],
                ['name' => ['en' => $en, 'cs' => $cs], 'color' => $color],
            );
        }
    }
}
