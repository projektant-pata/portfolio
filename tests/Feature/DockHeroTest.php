<?php

use App\Models\Setting;

beforeEach(function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);
});

test('the experience page opens with the dock hero, not the shared page hero', function () {
    $this->get(route('experience'))
        ->assertOk()
        ->assertSee('class="dock-hero"', false)
        ->assertDontSee('class="hero-page', false);
});

test('the dock hero renders its settings copy', function () {
    Setting::updateOrCreate(['key' => 'experience_hero_suptitle'], ['value' => ['en' => '🗓️ Where I have been', 'cs' => '🗓️ Kudy jsem prošel']]);
    Setting::updateOrCreate(['key' => 'experience_hero_title'], ['value' => ['en' => 'My <span>journey</span>,', 'cs' => 'Moje <span>cesta</span>,']]);

    $this->get(route('experience'))
        ->assertSee('🗓️ Where I have been')
        ->assertSee('<h1 class="dock-hero-title">My <span>journey</span>,</h1>', false)
        ->assertSee('id="hero-rotator"', false)
        ->assertSee('data-roles=', false);
});

test('the dock hero renders the wordmark and tags from the lang files', function () {
    $this->get(route('experience'))
        ->assertSee('aria-hidden="true">Experience<', false)
        ->assertSee('>Backend</li>', false)
        ->assertSee('>Erasmus</li>', false);

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('aria-hidden="true">Zkušenosti<', false)
        ->assertSee('>Soutěže</li>', false);
});

test('the dock hero renders the czech title under the cs locale', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('Moje <span>cesta</span>,', false)
        ->assertDontSee('My <span>journey</span>,', false);
});

test('the experience page still renders exactly one h1 and one rotator', function () {
    $html = $this->get(route('experience'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, 'id="hero-rotator"'))->toBe(1);
});

test('the dock hero renders the photo caption from the lang files', function () {
    $this->get(route('experience'))
        ->assertSee('class="dock-hero-cap"', false)
        ->assertSee('Talking to students about getting into programming.');

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('Přednáška pro studenty o cestě k programování.');
});

test('the dock hero omits the caption markup when no caption is passed', function () {
    $this->blade('<x-portfolio.dock-hero title="Title" />')
        ->assertDontSee('dock-hero-cap', false);
});

test('the dock column renders label-only while the dock asset is missing', function () {
    config()->set('portfolio.hero_images.experience_dock', '');

    $this->get(route('experience'))
        ->assertOk()
        ->assertDontSee('experience-dock', false);
});

test('the dock column renders the image once the asset is configured', function () {
    config()->set('portfolio.hero_images.experience_dock', 'images/experience-dock.webp');

    $this->get(route('experience'))
        ->assertSee('images/experience-dock.webp', false);
});

test('the section under the dock hero opts out of the scroll-reveal fade', function () {
    $this->get(route('experience'))
        ->assertSee('id="experience" class="portfolio-section portfolio-section--no-reveal"', false);
});
