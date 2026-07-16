<?php

use App\Models\Experience;

test('home page returns 200', function () {
    $this->get(route('home'))->assertOk();
});

test('experience links open in a new tab with rel noopener', function () {
    Experience::factory()->create([
        'type' => 'life',
        'title' => ['en' => 'Linked Role'],
        'links' => [['url' => 'https://example.com']],
    ]);

    $this->get(route('home'))
        ->assertSee('target="_blank" rel="noopener noreferrer"', false);
});
