<?php

use App\Models\Link;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\BadgesSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProjectsSeeder;

test('database seeder seeds the admin user and projects', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1);
    expect(Project::pluck('slug')->all())
        ->toContain('spse-hub', 'u-sladovny', 'portfolio');
});

test('the projects seeder gives every project a kind, a status and a bilingual role', function () {
    $this->seed(BadgesSeeder::class);
    $this->seed(ProjectsSeeder::class);

    $projects = Project::all();

    expect($projects)->toHaveCount(3);

    $projects->each(function ($project) {
        expect($project->kind)->toBeIn(Project::KINDS)
            ->and($project->status)->toBeIn(Project::STATUSES)
            ->and($project->role)->toHaveKeys(['en', 'cs']);
    });

    expect($projects->firstWhere('slug', 'u-sladovny')->client)->toBe('PekneWeby');
});

test('the projects seeder types every link', function () {
    $this->seed(BadgesSeeder::class);
    $this->seed(ProjectsSeeder::class);

    $kinds = Link::pluck('kind')->unique()->values();

    expect($kinds->every(fn ($kind) => in_array($kind, Link::KINDS, true)))->toBeTrue();
    expect(Link::where('url', 'like', '%github.com%')->pluck('kind')->unique()->all())->toBe(['repo']);
});
