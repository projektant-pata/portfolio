<?php

use App\Models\Experience;
use App\Models\Project;
use App\Models\Setting;

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

test('featured project links open in a new tab with rel noopener', function () {
    $project = Project::factory()->create(['sort_order' => 0]);
    $project->links()->create([
        'url' => 'https://example.com',
        'alt' => ['en' => 'Visit website'],
        'img_url' => 'images/projects/icons/web.webp',
    ]);

    $this->get(route('home'))
        ->assertSee('href="https://example.com" target="_blank" rel="noopener noreferrer"', false);
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

test('experience links open in a new tab with rel noopener', function () {
    Experience::factory()->create([
        'type' => 'life',
        'title' => ['en' => 'Linked Role'],
        'links' => [['url' => 'https://example.com']],
    ]);

    $this->get(route('home'))
        ->assertSee('target="_blank" rel="noopener noreferrer"', false);
});

test('hero_roles markup survives to the page unescaped', function () {
    Setting::updateOrCreate(['key' => 'hero_roles'], [
        'value' => ['en' => ['Full-stack <span>developer</span>'], 'cs' => ['Full-stack <span>vývojář</span>']],
    ]);

    $this->get(route('home'))
        ->assertSee('Full-stack <span>developer</span>', false)
        ->assertDontSee('Full-stack &lt;span&gt;developer&lt;/span&gt;', false);
});
