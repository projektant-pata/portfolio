<?php

use App\Models\Article;
use App\Models\Badge;
use App\Models\User;
use Livewire\Livewire;

test('manage articles page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.articles'))
        ->assertOk();
});

test('can create article with english fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->set('header', ['en' => 'My First Article', 'cs' => ''])
        ->set('description', ['en' => 'A short intro.', 'cs' => ''])
        ->set('content', ['en' => '## Hello\n\nWorld.', 'cs' => ''])
        ->set('slug', 'my-first-article')
        ->set('date', '2026-04-07')
        ->call('save')
        ->assertHasNoErrors();

    $article = Article::first();
    expect($article->header)->toBe(['en' => 'My First Article'])
        ->and($article->slug)->toBe('my-first-article');
});

test('create article requires english header', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->set('header', ['en' => '', 'cs' => ''])
        ->set('slug', 'test')
        ->set('date', '2026-04-07')
        ->call('save')
        ->assertHasErrors(['header.en']);
});

test('can edit article and update translations', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create([
        'header' => ['en' => 'My Article'],
        'description' => ['en' => 'Short.'],
        'content' => ['en' => 'Content here.'],
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('openEdit', $article->id)
        ->assertSet('header', ['en' => 'My Article', 'cs' => ''])
        ->set('header', ['en' => 'My Article', 'cs' => 'Můj článek'])
        ->call('save')
        ->assertHasNoErrors();

    expect($article->fresh()->header)->toBe(['en' => 'My Article', 'cs' => 'Můj článek']);
});

test('can delete article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('confirmDelete', $article->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Article::count())->toBe(0);
});
