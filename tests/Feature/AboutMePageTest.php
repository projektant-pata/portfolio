<?php

use Database\Seeders\SettingSeeder;

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('about me page contains about me section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-content', false);
});

test('about me page contains stats section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-stats-cards', false);
});

test('the about me intro is introduced by a section head', function () {
    $this->seed(SettingSeeder::class);

    $this->get(route('about-me'))
        ->assertSee('<p class="sechead-eyebrow">Who I am</p>', false)
        ->assertSee('Longer version of the hero', false);
});

test('the about me intro head carries no ghost under the hero', function () {
    $this->seed(SettingSeeder::class);

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->toContain('sechead--noghost')
        ->and($html)->not->toContain('<div class="sechead-ghost" aria-hidden="true">About me</div>');
});
