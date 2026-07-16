<?php

use App\Models\Project;
use App\Models\User;

test('database seeder seeds the admin user and projects', function () {
    $this->seed(Database\Seeders\DatabaseSeeder::class);

    expect(User::count())->toBe(1);
    expect(Project::pluck('slug')->all())
        ->toContain('spse-hub', 'u-sladovny', 'portfolio');
});
