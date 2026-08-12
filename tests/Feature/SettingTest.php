<?php

use App\Models\Setting;

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

    $this->seed(\Database\Seeders\SettingSeeder::class);

    expect(Setting::text('about_hero_title', 'cs'))->toBe('Něco <span>o mně</span>,')
        ->and(Setting::text('experience_hero_suptitle', 'en'))->toBe('🗓️ Where I’ve been')
        ->and(Setting::list('projects_hero_roles', 'cs'))->toHaveCount(4)
        ->and(Setting::whereIn('key', ['about_hero_subtitle', 'about_hero_meta'])->count())->toBe(0);
});
