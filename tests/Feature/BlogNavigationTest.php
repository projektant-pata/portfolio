<?php

use Database\Seeders\SettingSeeder;

test('every public page links to the blog from the nav and the footer', function (string $route) {
    $this->seed(SettingSeeder::class);

    $html = $this->get(route($route))->assertOk()->getContent();

    expect(substr_count($html, 'href="'.route('blog').'"'))->toBeGreaterThanOrEqual(2);
})->with(['home', 'about-me', 'experience', 'projects']);

test('the retired spse link is gone', function () {
    $this->seed(SettingSeeder::class);

    $this->get(route('home'))->assertDontSee('hyvlri22.llmp.spse-net.cz');
});
