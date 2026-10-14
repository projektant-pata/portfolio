<?php

use App\Models\Badge;
use App\Models\Experience;

test('both scopes render into one track, with life hidden behind the toggle', function () {
    Experience::factory()->create(['type' => 'work', 'title' => ['en' => 'Work Entry'], 'year' => ['en' => '2024']]);
    Experience::factory()->create(['type' => 'life', 'title' => ['en' => 'Life Entry'], 'year' => ['en' => '2023']]);

    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('Work Entry')
        ->and($html)->toContain('Life Entry')
        ->and($html)->toContain('<article class="wl-e" data-scope="work">')
        ->and($html)->toContain('<article class="wl-e" data-scope="life" hidden>');
});

test('an entry renders its year, subtitle as the result chip, and its badges', function () {
    $experience = Experience::factory()->create([
        'type' => 'work',
        'title' => ['en' => 'Hackathon AstroPi'],
        'subtitle' => ['en' => '1st team place'],
        'year' => ['en' => '2024'],
    ]);
    $experience->badges()->attach(Badge::factory()->create(['name' => ['en' => 'Python'], 'color' => '#2DD4BF']));

    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('<div class="wl-when">2024</div>')
        ->and($html)->toContain('<span class="wl-res">1st team place</span>')
        ->and($html)->toContain('style="--bc: #2DD4BF"')
        ->and($html)->toContain('Python');
});

test('the entry teaser strips markup and stops at 180 characters', function () {
    Experience::factory()->create([
        'type' => 'work',
        'title' => ['en' => 'Long Entry'],
        'content' => ['en' => '**Bold** '.str_repeat('word ', 80)],
    ]);

    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('Bold word')
        ->and($html)->not->toContain('<strong>Bold</strong>')
        ->and($html)->toContain('...');
});

test('the footer count and span are derived from the entries themselves', function () {
    Experience::factory()->create(['type' => 'work', 'year' => ['en' => '2021']]);
    Experience::factory()->create(['type' => 'work', 'year' => ['en' => '2024']]);
    Experience::factory()->create(['type' => 'life', 'year' => ['en' => '2022 - now']]);

    $html = $this->get(route('home'))->getContent();

    // The visible count is the work one; the life string rides along on the
    // toggle button, which is what the listener swaps in.
    expect($html)->toContain('<b>2</b> work entries · 2021—2024')
        ->and($html)->toContain('data-count="&lt;b&gt;1&lt;/b&gt; life entries · 2022—now"');
});

test('an open-ended year makes the span run to now in the active locale', function () {
    $entries = collect([
        Experience::factory()->make(['year' => ['en' => '2022 - now', 'cs' => '2022 - nyní']]),
        Experience::factory()->make(['year' => ['en' => '2021', 'cs' => '2021']]),
    ]);

    expect(Experience::timelineSpan($entries, 'en'))->toBe('2021—now');

    app()->setLocale('cs');

    expect(Experience::timelineSpan($entries, 'cs'))->toBe('2021—teď');
});

test('a single year, and no year at all, both degrade quietly', function () {
    $single = collect([Experience::factory()->make(['year' => ['en' => '2024']])]);
    $none = collect([Experience::factory()->make(['year' => ['en' => 'someday']])]);

    expect(Experience::timelineSpan($single, 'en'))->toBe('2024')
        ->and(Experience::timelineSpan($none, 'en'))->toBe('');
});

test('the work plate carries the photo and life falls back to the striped slot', function () {
    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('images/home/work-life-work.png')
        ->and($html)->toContain('<figure class="wl-shot wl-shot--empty" data-scope="life" hidden>')
        ->and($html)->toContain('life photo · 1200×1500');
});

test('an empty scope says so instead of leaving the track blank', function () {
    Experience::query()->delete();
    Experience::factory()->create(['type' => 'work', 'title' => ['en' => 'Only Work']]);

    $html = $this->get(route('home'))->getContent();

    expect($html)->toContain('<p class="wl-empty" data-scope="life" hidden>')
        ->and($html)->toContain('Nothing in this list yet.');
});
