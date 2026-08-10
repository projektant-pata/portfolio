<?php

use App\Models\Setting;

it('reflects an updated value immediately when app.debug is true', function () {
    config(['app.debug' => true]);

    $setting = Setting::factory()->create(['key' => 'reviews_title', 'value' => ['en' => 'Old']]);

    expect(Setting::allKeyed()['reviews_title'])->toEqual(['en' => 'Old']);

    $setting->update(['value' => ['en' => 'New']]);

    expect(Setting::allKeyed()['reviews_title'])->toEqual(['en' => 'New']);
});
