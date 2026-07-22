<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Order matters: badges must exist before
     * projects/experiences attach them. All child seeders are re-runnable.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BadgesSeeder::class,
            ProjectsSeeder::class,
            ExperienceSeeder::class,
            SettingSeeder::class,
            StatSeeder::class,
            ReviewSeeder::class,
            AboutCardSeeder::class,
        ]);
    }
}
