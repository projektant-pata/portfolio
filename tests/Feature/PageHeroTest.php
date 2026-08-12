<?php

use App\Models\Setting;

test('the home page renders exactly one h1', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
});

test('the home hero renders through the shared component', function () {
    Setting::updateOrCreate(['key' => 'hero_suptitle'], ['value' => ['en' => 'Hello world', 'cs' => 'Ahoj']]);
    Setting::updateOrCreate(['key' => 'hero_title'], ['value' => ['en' => 'I am <span>someone</span>,', 'cs' => 'Jsem <span>někdo</span>,']]);
    Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => [
        'en' => ['Chess <span>player</span>', 'Coffee <span>drinker</span>'],
        'cs' => ['<span>Šachista</span>', 'Pijan <span>kávy</span>'],
    ]]);

    $this->get(route('home'))
        ->assertSee('hero-page--full', false)
        ->assertSee('Hello world')
        ->assertSee('<h1>I am <span>someone</span>,</h1>', false)
        ->assertSee('Chess <span>player</span>', false)
        ->assertSee('id="hero-rotator"', false)
        ->assertSee('data-roles=', false);
});

test('the hero omits the rotator when there are fewer than two roles', function () {
    Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => ['en' => ['Only one'], 'cs' => ['Jenom jedna']]]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('id="hero-rotator"', false);
});
