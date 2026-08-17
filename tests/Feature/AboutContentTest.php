<?php

use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Stat;

test('a path node stores both locales and the now flag', function () {
    $node = PathNode::factory()->create([
        'year' => ['en' => 'Now', 'cs' => 'Teď'],
        'title' => ['en' => 'Freelance', 'cs' => 'Freelance'],
        'body' => ['en' => 'Paid client work.', 'cs' => 'Placená práce pro klienty.'],
        'is_now' => true,
        'sort_order' => 4,
    ]);

    expect($node->fresh()->year)->toEqual(['en' => 'Now', 'cs' => 'Teď'])
        ->and($node->fresh()->is_now)->toBeTrue()
        ->and($node->getTranslation('title', 'cs'))->toBe('Freelance')
        ->and($node->getTranslation('body', 'fr'))->toBe('Paid client work.');
});

test('an interest stores its image path and translatable meta', function () {
    $interest = Interest::factory()->create([
        'meta' => ['en' => '1684 elo', 'cs' => '1684 elo'],
        'image_path' => 'images/about/chess.webp',
    ]);

    expect($interest->fresh()->meta)->toEqual(['en' => '1684 elo', 'cs' => '1684 elo'])
        ->and($interest->fresh()->image_path)->toBe('images/about/chess.webp');
});

test('a stat defaults to the normal tier and accepts hero with a note', function () {
    $plain = Stat::factory()->create();
    $hero = Stat::factory()->hero()->create();

    expect($plain->fresh()->tier)->toBe('normal')
        ->and($plain->fresh()->note)->toBeNull()
        ->and($hero->fresh()->tier)->toBe('hero')
        ->and($hero->getTranslation('note', 'en'))->not->toBe('');
});

test('stats group by tier without losing sort order', function () {
    Stat::factory()->create(['tier' => 'soft', 'sort_order' => 1]);
    Stat::factory()->hero()->create(['sort_order' => 0]);

    $tiers = Stat::orderBy('sort_order')->get()->pluck('tier')->all();

    expect($tiers)->toBe(['hero', 'soft'])
        ->and(Stat::TIERS)->toBe(['hero', 'normal', 'soft']);
});

test('an experience defaults to hidden on the about page', function () {
    expect(Experience::factory()->create()->fresh()->show_on_about)->toBeFalse()
        ->and(Experience::factory()->create(['show_on_about' => true])->fresh()->show_on_about)->toBeTrue();
});
