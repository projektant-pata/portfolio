<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Link;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        Project::query()->delete(); // cascades to links + project_badge

        $badgeIds = Badge::pluck('id', 'slug');

        $spsehub = Project::create([
            'slug' => 'spse-hub',
            'year' => 2022,
            'header' => ['en' => 'SPŠE Hub', 'cs' => 'SPŠE Rozcestník'],
            'description' => [
                'en' => 'The SPSE Hub is a project created under the guidance of Mr. Nitrogen to teach how to make a website. It marks my beginnings and has a nostalgic effect on me – so I\'m including it. Built using HTML5, CSS3, and JavaScript.',
                'cs' => 'Tento projekt je rozcestník všech webových stránek, které jsem vytvořil při studiu na střední škole při plnění úkolů od učitele Reného "Dusíka" Duse. Jsou to jedny z mých prvních stránek (první jsme vytvářeli už na základní škole).',
            ],
            'img_url' => 'images/projects/spse_wp.png',
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Everything — my first hand-written site',
                'cs' => 'Všechno — moje první ručně psané stránky',
            ],
        ]);

        Link::create([
            'project_id' => $spsehub->id,
            'url' => 'https://hyvlri22.llmp.spse-net.cz/',
            'alt' => ['en' => 'Visit website', 'cs' => 'Navštívit web'],
            'img_url' => 'images/projects/icons/web.webp',
            'kind' => 'live',
        ]);

        Link::create([
            'project_id' => $spsehub->id,
            'url' => 'https://github.com/projektant-pata/SPSE-WP',
            'alt' => ['en' => 'View on GitHub', 'cs' => 'Zobrazit na GitHubu'],
            'img_url' => 'images/mobile/icons/github.webp',
            'kind' => 'repo',
        ]);

        $spsehub->badges()->sync($badgeIds->only(['javascript'])->values()->all());

        $usladovny = Project::create([
            'slug' => 'u-sladovny',
            'year' => 2025,
            'header' => ['en' => 'U Sladovny', 'cs' => 'U Sladovny'],
            'description' => [
                'en' => 'A project I was part of during my part-time work at PekneWeby. The plan was simple – rebrand and rebuild the restaurant. My part was front-end and back-end. And the result? I think PekneWeby did a fabulous job.',
                'cs' => 'Projekt, na kterém jsem se podílel během své práce na částečný úvazek v PekneWeby. Plán byl jednoduchý - rebranding a rekonstrukce restaurace. Můj záběr byl ve front-endu a back-endu. A výsledek? Myslím, že firma PekneWeby odvedla báječnou práci.',
            ],
            'img_url' => 'images/projects/usladovny.png',
            'kind' => 'client',
            'client' => 'PekneWeby',
            'status' => 'live',
            'role' => [
                'en' => 'Front-end and back-end',
                'cs' => 'Front-end a back-end',
            ],
        ]);

        Link::create([
            'project_id' => $usladovny->id,
            'url' => 'https://www.usladovnychrudim.cz/',
            'alt' => ['en' => 'Visit website', 'cs' => 'Navštívit web'],
            'img_url' => 'images/projects/icons/web.webp',
            'kind' => 'live',
        ]);

        $usladovny->badges()->sync($badgeIds->only(['symfony', 'php'])->values()->all());

        $portfolio = Project::create([
            'slug' => 'portfolio',
            'year' => 2026,
            'header' => ['en' => 'Portfolio', 'cs' => 'Portfólio'],
            'description' => [
                'en' => 'I think everybody making something artistic should have a portfolio in any form that displays their mind, creativity, and most importantly, personality – and I believe that web developers are artists too.',
                'cs' => 'Myslím si, že každý, kdo dělá něco uměleckého, by měl mít portfolio v jakékoli podobě, které ukazuje jeho um, kreativitu a hlavně osobnost - a věřím, že i weboví vývojáři jsou umělci.',
            ],
            'img_url' => 'images/projects/portfolio.png',
            'kind' => 'personal',
            'status' => 'live',
            'role' => [
                'en' => 'Design and build, start to finish',
                'cs' => 'Návrh i realizace, od začátku do konce',
            ],
        ]);

        Link::create([
            'project_id' => $portfolio->id,
            'url' => 'https://github.com/projektant-pata/portfolio',
            'alt' => ['en' => 'View on GitHub', 'cs' => 'Zobrazit na GitHubu'],
            'img_url' => 'images/mobile/icons/github.webp',
            'kind' => 'repo',
        ]);

        $portfolio->badges()->sync($badgeIds->only(['laravel', 'php'])->values()->all());
    }
}
