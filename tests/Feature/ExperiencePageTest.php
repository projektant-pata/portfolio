<?php

use App\Models\Experience;

test('experience page returns 200', function () {
    Experience::factory()->create();

    $this->get(route('experience'))->assertOk();
});

test('experience page loads the experience view', function () {
    Experience::factory()->create();

    $this->get(route('experience'))->assertViewIs('experience');
});

test('experience page passes experiences to view', function () {
    Experience::factory()->count(3)->create();

    $response = $this->get(route('experience'));

    $response->assertViewHas('experiences');
    expect($response->viewData('experiences'))->toHaveCount(3);
});

test('experience page passes badges to view', function () {
    Experience::factory()->create();

    $this->get(route('experience'))->assertViewHas('badges');
});

test('experience page shows experience title', function () {
    Experience::factory()->create([
        'title' => ['en' => 'Senior Developer'],
    ]);

    $this->get(route('experience'))->assertSee('Senior Developer');
});
