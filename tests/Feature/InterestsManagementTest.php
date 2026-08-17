<?php

use App\Models\Interest;
use App\Models\User;
use Livewire\Livewire;

test('an admin can create an interest', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->set('interestTitle', ['en' => 'Reading', 'cs' => 'Čtení'])
        ->set('highlight', ['en' => '', 'cs' => ''])
        ->set('body', ['en' => 'Books.', 'cs' => 'Knihy.'])
        ->set('meta', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    expect(Interest::first()->title)->toEqual(['en' => 'Reading', 'cs' => 'Čtení']);
});

test('an interest requires an english title and body', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->set('interestTitle', ['en' => '', 'cs' => ''])
        ->set('body', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['interestTitle.en', 'body.en']);
});

test('an admin can edit and delete an interest', function () {
    $interest = Interest::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->call('openEdit', $interest->id)
        ->set('interestTitle', ['en' => 'Renamed', 'cs' => 'Přejmenováno'])
        ->call('save')
        ->assertHasNoErrors();

    expect($interest->fresh()->getTranslation('title', 'en'))->toBe('Renamed');

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->call('confirmDelete', $interest->id)
        ->call('delete');

    expect(Interest::count())->toBe(0);
});

test('reordering interests rewrites sort order', function () {
    $first = Interest::factory()->create(['sort_order' => 0]);
    $second = Interest::factory()->create(['sort_order' => 1]);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->call('reorder', $second->id, 0);

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});

test('an interest keeps its highlight and image path', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->set('interestTitle', ['en' => 'Chess since 2nd grade', 'cs' => 'Šachy od druhé třídy'])
        ->set('highlight', ['en' => '2nd grade', 'cs' => 'druhé třídy'])
        ->set('body', ['en' => 'Tournaments.', 'cs' => 'Turnaje.'])
        ->set('meta', ['en' => '1684 elo', 'cs' => '1684 elo'])
        ->set('imagePath', 'images/about/chess.webp')
        ->call('save')
        ->assertHasNoErrors();

    $interest = Interest::first();

    expect($interest->highlight)->toEqual(['en' => '2nd grade', 'cs' => 'druhé třídy'])
        ->and($interest->image_path)->toBe('images/about/chess.webp')
        ->and($interest->highlightedTitle('en'))->toContain('<span>2nd grade</span>');
});
