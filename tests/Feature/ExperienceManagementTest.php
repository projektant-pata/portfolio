<?php

use App\Models\Experience;
use App\Models\User;
use Livewire\Livewire;

test('experience stores title as json with locale keys', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer', 'cs' => 'Softwarový vývojář'],
    ]);

    expect($experience->fresh()->title)->toBe(['en' => 'Software Developer', 'cs' => 'Softwarový vývojář']);
});

test('getTranslation returns value for requested locale', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer', 'cs' => 'Softwarový vývojář'],
    ]);

    expect($experience->getTranslation('title', 'cs'))->toBe('Softwarový vývojář');
});

test('getTranslation falls back to english when locale is missing', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer'],
    ]);

    expect($experience->getTranslation('title', 'cs'))->toBe('Software Developer');
});

test('getTranslation returns empty string when field is null', function () {
    $experience = Experience::factory()->create([
        'content' => null,
    ]);

    expect($experience->getTranslation('content', 'en'))->toBe('');
});

test('manage experiences page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.experiences'))
        ->assertOk();
});

test('experience year is stored as json with locale keys', function () {
    $experience = Experience::factory()->create([
        'year' => ['en' => '2022 – present', 'cs' => '2022 – nyní'],
    ]);

    expect($experience->fresh()->year)->toBe(['en' => '2022 – present', 'cs' => '2022 – nyní']);
});

test('can create experience with i18n year', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => 'Developer', 'cs' => ''])
        ->set('year', ['en' => '2024 – present', 'cs' => '2024 – nyní'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Experience::first()->year)->toBe(['en' => '2024 – present', 'cs' => '2024 – nyní']);
});

test('can create experience with english title', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => 'Software Developer', 'cs' => ''])
        ->set('subtitle', ['en' => 'Acme Corp', 'cs' => ''])
        ->set('content', ['en' => '## Overview\n\nGreat job.', 'cs' => ''])
        ->set('year', ['en' => '2024', 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    $experience = Experience::first();
    expect($experience->title)->toBe(['en' => 'Software Developer'])
        ->and($experience->subtitle)->toBe(['en' => 'Acme Corp'])
        ->and($experience->content)->toBe(['en' => '## Overview\n\nGreat job.']);
});

test('create experience requires english title', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['title.en']);
});

test('can edit experience and update translations', function () {
    $user = User::factory()->create();
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Developer'],
        'content' => ['en' => 'Some content'],
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->call('openEdit', $experience->id)
        ->assertSet('title', ['en' => 'Developer', 'cs' => ''])
        ->assertSet('content', ['en' => 'Some content', 'cs' => ''])
        ->set('title', ['en' => 'Developer', 'cs' => 'Vývojář'])
        ->call('save')
        ->assertHasNoErrors();

    expect($experience->fresh()->title)->toBe(['en' => 'Developer', 'cs' => 'Vývojář']);
});
