<?php

use App\Models\Article;
use App\Models\Badge;
use Database\Seeders\SettingSeeder;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

test('a badge param narrows the listing and the count', function () {
    $hardware = Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F59E0B']);
    $tagged = Article::factory()->published()->create(['header' => ['en' => 'Tagged post']]);
    $tagged->badges()->attach($hardware);
    Article::factory()->published()->create(['header' => ['en' => 'Untagged post']]);

    $this->get(route('blog', ['badge' => 'hardware']))
        ->assertOk()
        ->assertSee('Tagged post')
        ->assertDontSee('Untagged post')
        ->assertSee('blog-filter', false)
        ->assertSee('Filtered by')
        ->assertSee('<b>1</b> of 2 posts', false);
});

test('show all clears the filter', function () {
    Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F59E0B']);

    $this->get(route('blog', ['badge' => 'hardware']))
        ->assertSee('href="'.route('blog').'" class="blog-reset"', false)
        ->assertSee('Show all');
});

test('an unknown badge shows the filtered empty state, not a 404', function () {
    Article::factory()->published()->create();

    $this->get(route('blog', ['badge' => 'nope']))
        ->assertOk()
        ->assertSee('Nothing under that badge yet.');
});

test('no posts at all shows the unfiltered empty state', function () {
    $this->get(route('blog'))
        ->assertOk()
        ->assertSee('Nothing published yet.');
});

test('the filter line is absent without the param', function () {
    Article::factory()->published()->create();

    $this->get(route('blog'))->assertDontSee('blog-filter', false);
});
