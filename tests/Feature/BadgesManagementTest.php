<?php

use App\Models\Badge;
use App\Models\User;
use Livewire\Livewire;

test('badge name is stored as json with locale keys', function () {
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel', 'cs' => 'Laravel'],
        'color' => 'red',
    ]);

    expect($badge->fresh()->name)->toBe(['en' => 'Laravel', 'cs' => 'Laravel']);
});

test('manage badges page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.badges'))
        ->assertOk();
});

test('can create badge with english name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('slug', 'laravel')
        ->set('color', 'red')
        ->call('save')
        ->assertHasNoErrors();

    $badge = Badge::first();
    expect($badge->name)->toBe(['en' => 'Laravel'])
        ->and($badge->slug)->toBe('laravel');
});

test('create badge requires english name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => '', 'cs' => ''])
        ->set('slug', 'laravel')
        ->call('save')
        ->assertHasErrors(['name.en']);
});

test('can edit badge and update translations', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel'],
        'color' => 'red',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('openEdit', $badge->id)
        ->assertSet('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('name', ['en' => 'Laravel', 'cs' => 'Laravel'])
        ->call('save')
        ->assertHasNoErrors();

    expect($badge->fresh()->name)->toBe(['en' => 'Laravel', 'cs' => 'Laravel']);
});

test('can delete badge', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel'],
        'color' => 'red',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('confirmDelete', $badge->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Badge::count())->toBe(0);
});
