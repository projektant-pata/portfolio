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

$nextSectionVisibleJs = <<<'JS'
    (() => {
        const hero = document.querySelector('.hero-page');
        const next = hero.nextElementSibling;
        return next.getBoundingClientRect().top < window.innerHeight;
    })()
JS;

test('a subpage hero stops short of the fold on desktop', function (string $path) use ($heroPeekJs, $nextSectionVisibleJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    expect($page->script($heroPeekJs))->toBeGreaterThan(0)
        ->and($page->script($heroPeekJs))->toBeLessThan(200)
        ->and($page->script($nextSectionVisibleJs))->toBeTrue();
})->with(['/about-me', '/experience', '/projects']);

test('a subpage hero stops short of the fold on mobile', function (string $path) use ($heroPeekJs, $nextSectionVisibleJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(390, 844);

    expect($page->script($heroPeekJs))->toBeGreaterThan(0)
        ->and($page->script($heroPeekJs))->toBeLessThan(200)
        ->and($page->script($nextSectionVisibleJs))->toBeTrue();
})->with(['/about-me', '/experience', '/projects']);

test('a subpage hero rotator cycles its roles', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/about-me')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});
