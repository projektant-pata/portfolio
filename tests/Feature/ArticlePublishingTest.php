<?php

use App\Models\Article;
use App\Models\User;
use Livewire\Livewire;

test('the published scope returns only articles published in the past', function () {
    $live = Article::factory()->published()->create();
    $draft = Article::factory()->draft()->create();
    $scheduled = Article::factory()->scheduled()->create();

    $ids = Article::published()->pluck('id')->all();

    expect($ids)->toContain($live->id)
        ->and($ids)->not->toContain($draft->id)
        ->and($ids)->not->toContain($scheduled->id);
});

test('isPublished reflects the gate', function () {
    expect(Article::factory()->published()->create()->isPublished())->toBeTrue()
        ->and(Article::factory()->draft()->create()->isPublished())->toBeFalse()
        ->and(Article::factory()->scheduled()->create()->isPublished())->toBeFalse();
});

test('the admin form saves and clears the publish time', function () {
    $user = User::factory()->create();
    $article = Article::factory()->draft()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('openEdit', $article->id)
        ->set('published_at', '2026-08-01T09:30')
        ->call('save');

    expect($article->fresh()->published_at->format('Y-m-d H:i'))->toBe('2026-08-01 09:30');

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('openEdit', $article->id)
        ->set('published_at', '')
        ->call('save');

    expect($article->fresh()->published_at)->toBeNull();
});
