<?php

use App\Models\Project;
use App\Models\User;

test('database seeder seeds the admin user and projects', function () {
    $this->seed(Database\Seeders\DatabaseSeeder::class);

    expect(User::count())->toBe(1);
    expect(Project::pluck('slug')->all())
        ->toContain('spse-hub', 'u-sladovny', 'portfolio');
});

test('the projects seeder gives every project a kind, a status and a bilingual role', function () {
    $this->seed(\Database\Seeders\BadgesSeeder::class);
    $this->seed(\Database\Seeders\ProjectsSeeder::class);

    $projects = \App\Models\Project::all();

    expect($projects)->toHaveCount(3);

    $projects->each(function ($project) {
        expect($project->kind)->toBeIn(\App\Models\Project::KINDS)
            ->and($project->status)->toBeIn(\App\Models\Project::STATUSES)
            ->and($project->role)->toHaveKeys(['en', 'cs']);
    });

    expect($projects->firstWhere('slug', 'u-sladovny')->client)->toBe('PekneWeby');
});

test('the projects seeder types every link', function () {
    $this->seed(\Database\Seeders\BadgesSeeder::class);
    $this->seed(\Database\Seeders\ProjectsSeeder::class);

    $kinds = \App\Models\Link::pluck('kind')->unique()->values();

    expect($kinds->every(fn ($kind) => in_array($kind, \App\Models\Link::KINDS, true)))->toBeTrue();
    expect(\App\Models\Link::where('url', 'like', '%github.com%')->pluck('kind')->unique()->all())->toBe(['repo']);
});
