<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'hero_suptitle' => ['en' => '👋 Hello world!', 'cs' => '👋 Ahoj světe!'],
            'hero_title' => ['en' => 'I’m <span>projektant-pata</span>,', 'cs' => 'Jsem <span>projektant-pata</span>,'],
            'hero_roles' => [
                'en' => ['Full-stack <span>developer</span>', 'Chess <span>player</span>', 'Spring Boot <span>engineer</span>', 'Laravel <span>craftsman</span>', 'Problem <span>solver</span>'],
                'cs' => ['Full-stack <span>vývojář</span>', '<span>Šachista</span>', 'Spring Boot <span>inženýr</span>', 'Laravel <span>řemeslník</span>', 'Řešitel <span>problémů</span>'],
            ],
            'stats_title' => ['en' => 'My Stats', 'cs' => 'Moje statistiky'],
            'tools_title' => ['en' => 'Tools', 'cs' => 'Nástroje'],
            'reviews_title' => ['en' => 'Reviews', 'cs' => 'Reference'],
            'about_title' => ['en' => 'About me', 'cs' => 'O mně'],
            'about_hero_suptitle' => ['en' => '👤 whoami', 'cs' => '👤 whoami'],
            'about_hero_title' => ['en' => 'A bit <span>about me</span>,', 'cs' => 'Něco <span>o mně</span>,'],
            'about_hero_roles' => [
                'en' => ['Life <span>outside code</span>', '<span>Arch</span> btw', '<span>Home</span> labber', 'Student <span>by day</span>', 'Freelancer <span>by night</span>', '<span>Chess</span> player'],
                'cs' => ['Život <span>mimo kód</span>', '<span>Arch</span>, mimochodem', 'Homelab <span>nadšenec</span>', 'Ve dne <span>student</span>', 'V noci <span>freelancer</span>', '<span>Šachista</span>'],
            ],
            'experience_hero_suptitle' => ['en' => '🗓️ Where I’ve been', 'cs' => '🗓️ Kudy jsem prošel'],
            'experience_hero_title' => ['en' => 'My <span>journey</span>,', 'cs' => 'Moje <span>cesta</span>,'],
            'experience_hero_roles' => [
                'en' => ['Certificates & <span>competitions</span>', '<span>PHP</span> enjoyer', '<span>Java</span> lover', '<span>JavaScript</span> hater!!', 'From <span>2021</span> to now'],
                'cs' => ['Certifikáty a <span>soutěže</span>', '<span>PHP</span> nadšenec', '<span>Javu</span> miluju', '<span>JavaScript</span> nesnáším!!', 'Od <span>2021</span> dodnes'],
            ],
            'projects_hero_suptitle' => ['en' => '🛠️ What I’ve built', 'cs' => '🛠️ Co jsem postavil'],
            'projects_hero_title' => ['en' => 'Things I’ve <span>shipped</span>,', 'cs' => 'Věci, co jsem <span>postavil</span>,'],
            'projects_hero_roles' => [
                'en' => ['Side projects that <span>survived</span>', 'Laravel <span>monoliths</span>', 'Spring Boot <span>APIs</span>', 'Shipped with <span>Docker</span>', 'Built, broken, <span>rebuilt</span>'],
                'cs' => ['Vedlejšáky, co <span>přežily</span>', 'Laravel <span>monolity</span>', 'Spring Boot <span>API</span>', 'Nasazeno přes <span>Docker</span>', 'Postaveno, rozbito, <span>opraveno</span>'],
            ],
            'blog_hero_suptitle' => ['en' => '📝 Thinking out loud', 'cs' => '📝 Myslím nahlas'],
            'blog_hero_title' => ['en' => 'Build logs, and <span>what broke</span>,', 'cs' => 'Zápisky a <span>co se rozbilo</span>,'],
            'blog_hero_roles' => [
                'en' => ['Writes only when <span>something breaks</span>', 'Markdown <span>maximalist</span>', '<span>Arch</span> btw', 'Still not a <span>frontend dev</span>', 'Commits at 2am, <span>posts at 2pm</span>'],
                'cs' => ['Píše, jen když se <span>něco rozbije</span>', 'Markdown <span>fanatik</span>', '<span>Arch</span>, mimochodem', 'Pořád ne <span>frontend vývojář</span>', 'Commituje ve 2 ráno, <span>publikuje ve 2 odpoledne</span>'],
            ],
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Drop any keys no longer defined here (keeps the table canonical).
        Setting::whereNotIn('key', array_keys($settings))->delete();
    }
}
