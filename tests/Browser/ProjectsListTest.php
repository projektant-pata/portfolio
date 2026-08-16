<?php

use App\Models\Badge;
use App\Models\Project;
use Database\Seeders\SettingSeeder;

/**
 * Browser tests run inside the container against the seeded settings the dock
 * hero needs; the first visit compiles the page, so keep the timeout generous.
 */

/**
 * `#proj-seg-thumb` transitions transform/width over --t-fast (0.3s), so a
 * geometry read taken in the same tick as the click races the animation and
 * returns the pre-transition rect. Same fix LightThemeTest.php uses for its
 * own --t-base transition: wait it out before reading computed geometry.
 */
$segTransition = 1;

beforeEach(function () {
    $this->seed(SettingSeeder::class);

    $laravel = Badge::factory()->create(['slug' => 'laravel', 'name' => ['en' => 'Laravel'], 'color' => '#FF2D20']);
    $php = Badge::factory()->create(['slug' => 'php', 'name' => ['en' => 'PHP'], 'color' => '#777BB4']);

    Project::factory()->create(['year' => 2026, 'slug' => 'portfolio', 'header' => ['en' => 'Portfolio'], 'kind' => 'personal'])
        ->badges()->attach($laravel);
    Project::factory()->client()->create(['year' => 2025, 'slug' => 'u-sladovny', 'header' => ['en' => 'U Sladovny'], 'client' => 'PekneWeby'])
        ->badges()->attach($php);
    Project::factory()->create(['year' => 2022, 'slug' => 'spse-hub', 'header' => ['en' => 'SPSE Hub'], 'kind' => 'school']);
});

test('the kind segment filters the list and hides emptied year groups', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');

    $visibleRows = <<<'JS'
        document.querySelectorAll('.proj-item:not([hidden])').length
    JS;
    $visibleGroups = <<<'JS'
        document.querySelectorAll('.proj-group:not([hidden])').length
    JS;

    expect($page->script($visibleRows))->toBe(1)
        ->and($page->script($visibleGroups))->toBe(1);
});

test('two stack chips union rather than intersect', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-stack-filter="laravel"]');
    $page->click('[data-stack-filter="php"]');

    expect($page->script('document.querySelectorAll(".proj-item:not([hidden])").length'))->toBe(2);
});

test('the segment thumb moves to the pressed button', function () use ($segTransition) {
    $page = visit('/projects')->resize(1440, 900);

    $before = $page->script('document.getElementById("proj-seg-thumb").getBoundingClientRect().left');

    $page->click('[data-kind-filter="school"]');
    $page->wait($segTransition);

    $after = $page->script('document.getElementById("proj-seg-thumb").getBoundingClientRect().left');

    expect($after)->toBeGreaterThan($before);
});

test('filtering to nothing shows the empty state', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');
    $page->click('[data-stack-filter="laravel"]');

    expect($page->script('document.getElementById("proj-empty").hidden'))->toBeFalse();
});

test('two rows open at once', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('.proj-group:nth-of-type(1) .proj-item:nth-of-type(1) .proj-toggle');
    $page->click('.proj-group:nth-of-type(2) .proj-item:nth-of-type(1) .proj-toggle');

    expect($page->script('document.querySelectorAll(".proj-item.is-open").length'))->toBe(2);
});

test('the projects list never scrolls sideways', function (int $width) {
    $page = visit('/projects')->resize($width, 900);

    $overflow = <<<'JS'
        Math.round(document.documentElement.scrollWidth - document.documentElement.clientWidth)
    JS;

    expect($page->script($overflow))->toBeLessThanOrEqual(0);
})->with([1440, 1100, 760, 390]);

test('picking the client kind writes ?kind=client into the URL', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');

    expect($page->script('window.location.search'))->toBe('?kind=client');
});

test('clearing the filters empties the query string', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');
    expect($page->script('window.location.search'))->toBe('?kind=client');

    $page->click('#proj-clear');

    expect($page->script('window.location.search'))->toBe('');
});

test('loading the page with ?kind=client in the URL restores the filter on load', function () {
    $page = visit('/projects?kind=client')->resize(1440, 900);

    $visibleRows = <<<'JS'
        document.querySelectorAll('.proj-item:not([hidden])').length
    JS;
    $pressed = <<<'JS'
        document.querySelector('[data-kind-filter="client"]').getAttribute('aria-pressed')
    JS;

    expect($page->script($visibleRows))->toBe(1)
        ->and($page->script($pressed))->toBe('true');
});
