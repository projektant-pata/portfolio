<?php

use Illuminate\Support\Facades\App;

test('language toggle switches locale via POST', function () {
    session(['locale' => 'en']);

    $this->post(route('language.toggle'))
        ->assertRedirect();

    expect(session('locale'))->toBe('cs');
});

test('language toggle is not reachable via GET', function () {
    $this->get('/language/toggle')->assertStatus(405);
});

test('SetLocale ignores an invalid session locale', function () {
    session(['locale' => 'de']);

    $this->get(route('home'))->assertOk();

    expect(App::getLocale())->not->toBe('de');
});
