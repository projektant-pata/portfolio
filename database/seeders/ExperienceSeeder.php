<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Experience;
use Illuminate\Database\Seeder;

/**
 * Restores the Work/Life experience entries recovered from the original
 * (pre-Livewire) portfolio's translation files. Re-runnable: it clears the
 * experiences table first, so `db:seed --class=ExperienceSeeder` is idempotent.
 *
 * type mapping (original taxonomy was Work / Education; new app is Work / Life):
 *   work = actual jobs / work placements
 *   life = education, certificates, competitions
 * Flip any entry's type from the dashboard if you disagree.
 */
class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        Experience::query()->delete();

        $badgeIds = Badge::pluck('id', 'slug');

        $entries = [
            [
                'type' => 'work',
                'is_special' => false,
                'sort_order' => 1,
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'Erasmus', 'cs' => 'Erasmus'],
                'subtitle' => [
                    'en' => 'Work trip to Spain made possible by the European Union',
                    'cs' => 'Pracovní cesta do Španělska umožněná Evropskou unií',
                ],
                'content' => ['en' => '', 'cs' => ''],
                'links' => [['url' => 'https://erasmus-plus.ec.europa.eu/', 'alt' => 'Erasmus+']],
                'badges' => ['work'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'sort_order' => 2,
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'Hackathon AstroPi', 'cs' => 'Hackathon AstroPi'],
                'subtitle' => ['en' => '1st team place', 'cs' => '1. týmové místo'],
                'content' => [
                    'en' => "Space themed hackathon made possible by ESA, with 2 goals — Mission Zero and Mission Space Lab, in a team with Petr Machovec and Ondřej Kučera.\n\n**Mission Zero** — a Python program animating an 8x8 display on the AstroPi, reactive to the environment (change of heat or pressure).\n\n**Mission Space Lab** — a Python program that calculates the speed of the ISS by taking 2 pictures of Earth and matching anchor points.",
                    'cs' => "Hackathon s vesmírnou tematikou, který umožnila ESA, se dvěma cíli — Mission Zero a Mission Space Lab, v týmu s Petrem Machovcem a Ondřejem Kučerou.\n\n**Mission Zero** — program v Pythonu animovaný na displeji 8x8 na AstroPi, reaktivní na prostředí (změna tepla nebo tlaku).\n\n**Mission Space Lab** — program v Pythonu, který vypočítá rychlost ISS tím, že z ní pořídí 2 snímky Země a následně porovná kotevní body.",
                ],
                'links' => [['url' => 'https://www.spse.cz/clanek.php?id=1103', 'alt' => 'SPŠE']],
                'badges' => ['competition', 'python'],
            ],
            [
                'type' => 'work',
                'is_special' => false,
                'sort_order' => 3,
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'ByEvolution', 'cs' => 'ByEvolution'],
                'subtitle' => [
                    'en' => 'Work experience in Erasmus CIT+',
                    'cs' => 'Pracovní stáž na Erasmus CIT+',
                ],
                'content' => [
                    'en' => "I was selected for an Erasmus trip in Spain. Here I worked 14 days in ByEvolution Creative Factory, a firm that specializes in blockchain and crypto technology.\n\n- Automatization of application in Selenium\n- Front-end with HTML, Tailwind, Alpine.js",
                    'cs' => "Byl jsem vybrán na Erasmus do Španělska. Zde jsem 14 dní pracoval v ByEvolution Creative Factory, firmě, která se specializuje na blockchain a krypto technologie.\n\n- Automatizace aplikace v Seleniu\n- Front-end s HTML, Tailwind, Alpine.js",
                ],
                'links' => [['url' => 'https://byevolution.com/', 'alt' => 'ByEvolution']],
                'badges' => ['work', 'blockchain', 'javascript'],
            ],
            [
                'type' => 'work',
                'is_special' => true,
                'sort_order' => 4,
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'PekneWeby', 'cs' => 'PekneWeby'],
                'subtitle' => ['en' => 'Part-time job', 'cs' => 'Brigádní práce'],
                'content' => [
                    'en' => "Took part in complete rebranding of restaurant U Sladovny in Chrudim.\n\n- Took part in Front-end + Back-end of a website made in Symfony\n- [Website](https://www.usladovnychrudim.cz/)",
                    'cs' => "Účastnil jsem se kompletního rebrandingu restaurace U Sladovny v Chrudimi.\n\n- Účastnil jsem se Front-endu + Back-endu webových stránek vytvořených v Symfony\n- [Odkaz zde](https://www.usladovnychrudim.cz/)",
                ],
                'links' => [['url' => 'https://www.usladovnychrudim.cz/', 'alt' => 'U Sladovny']],
                'badges' => ['work', 'symfony', 'php'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'sort_order' => 5,
                'year' => ['en' => '2023', 'cs' => '2023'],
                'title' => ['en' => 'Cisco IT Essentials', 'cs' => 'Cisco IT Essentials'],
                'subtitle' => ['en' => 'Certificate', 'cs' => 'Certifikát'],
                'content' => ['en' => '', 'cs' => ''],
                'links' => [['url' => 'https://netacad.cz/it-essentials/', 'alt' => 'NetAcad']],
                'badges' => ['certificate', 'hardware'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'sort_order' => 6,
                'year' => ['en' => '2022 - now', 'cs' => '2022 - nyní'],
                'title' => ['en' => 'Student', 'cs' => 'Student'],
                'subtitle' => ['en' => 'SPŠE a VOŠ Pardubice', 'cs' => 'SPŠE a VOŠ Pardubice'],
                'content' => [
                    'en' => "School education with focus on full-stack web development and app development.\n\n- HTML + CSS + JS in front-end\n- PHP + Laravel in back-end\n- MySQL in databases\n- Java in app development\n- Basics in Photoshop and Illustrator\n- Basics in web design\n- Developed interest in cybersecurity",
                    'cs' => "Školní vzdělání se zaměřením na full-stack webový vývoj a vývoj aplikací.\n\n- HTML + CSS + JS ve front-endu\n- PHP + Laravel v back-endu\n- MySQL v databázích\n- Java ve vývoji aplikací\n- Základy Photoshopu a Illustratoru\n- Základy webového designu\n- Vyvinutý zájem o kybernetickou bezpečnost",
                ],
                'links' => [['url' => 'https://www.spse.cz/', 'alt' => 'SPŠE']],
                'badges' => ['education', 'java', 'php', 'laravel'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'sort_order' => 7,
                'year' => ['en' => '2021', 'cs' => '2021'],
                'title' => ['en' => 'IT-Slot', 'cs' => 'IT-Slot'],
                'subtitle' => [
                    'en' => '11th place out of 8320 students',
                    'cs' => '11. místo z 8320 studentů',
                ],
                'content' => ['en' => '', 'cs' => ''],
                'links' => [['url' => 'https://www.it-slot.cz/results/year/2021', 'alt' => 'IT-Slot']],
                'badges' => ['competition', 'it'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'sort_order' => 8,
                'year' => ['en' => '2021 - now', 'cs' => '2021 - nyní'],
                'title' => ['en' => 'ECDL', 'cs' => 'ECDL'],
                'subtitle' => ['en' => 'Certificate', 'cs' => 'Certifikát'],
                'content' => ['en' => '', 'cs' => ''],
                'links' => [['url' => 'https://www.ecdl.cz/', 'alt' => 'ECDL']],
                'badges' => ['certificate'],
            ],
        ];

        foreach ($entries as $entry) {
            $slugs = $entry['badges'] ?? [];
            unset($entry['badges']);

            $experience = Experience::create($entry);
            $experience->badges()->sync($badgeIds->only($slugs)->values()->all());
        }
    }
}
