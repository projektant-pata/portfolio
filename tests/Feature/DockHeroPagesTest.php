<?php

use App\Models\Setting;

test('the home page renders exactly one h1', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
});

test('the home hero renders through the shared dock hero', function () {
    Setting::updateOrCreate(['key' => 'hero_suptitle'], ['value' => ['en' => 'Hello world', 'cs' => 'Ahoj']]);
    Setting::updateOrCreate(['key' => 'hero_title'], ['value' => ['en' => 'I am <span>someone</span>,', 'cs' => 'Jsem <span>někdo</span>,']]);
    Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => [
        'en' => ['Chess <span>player</span>', 'Coffee <span>drinker</span>'],
        'cs' => ['<span>Šachista</span>', 'Pijan <span>kávy</span>'],
    ]]);

    $this->get(route('home'))
        ->assertSee('dock-hero--full', false)
        ->assertSee('Hello world')
        ->assertSee('<h1 class="dock-hero-title">I am <span>someone</span>,</h1>', false)
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

test('every public page opens with the dock hero', function (string $route) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route($route))
        ->assertOk()
        ->assertSee('class="dock-hero', false)
        ->assertDontSee('hero-page', false);
})->with(['home', 'about-me', 'projects', 'experience']);

test('every public page renders exactly one h1 and one rotator', function (string $route) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $html = $this->get(route($route))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, 'id="hero-rotator"'))->toBe(1);
})->with(['home', 'about-me', 'projects', 'experience']);

test('only the home hero is full height', function (string $route) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route($route))
        ->assertOk()
        ->assertDontSee('dock-hero--full', false);
})->with(['about-me', 'projects', 'experience']);

test('the about me hero renders its settings copy', function () {
    Setting::updateOrCreate(['key' => 'about_hero_suptitle'], ['value' => ['en' => '👤 whoami', 'cs' => '👤 whoami']]);
    Setting::updateOrCreate(['key' => 'about_hero_title'], ['value' => ['en' => 'A bit <span>about me</span>,', 'cs' => 'Něco <span>o mně</span>,']]);
    Setting::updateOrCreate(['key' => 'about_hero_roles'], ['value' => [
        'en' => ['Student <span>by day</span>', 'Freelancer <span>by night</span>'],
        'cs' => ['Ve dne <span>student</span>', 'V noci <span>freelancer</span>'],
    ]]);

    $this->get(route('about-me'))
        ->assertSee('👤 whoami')
        ->assertSee('<h1 class="dock-hero-title">A bit <span>about me</span>,</h1>', false)
        ->assertSee('Student <span>by day</span>', false);
});

test('the subpage heroes render the czech copy under the cs locale', function () {
    Setting::updateOrCreate(['key' => 'projects_hero_title'], ['value' => ['en' => 'Things I’ve <span>shipped</span>,', 'cs' => 'Věci, co jsem <span>postavil</span>,']]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('projects'))
        ->assertSee('Věci, co jsem <span>postavil</span>,', false);
});

test('the subpage heroes carry the rotator role list in data-roles', function () {
    Setting::updateOrCreate(['key' => 'projects_hero_roles'], ['value' => [
        'en' => ['Laravel <span>monoliths</span>', 'Spring Boot <span>APIs</span>'],
        'cs' => ['Laravel <span>monolity</span>', 'Spring Boot <span>API</span>'],
    ]]);

    // @json escapes the markup with JSON_HEX_TAG inside the attribute.
    $this->get(route('projects'))
        ->assertSee('data-roles=', false)
        ->assertSee('Spring Boot \u003Cspan\u003EAPIs\u003C\/span\u003E', false);
});

test('each page renders its own wordmark and tags in both locales', function (string $route, string $wordmarkEn, string $tagEn, string $wordmarkCs, string $tagCs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route($route))
        ->assertSee('aria-hidden="true">'.$wordmarkEn.'<', false)
        ->assertSee('>'.$tagEn.'</li>', false);

    $this->withSession(['locale' => 'cs'])
        ->get(route($route))
        ->assertSee('aria-hidden="true">'.$wordmarkCs.'<', false)
        ->assertSee('>'.$tagCs.'</li>', false);
})->with([
    ['home', 'Portfolio', 'Laravel', 'Portfolio', 'Laravel'],
    ['about-me', 'About me', 'Chess', 'O mně', 'Šachy'],
    ['projects', 'Projects', 'Hackathons', 'Projekty', 'Hackathony'],
    ['experience', 'Experience', 'Backend', 'Zkušenosti', 'Soutěže'],
]);

test('the home hero renders its photo caption', function () {
    $this->get(route('home'))
        ->assertSee('class="dock-hero-cap"', false)
        ->assertSee('Full-stack developer and freelancer from Pardubice, Czechia.');

    $this->withSession(['locale' => 'cs'])
        ->get(route('home'))
        ->assertSee('Full-stack vývojář a freelancer z Pardubic.');
});

test('the subpage heroes carry their own photo crop', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route('projects'))
        ->assertSee('--dock-hero-photo-pos: 50% 30%', false);
});

test('the sections that peek under a subpage hero opt out of the scroll-reveal fade', function (string $route, string $id) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route($route))
        ->assertSee('id="'.$id.'" class="portfolio-section portfolio-section--no-reveal"', false);
})->with([
    ['about-me', 'about-me'],
    ['projects', 'projects'],
]);

test('the section under the full-height home hero still fades in on scroll', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route('home'))
        ->assertSee('id="stats" class="portfolio-section"', false);
});
