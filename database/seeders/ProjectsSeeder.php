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

        $firstSite = Project::create([
            'slug' => 'first-site',
            'year' => 2020,
            'header' => ['en' => 'First site', 'cs' => 'První stránky'],
            'description' => [
                'en' => "My first website. Unfortunately, I have no backup :(. This taught me how important backups are!",
                'cs' => "Moje první webové stránky. Bohužel nemám zálohu :(. Naučilo mě to, jak důležité jsou zálohy!",
            ],
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Everything — my very first hand-written site',
                'cs' => 'Všechno — moje úplně první ručně psané stránky',
            ],
        ]);

        $firstSite->badges()->sync($badgeIds->only(['html', 'css', 'education'])->values()->all());

        $lifely = Project::create([
            'slug' => 'lifely',
            'year' => 2026,
            'header' => ['en' => 'Lifely', 'cs' => 'Lifely'],
            'description' => [
                'en' => 'Project for the OpenData KHK and Hackathon on NKU (national audit office) hackathons. The idea was to help people find their new place to live by scanning the area and evaluating nearby healthcare, noise from all sources we could get our hands on (like a nearby airport), and stuff like that. Links to OpenData NKU and NKU coming in the future.',
                'cs' => 'Projekt pro hackathony OpenData KHK a Hackathon on NKU. Cílem bylo pomoci lidem najít nové bydliště skenováním okolí a vyhodnocením dostupnosti zdravotní péče, hluku ze všech zdrojů, které jsme dokázali získat (například blízkého letiště), a podobných věcí. Odkazy na OpenData NKU a NKU přibudou v budoucnu.',
            ],
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Hackathon team member',
                'cs' => 'Člen hackathonového týmu',
            ],
        ]);

        $lifely->badges()->sync($badgeIds->only(['typescript', 'hackathon', 'education'])->values()->all());

        $zemloveka = Project::create([
            'slug' => 'zemloveka',
            'year' => 2026,
            'header' => ['en' => 'ZemLOVEka', 'cs' => 'ZemLOVEka'],
            'description' => [
                'en' => "A gamified cookguide for people who want to get into cooking. Three modes: Campaign, a story-driven mode where people progress and learn the basics of cooking and cooking safety; Open World, similar to Campaign but where people choose a country and learn its dishes and culture; and a basic cookguide for finding recipes for people who already know what they want. Link to Funovation 26.",
                'cs' => 'Gamifikovaný kuchařský průvodce pro lidi, kteří se chtějí naučit vařit. Tři módy: Kampaň, příběhový mód, kde lidé postupují a učí se základy vaření a bezpečnosti při vaření; Otevřený svět, podobný Kampani, jen si lidé vyberou zemi a učí se její jídla a kulturu; a základní kuchařský průvodce pro hledání receptů pro ty, kdo už vědí, co chtějí. Odkaz na Funovation 26.',
            ],
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Hackathon team member',
                'cs' => 'Člen hackathonového týmu',
            ],
        ]);

        $zemloveka->badges()->sync($badgeIds->only(['typescript', 'hackathon', 'education'])->values()->all());

        $tda = Project::create([
            'slug' => 'teachers-digital-academy',
            'year' => 2026,
            'header' => ['en' => 'Teachers digital Academy', 'cs' => 'Teachers digital Academy'],
            'description' => [
                'en' => 'Platform that innovates distance teaching with interactive quizzes, live presentations, and a lot more!',
                'cs' => 'Platforma, která inovuje výuku na dálku pomocí interaktivních kvízů, živých prezentací a mnoha dalších funkcí!',
            ],
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Hackathon team member',
                'cs' => 'Člen hackathonového týmu',
            ],
        ]);

        $tda->badges()->sync($badgeIds->only(['typescript', 'java', 'hackathon', 'education'])->values()->all());

        $tourdecloud = Project::create([
            'slug' => 'tourdecloud',
            'year' => 2026,
            'header' => ['en' => 'TourdeCloud', 'cs' => 'TourdeCloud'],
            'description' => [
                'en' => 'Took part in creating the platform for the next year of Tour-de-App 27. My part was back-end — the achievements and leveling system. TourdeCloud is a platform for contestants to upload their projects, show progress, roadmap, and a lot more!',
                'cs' => 'Podílel jsem se na tvorbě platformy pro další ročník Tour-de-App 27. Můj podíl byl back-end - systém achievementů a levelování. TourdeCloud je platforma pro soutěžící, kde mohou nahrávat své projekty, ukazovat progres, roadmapu a mnoho dalšího!',
            ],
            'kind' => 'client',
            'client' => 'Tour-de-App',
            'status' => 'live',
            'role' => [
                'en' => 'Back-end — achievements and leveling system',
                'cs' => 'Back-end - systém achievementů a levelování',
            ],
        ]);

        $tourdecloud->badges()->sync($badgeIds->only(['typescript', 'non-profit'])->values()->all());
    }
}
