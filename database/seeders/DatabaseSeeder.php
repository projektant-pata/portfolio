<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => env('SEED_ADMIN_NAME', 'admin'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('SEED_ADMIN_PASSWORD', Str::password(16)),
            'profile_picture_url' => env('SEED_ADMIN_PICTURE'),
        ]);

        $this->call(ProjectsSeeder::class);
    }
}
