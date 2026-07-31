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
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Drop any keys no longer defined here (keeps the table canonical).
        Setting::whereNotIn('key', array_keys($settings))->delete();
    }
}
