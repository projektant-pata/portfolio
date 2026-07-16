<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'projektant-pata',
            'email' => 'richard.hyvl@gmail.com',
            'password' => 'Heslo123!',
            'profile_picture_url' => '/images/users/projektant-pata.jpg',
        ]);
    }
}
