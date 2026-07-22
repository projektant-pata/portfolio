<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'richard.hyvl@gmail.com')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Richard Hyvl'),
                'password' => env('SEED_ADMIN_PASSWORD', 'a'), // dev/test login; hashed by cast
                'email_verified_at' => now(),
                'profile_picture_url' => env('SEED_ADMIN_PICTURE'),
            ],
        );

        // is_admin is excluded from mass assignment, so grant it explicitly.
        $user->forceFill(['is_admin' => true])->save();
    }
}
