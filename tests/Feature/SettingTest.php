<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;

it('reflects an updated value immediately when app.debug is true', function () {
    config(['app.debug' => true]);

    $setting = Setting::factory()->create(['key' => 'reviews_title', 'value' => ['en' => 'Old']]);

    expect(Setting::allKeyed()['reviews_title'])->toEqual(['en' => 'Old']);

    $setting->update(['value' => ['en' => 'New']]);

    expect(Setting::allKeyed()['reviews_title'])->toEqual(['en' => 'New']);
});

test('the seeder installs the subpage hero copy and drops the orphaned keys', function () {
    Setting::updateOrCreate(['key' => 'about_hero_subtitle'], ['value' => ['en' => 'stale']]);
    Setting::updateOrCreate(['key' => 'about_hero_meta'], ['value' => ['en' => 'stale']]);

    $this->seed(SettingSeeder::class);

    expect(Setting::text('about_hero_title', 'cs'))->toBe('Něco <span>o mně</span>,')
        ->and(Setting::text('experience_hero_suptitle', 'en'))->toBe('🗓️ Where I’ve been')
        ->and(Setting::list('projects_hero_roles', 'cs'))->toHaveCount(5)
        ->and(Setting::whereIn('key', ['about_hero_subtitle', 'about_hero_meta'])->count())->toBe(0);
});

test('the about fact strip reads back as a list of label/value maps', function () {
    $this->seed(SettingSeeder::class);

    $facts = Setting::list('about_facts', 'en');

    expect($facts)->toBeArray()->not->toBeEmpty();

    foreach ($facts as $fact) {
        expect($fact)->toHaveKeys(['label', 'value']);
    }
});

test('the about intro body is a two-paragraph list in both locales', function () {
    $this->seed(SettingSeeder::class);

    expect(Setting::list('about_intro_body', 'en'))->toHaveCount(2)
        ->and(Setting::list('about_intro_body', 'cs'))->toHaveCount(2)
        ->and(Setting::text('about_available', 'cs'))->not->toBe('');
});
