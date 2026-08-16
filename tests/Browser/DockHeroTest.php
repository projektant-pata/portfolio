<?php

use Database\Seeders\SettingSeeder;

/**
 * Height of the next section's sliver above the fold. This — not the hero's
 * own box — is what "fills the first screen" means here: the hero is sized
 * symmetrically about the viewport centre (the same offset above and below),
 * so its bottom edge sits well short of the fold by design and the peek is
 * bought back with its bottom margin.
 */
$nextSectionPeekJs = <<<'JS'
    (() => {
        const next = document.querySelector('.dock-hero').nextElementSibling;
        return Math.round(window.innerHeight - next.getBoundingClientRect().top);
    })()
JS;

/** Distance in px between the hero band's centre and the viewport's centre. */
$heroBandOffsetJs = <<<'JS'
    (() => {
        const band = document.querySelector('.dock-hero-inner').getBoundingClientRect();
        return Math.round(Math.abs((band.top + band.bottom) / 2 - window.innerHeight / 2));
    })()
JS;

test('the home hero fills the first screen with nothing of the next section showing', function () use ($nextSectionPeekJs) {
    $this->seed(SettingSeeder::class);

    $page = visit('/')->resize(1440, 900);

    expect($page->script($nextSectionPeekJs))->toBeLessThanOrEqual(0);
});

test('the hero keeps its side-by-side desktop layout', function (string $path) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    $sideBySide = <<<'JS'
        (() => {
            const copy = document.querySelector('.dock-hero-copy').getBoundingClientRect();
            const photo = document.querySelector('.dock-hero-photo').getBoundingClientRect();
            return photo.left >= copy.right - 1;
        })()
    JS;

    expect($page->script($sideBySide))->toBeTrue();
})->with(['/', '/about-me', '/projects', '/experience', '/blog']);

/**
 * Samples the rotator's text every 100ms rather than comparing two snapshots:
 * a single "before vs 3s later" read is a coin flip, because the typewriter
 * pauses for 1.6s on a fully typed role and can be sitting on the same string
 * it started from. Distinct samples prove the animation is running whatever
 * phase it is caught in.
 */
$startSamplingJs = <<<'JS'
    (() => {
        window.__rotatorSamples = [];
        window.setInterval(() => {
            window.__rotatorSamples.push(document.getElementById('hero-rotator').textContent);
        }, 100);
        return true;
    })()
JS;

$distinctSamplesJs = 'new Set(window.__rotatorSamples).size';

test('the hero rotator cycles its roles', function (string $path) use ($startSamplingJs, $distinctSamplesJs) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);
    $page->script($startSamplingJs);
    $page->wait(3);

    expect($page->script($distinctSamplesJs))->toBeGreaterThan(1);
})->with(['/', '/about-me', '/projects', '/experience', '/blog']);

test('a subpage hero fills the first screen and still lets the next section peek in', function (string $path) use ($nextSectionPeekJs) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    expect($page->script($nextSectionPeekJs))->toBeGreaterThan(0)
        ->and($page->script($nextSectionPeekJs))->toBeLessThan(200);
})->with(['/about-me', '/projects', '/experience', '/blog']);

test('every hero centres its band on the same line', function (string $path) use ($heroBandOffsetJs) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    expect($page->script($heroBandOffsetJs))->toBeLessThanOrEqual(2);
})->with(['/', '/about-me', '/projects', '/experience', '/blog']);

test('the hero columns stack on mobile', function (string $path) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(390, 844);

    $stacked = <<<'JS'
        (() => {
            const copy = document.querySelector('.dock-hero-copy').getBoundingClientRect();
            const photo = document.querySelector('.dock-hero-photo').getBoundingClientRect();
            return photo.top >= copy.bottom - 1;
        })()
    JS;

    expect($page->script($stacked))->toBeTrue();
})->with(['/', '/about-me', '/projects', '/experience', '/blog']);

test('the section that peeks under a subpage hero does not wait for the scroll observer', function (string $path) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    // .portfolio-section--no-reveal never gets the opacity: 0 starting state,
    // so the peeking section is painted before the observer ever fires.
    $opacity = <<<'JS'
        (() => {
            const next = document.querySelector('.dock-hero').nextElementSibling;
            return getComputedStyle(next).opacity;
        })()
    JS;

    expect($page->script($opacity))->toBe('1');
})->with(['/about-me', '/projects', '/experience', '/blog']);

test('the section after the home hero fades in on scroll like every other section', function () {
    $this->seed(SettingSeeder::class);

    // Exact opacity is timing-dependent (the reveal may already be mid-flight
    // by the time this runs), so assert the transition is wired up at all
    // rather than pin an instantaneous value.
    $transitionDuration = <<<'JS'
        (() => {
            const next = document.querySelector('.dock-hero').nextElementSibling;
            return getComputedStyle(next).transitionDuration;
        })()
    JS;

    $page = visit('/')->resize(1440, 900);

    expect($page->script($transitionDuration))->not->toBe('0s');
});

test('the hero copy is revealed by the entrance sequence', function (string $path) {
    $this->seed(SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);
    $page->wait(1);

    $titleOpacity = <<<'JS'
        (() => getComputedStyle(document.querySelector('.dock-hero-title')).opacity)()
    JS;

    expect($page->script($titleOpacity))->toBe('1');
})->with(['/', '/about-me', '/projects', '/experience', '/blog']);
