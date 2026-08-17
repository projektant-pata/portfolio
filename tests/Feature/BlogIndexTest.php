<?php

use App\Models\Article;
use App\Models\Badge;
use Database\Seeders\SettingSeeder;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

test('the blog page opens with the shared dock hero', function () {
    $this->get(route('blog'))
        ->assertOk()
        ->assertSee('class="dock-hero"', false)
        ->assertSee('aria-hidden="true">Blog<', false);
});

test('the listing shows published articles and hides drafts and scheduled ones', function () {
    $live = Article::factory()->published()->create(['header' => ['en' => 'A live post']]);
    Article::factory()->draft()->create(['header' => ['en' => 'A draft post']]);
    Article::factory()->scheduled()->create(['header' => ['en' => 'A scheduled post']]);

    $this->get(route('blog'))
        ->assertOk()
        ->assertSee('A live post')
        ->assertDontSee('A draft post')
        ->assertDontSee('A scheduled post')
        ->assertSee(route('blog.show', $live->slug), false);
});

test('the newest post is the lead row and the rest are not', function () {
    Article::factory()->published()->create(['date' => '2026-03-18', 'header' => ['en' => 'Newest']]);
    Article::factory()->published()->create(['date' => '2025-06-30', 'header' => ['en' => 'Oldest']]);

    $html = $this->get(route('blog'))->assertOk()->getContent();

    expect(substr_count($html, 'blog-row--lead'))->toBe(1)
        ->and(strpos($html, 'Newest'))->toBeLessThan(strpos($html, 'Oldest'));
});

test('a row without a thumbnail falls back to its archive numeral, oldest first', function () {
    Article::factory()->published()->create(['date' => '2025-06-30', 'thumbnail_url' => null]);
    Article::factory()->published()->create(['date' => '2026-03-18', 'thumbnail_url' => null]);

    $this->get(route('blog'))
        ->assertSee('blog-row--noimg', false)
        ->assertSee('>01<', false)
        ->assertSee('>02<', false);
});

test('the date rail is localised', function () {
    Article::factory()->published()->create(['date' => '2026-03-18']);

    $this->get(route('blog'))
        ->assertSee('<time datetime="2026-03-18"', false)
        ->assertSee('>Mar 2026<', false);

    $this->withSession(['locale' => 'cs'])
        ->get(route('blog'))
        ->assertSee('>18.<', false)
        ->assertSee('>3. 2026<', false);
});

test('row badges render as coloured pills, not nested links', function () {
    $badge = Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F59E0B']);
    Article::factory()->published()->create()->badges()->attach($badge);

    $this->get(route('blog'))
        ->assertSee('--badge-color: #F59E0B', false)
        // The whole row is already an <a>; a badge link inside it would be
        // invalid HTML. Linked badges live in the article header instead.
        ->assertDontSee('?badge=hardware', false);
});

test('the page renders exactly one h1', function () {
    Article::factory()->published()->count(3)->create();

    $html = $this->get(route('blog'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
});

test('the blog archive is introduced by a section head carrying the count', function () {
    Article::factory()->published()->create();

    $this->get(route('blog'))
        ->assertSee('<p class="sechead-eyebrow">Archive</p>', false)
        ->assertSee('Everything I <em>published</em>', false)
        ->assertSee('sechead-note', false)
        // The note prop renders unescaped ({!! !!} in section-head.blade.php)
        // so the count's <b> reaches the page as real markup, not entities —
        // assert the raw tag to catch a regression to {{ }} escaping it.
        ->assertSee('<b>1</b> of 1 post', false);
});
