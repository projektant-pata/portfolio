<?php

use App\Models\Article;
use App\Models\Badge;

test('read next prefers posts sharing a badge, then fills up by date', function () {
    $hardware = Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F59E0B']);

    $current = Article::factory()->published()->create(['date' => '2026-03-18', 'header' => ['en' => 'Current']]);
    $current->badges()->attach($hardware);

    $sibling = Article::factory()->published()->create(['date' => '2024-01-01', 'header' => ['en' => 'Old sibling']]);
    $sibling->badges()->attach($hardware);

    Article::factory()->published()->create(['date' => '2026-02-02', 'header' => ['en' => 'Recent stranger']]);
    Article::factory()->published()->create(['date' => '2025-01-01', 'header' => ['en' => 'Old stranger']]);

    $this->get(route('blog.show', $current->slug))
        ->assertOk()
        ->assertSee('Read next')
        ->assertSee('Old sibling')
        ->assertSee('Recent stranger')
        ->assertDontSee('Old stranger');
});

test('read next never includes the current, draft or scheduled articles', function () {
    $current = Article::factory()->published()->create(['header' => ['en' => 'Current']]);
    Article::factory()->draft()->create(['header' => ['en' => 'A draft']]);
    Article::factory()->scheduled()->create(['header' => ['en' => 'A scheduled one']]);

    $this->get(route('blog.show', $current->slug))
        ->assertOk()
        ->assertDontSee('A draft')
        ->assertDontSee('A scheduled one')
        // Nothing else is publishable, so the block itself must be absent —
        // which also proves the current article did not list itself.
        ->assertDontSee('Read next');
});

test('read next is omitted when there is nothing else published', function () {
    $only = Article::factory()->published()->create();

    $this->get(route('blog.show', $only->slug))->assertDontSee('Read next');
});
