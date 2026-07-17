<?php

use App\Models\Badge;
use App\Models\Experience;
use App\Models\User;
use Livewire\Livewire;

test('badge name is stored as json with locale keys', function () {
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel', 'cs' => 'Laravel'],
        'color' => '#60A5FA',
    ]);

    expect($badge->fresh()->name)->toEqual(['en' => 'Laravel', 'cs' => 'Laravel']);
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
        ->set('color', '#60A5FA')
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
        'color' => '#60A5FA',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('openEdit', $badge->id)
        ->assertSet('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('name', ['en' => 'Laravel', 'cs' => 'Laravel'])
        ->call('save')
        ->assertHasNoErrors();

    expect($badge->fresh()->name)->toEqual(['en' => 'Laravel', 'cs' => 'Laravel']);
});

test('badge experiences relation uses the experience_badge pivot table', function () {
    $badge = Badge::create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);
    $experience = Experience::factory()->create();

    $badge->experiences()->attach($experience);

    expect($badge->experiences()->pluck('experiences.id'))->toContain($experience->id);
});

test('creating a badge with a duplicate slug fails', function () {
    $user = User::factory()->create();
    Badge::create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => 'Laravel Two', 'cs' => ''])
        ->set('slug', 'laravel')
        ->call('save')
        ->assertHasErrors(['slug']);
});

test('editing a badge keeps its own slug without a unique conflict', function () {
    $user = User::factory()->create();
    $badge = Badge::create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('openEdit', $badge->id)
        ->set('name', ['en' => 'Laravel Framework', 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    expect($badge->fresh()->name)->toBe(['en' => 'Laravel Framework']);
});

test('badge color must be a hex value', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('slug', 'laravel')
        ->set('color', 'red')
        ->call('save')
        ->assertHasErrors(['color']);
});

test('badge search matches case-insensitively', function () {
    $user = User::factory()->create();
    Badge::create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);
    Badge::create(['slug' => 'vue', 'name' => ['en' => 'Vue']]);

    $component = Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('search', 'lara');

    expect($component->instance()->badges->pluck('slug')->all())->toBe(['laravel']);
});

test('badge search escapes LIKE wildcards', function () {
    $user = User::factory()->create();
    Badge::create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);
    Badge::create(['slug' => 'vue', 'name' => ['en' => 'Vue']]);

    $component = Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('search', '%');

    // Literal '%' matches nothing; without escaping it would match every row.
    expect($component->instance()->badges->pluck('slug')->all())->toBe([]);
});

test('can delete badge', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel'],
        'color' => '#60A5FA',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('confirmDelete', $badge->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Badge::count())->toBe(0);
});
