<?php

use App\Models\Experience;
use App\Models\Interest;
use App\Models\Link;
use App\Models\PathNode;
use App\Models\Project;
use App\Models\Stat;
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

test('the database seeder fills the about page with path nodes, interests and settings', function () {
    $this->seed(DatabaseSeeder::class);

    expect(PathNode::count())->toBe(5)
        ->and(PathNode::where('is_now', true)->count())->toBe(1)
        ->and(Interest::count())->toBe(3)
        ->and(Experience::where('show_on_about', true)->count())->toBeGreaterThanOrEqual(4)
        ->and(Stat::where('tier', 'hero')->count())->toBe(2)
        ->and(Stat::where('tier', 'soft')->count())->toBe(2);
});

test('every seeded about row carries both locales', function () {
    $this->seed(DatabaseSeeder::class);

    PathNode::all()->each(function (PathNode $node) {
        expect($node->year)->toHaveKeys(['en', 'cs'])
            ->and($node->title)->toHaveKeys(['en', 'cs'])
            ->and($node->body)->toHaveKeys(['en', 'cs']);
    });

    Interest::all()->each(function (Interest $interest) {
        expect($interest->title)->toHaveKeys(['en', 'cs'])
            ->and($interest->body)->toHaveKeys(['en', 'cs'])
            ->and($interest->meta)->toHaveKeys(['en', 'cs']);
    });
});

test('the hero stats carry a supporting note in both locales', function () {
    $this->seed(DatabaseSeeder::class);

    Stat::where('tier', 'hero')->get()->each(
        fn (Stat $stat) => expect($stat->note)->toHaveKeys(['en', 'cs'])
    );
});
