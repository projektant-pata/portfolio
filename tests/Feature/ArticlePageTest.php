<?php

use App\Models\Article;
use App\Models\Badge;

test('a published article renders its shell and body', function () {
    $article = Article::factory()->published()->create([
        'slug' => 'raspberry-pi-room-temperature',
        'header' => ['en' => 'Teaching a Pi to notice the room is on fire'],
        'description' => ['en' => 'A DHT22, a Pi Zero, and three weeks of learning.'],
        'content' => ['en' => "## The sensor\n\nIt was never the sensor.\n\n| a | b |\n| --- | --- |\n| 1 | 2 |"],
        'date' => '2026-03-18',
    ]);

    $this->get(route('blog.show', $article->slug))
        ->assertOk()
        ->assertSee('Teaching a Pi to notice the room is on fire')
        ->assertSee('A DHT22, a Pi Zero, and three weeks of learning.')
        ->assertSee('18 March 2026')
        ->assertSee('min read')
        ->assertSee('<h2>The sensor</h2>', false)
        ->assertSee('<div class="blog-table">', false)
        ->assertSee(route('blog'), false);
});

test('a draft or scheduled article 404s', function () {
    $draft = Article::factory()->draft()->create();
    $scheduled = Article::factory()->scheduled()->create();

    $this->get(route('blog.show', $draft->slug))->assertNotFound();
    $this->get(route('blog.show', $scheduled->slug))->assertNotFound();
});

test('an unknown slug 404s', function () {
    $this->get('/blog/nope-not-here')->assertNotFound();
});

test('the cover band is omitted when there is no thumbnail', function () {
    $with = Article::factory()->published()->create(['thumbnail_url' => 'https://cdn.test/cover.jpg']);
    $without = Article::factory()->published()->create(['thumbnail_url' => null]);

    $this->get(route('blog.show', $with->slug))->assertSee('art-cover', false);
    $this->get(route('blog.show', $without->slug))->assertDontSee('art-cover', false);
});

test('article badges link back to the filtered listing', function () {
    $badge = Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F59E0B']);
    $article = Article::factory()->published()->create();
    $article->badges()->attach($badge);

    $this->get(route('blog.show', $article->slug))
        ->assertSee(route('blog').'?badge=hardware', false);
});

test('the czech locale renders the czech body and date', function () {
    $article = Article::factory()->published()->create([
        'header' => ['en' => 'English title', 'cs' => 'Český titulek'],
        'content' => ['en' => 'English body.', 'cs' => 'České tělo.'],
        'date' => '2026-03-18',
    ]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('blog.show', $article->slug))
        ->assertSee('Český titulek')
        ->assertSee('České tělo.')
        ->assertSee('18. března 2026')
        ->assertDontSee('English body.');
});

test('the article page renders exactly one h1', function () {
    $article = Article::factory()->published()->create();

    $html = $this->get(route('blog.show', $article->slug))->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
});
