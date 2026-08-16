<?php

use App\Models\Badge;
use App\Models\Link;
use App\Models\Project;

test('projects page returns 200', function () {
    Project::factory()->create();

    $this->get(route('projects'))->assertOk();
});

test('projects are grouped by year, newest first', function () {
    Project::factory()->create(['year' => 2022, 'header' => ['en' => 'Old one']]);
    Project::factory()->create(['year' => 2026, 'header' => ['en' => 'New one']]);

    $response = $this->get(route('projects'));

    expect($response->viewData('projects')->keys()->all())->toBe([2026, 2022]);
});

test('sort order breaks ties inside a year', function () {
    Project::factory()->create(['year' => 2026, 'sort_order' => 2, 'header' => ['en' => 'Second']]);
    Project::factory()->create(['year' => 2026, 'sort_order' => 1, 'header' => ['en' => 'First']]);

    $response = $this->get(route('projects'));

    $headers = $response->viewData('projects')[2026]->map(fn ($p) => $p->header['en'])->all();

    expect($headers)->toBe(['First', 'Second']);
});

test('each year prints one head carrying its project count', function () {
    Project::factory()->count(2)->create(['year' => 2026]);
    Project::factory()->create(['year' => 2022]);

    $html = $this->get(route('projects'))->getContent();

    expect(substr_count($html, 'class="proj-yhead"'))->toBe(2);
    expect($html)->toContain('2 projects')->toContain('1 project');
});

test('the project name is the row heading and the year is never printed inside a row', function () {
    Project::factory()->create(['year' => 2026, 'header' => ['en' => 'Portfolio']]);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('<h3 class="proj-name">Portfolio</h3>');
    expect($html)->not->toContain('class="proj-year"');
});

test('the rail prints kind and status', function () {
    Project::factory()->client()->create(['client' => 'PekneWeby', 'status' => 'live']);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('Client · PekneWeby')
        ->toContain('data-state="live"')
        ->toContain('Live');
});

test('a project without a status prints no status line', function () {
    Project::factory()->create(['status' => null]);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->not->toContain('proj-status');
});

test('the page passes only badges attached to a project as stack filters', function () {
    $attached = Badge::factory()->create();
    $unattached = Badge::factory()->create();
    Project::factory()->create()->badges()->attach($attached);

    $stackBadges = $this->get(route('projects'))->viewData('stackBadges');

    expect($stackBadges->pluck('id')->all())->toBe([$attached->id])
        ->and($stackBadges->pluck('id')->all())->not->toContain($unattached->id);
});

test('the czech locale renders czech project copy', function () {
    Project::factory()->create([
        'header' => ['en' => 'Portfolio', 'cs' => 'Portfólio'],
        'description' => ['en' => 'English body', 'cs' => 'Český text'],
        'status' => 'archived',
    ]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('projects'))
        ->assertSee('Portfólio')
        ->assertSee('Český text')
        ->assertSee('Archivováno');
});

test('a page with no projects renders the empty list message', function () {
    $this->get(route('projects'))->assertSee('No projects yet');
});
