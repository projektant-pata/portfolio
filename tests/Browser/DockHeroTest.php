<?php

/**
 * Distance in px between the bottom of the hero and the bottom of the
 * viewport. Positive = the next section peeks in; <= 0 = the hero fills
 * (or overflows) the first screen.
 */
$heroPeekJs = <<<'JS'
    (() => {
        const hero = document.querySelector('.hero-page');
        return Math.round(window.innerHeight - hero.getBoundingClientRect().bottom);
    })()
JS;

test('the home hero fills the first screen', function () use ($heroPeekJs) {
    $page = visit('/')->resize(1440, 900);

    expect($page->script($heroPeekJs))->toBeLessThanOrEqual(1);
});

test('the home hero keeps its side-by-side desktop layout', function () {
    $page = visit('/')->resize(1440, 900);

    $sideBySide = <<<'JS'
        (() => {
            const text = document.querySelector('.hero-page-text').getBoundingClientRect();
            const image = document.querySelector('.hero-page-image').getBoundingClientRect();
            return image.left >= text.right - 1;
        })()
    JS;

    expect($page->script($sideBySide))->toBeTrue();
});

test('the home hero rotator cycles its roles', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});

/**
 * Height of the next section's sliver above the fold. The subpage peek is
 * bought with a negative margin now, so it is measured from that section
 * rather than from the hero's own (full-screen) box.
 */
$nextSectionPeekJs = <<<'JS'
    (() => {
        const next = document.querySelector('.hero-page').nextElementSibling;
        return Math.round(window.innerHeight - next.getBoundingClientRect().top);
    })()
JS;

/** Distance in px between the hero text's centre and the viewport's centre. */
$heroTextOffsetJs = <<<'JS'
    (() => {
        const text = document.querySelector('.hero-page-text').getBoundingClientRect();
        return Math.round(Math.abs((text.top + text.bottom) / 2 - window.innerHeight / 2));
    })()
JS;

test('a subpage hero fills the first screen and still lets the next section peek in', function (string $path) use ($heroPeekJs, $nextSectionPeekJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    expect($page->script($heroPeekJs))->toBeLessThanOrEqual(1)
        ->and($page->script($nextSectionPeekJs))->toBeGreaterThan(0)
        ->and($page->script($nextSectionPeekJs))->toBeLessThan(200);
})->with(['/about-me', '/projects']);

test('a subpage hero fills the first screen and still lets the next section peek in on mobile', function (string $path) use ($heroPeekJs, $nextSectionPeekJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(390, 844);


    expect($page->script($heroPeekJs))->toBeLessThanOrEqual(1)
        ->and($page->script($nextSectionPeekJs))->toBeGreaterThan(0)
        ->and($page->script($nextSectionPeekJs))->toBeLessThan(200);
})->with(['/about-me', '/projects']);

test('every hero centres its text on the same line as the home hero', function (string $path) use ($heroTextOffsetJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    expect($page->script($heroTextOffsetJs))->toBeLessThanOrEqual(2);
})->with(['/', '/about-me', '/projects']);

test('the section after a subpage hero fades in on scroll like every other section', function (string $path) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    // Exact opacity is timing-dependent (the entrance transition may already
    // be mid-flight by the time this runs), so assert the reveal transition
    // is wired up at all rather than pin an instantaneous value — this is
    // what used to be force-disabled for the section right after the peek.
    $transitionDuration = <<<'JS'
        (() => {
            const next = document.querySelector('.hero-page').nextElementSibling;
            return getComputedStyle(next).transitionDuration;
        })()
    JS;

    expect($page->script($transitionDuration))->not->toBe('0s');
})->with(['/about-me', '/projects']);

test('a subpage hero rotator cycles its roles', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/about-me')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});
