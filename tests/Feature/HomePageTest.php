<?php

use App\Models\Experience;
use App\Models\Project;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tool;

test('home page returns 200', function () {
    $this->get(route('home'))->assertOk();
});

test('home page features projects from the database', function () {
    Project::factory()->create(['sort_order' => 0, 'header' => ['en' => 'First Featured']]);
    Project::factory()->create(['sort_order' => 1, 'header' => ['en' => 'Second Featured']]);

    $this->get(route('home'))
        ->assertSee('First Featured')
        ->assertSee('Second Featured');
});

test('home page teases only the first two projects', function () {
    Project::factory()->create(['sort_order' => 0, 'header' => ['en' => 'First Featured']]);
    Project::factory()->create(['sort_order' => 1, 'header' => ['en' => 'Second Featured']]);
    Project::factory()->create(['sort_order' => 2, 'header' => ['en' => 'Third Featured']]);

    $this->get(route('home'))
        ->assertSee('Second Featured')
        ->assertDontSee('Third Featured');
});

test('featured project row renders the project name, kind, and data-kind attribute', function () {
    $project = Project::factory()->create([
        'sort_order' => 0,
        'kind' => 'personal',
        'header' => ['en' => 'Featured One'],
    ]);

    $this->get(route('home'))
        ->assertSee('<h3 class="proj-name">Featured One</h3>', false)
        ->assertSee('data-kind="personal"', false)
        ->assertSee('Personal');
});

test('home page renders projects in the locale of the request', function () {
    Project::factory()->create([
        'sort_order' => 0,
        'header' => ['en' => 'English Header', 'cs' => 'Czech Header'],
    ]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('home'))
        ->assertSee('Czech Header')
        ->assertDontSee('English Header');
});

test('the home timeline is a static list — entry links live on the experience page', function () {
    Experience::factory()->create([
        'type' => 'work',
        'title' => ['en' => 'Linked Role'],
        'links' => [['url' => 'https://example.com/role']],
    ]);

    $this->get(route('home'))
        ->assertSee('Linked Role')
        ->assertDontSee('https://example.com/role');

    $this->get(route('experience'))
        ->assertSee('href="https://example.com/role" target="_blank" rel="noopener"', false);
});

test('home page renders every seeded review inside the carousel track', function () {
    Review::query()->delete();
    Review::factory()->create(['sort_order' => 0, 'name' => 'First Reviewer']);
    Review::factory()->create(['sort_order' => 1, 'name' => 'Second Reviewer']);
    Review::factory()->create(['sort_order' => 2, 'name' => 'Third Reviewer']);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('data-reviews-carousel', false)
        ->assertSee('reviews-carousel-viewport', false)
        ->assertSeeInOrder(['First Reviewer', 'Second Reviewer', 'Third Reviewer']);
});

test('hero_roles markup survives to the page unescaped', function () {
    Setting::updateOrCreate(['key' => 'hero_roles'], [
        'value' => [
            'en' => ['Full-stack <span>developer</span>', 'Chess <span>player</span>'],
            'cs' => ['Full-stack <span>vývojář</span>', '<span>Šachista</span>'],
        ],
    ]);

    $this->get(route('home'))
        ->assertSee('Full-stack <span>developer</span>', false)
        ->assertDontSee('Full-stack &lt;span&gt;developer&lt;/span&gt;', false);
});

test('the home toolbox shows only the tools flagged for home, no rotation chips', function () {
    Tool::factory()->onHome()->create(['name' => 'Spring Boot', 'color' => '#6DB33F', 'state' => 'using']);
    Tool::factory()->create(['name' => 'Proxmox']);
    Tool::factory()->chip()->create(['name' => 'VS Code']);

    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('tk--list')
        ->and($html)->toContain('Spring Boot')
        ->and($html)->toContain('--bc: #6DB33F')
        ->and($html)->not->toContain('VS Code')
        ->and($html)->not->toContain('Proxmox');
});

test('an empty toolbox draws no rows under the tools head', function () {
    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('id="tools"')
        ->and($html)->not->toContain('tk--list');
});
