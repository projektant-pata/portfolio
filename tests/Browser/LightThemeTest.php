<?php

/**
 * The light theme is the state without `.dark` on <html>, so these tests strip
 * the class the pre-paint script sets instead of driving the cookie: the CSS
 * under test keys off the class, not off how the class got there.
 */
$goLight = "(() => { document.documentElement.classList.remove('dark'); return true; })()";

/**
 * .portfolio-page transitions its colors over --t-base (0.5s), so a computed
 * style read straight after the class flip returns a mid-fade value.
 */
$themeTransition = 1;

/** Computed color of a selector's property, as the browser's `rgb(...)` string. */
$colorOf = fn (string $selector, string $property): string => <<<JS
    (() => getComputedStyle(document.querySelector('{$selector}')).{$property})()
JS;

/** WCAG contrast ratio between a selector's text color and a background color. */
$contrastOf = fn (string $selector, string $backgroundSelector): string => <<<JS
    (() => {
        const channel = (c) => {
            const s = c / 255;
            return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
        };
        const luminance = (rgb) => {
            const [r, g, b] = rgb.match(/\\d+/g).map(Number);
            return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
        };
        const fg = luminance(getComputedStyle(document.querySelector('{$selector}')).color);
        const bg = luminance(getComputedStyle(document.querySelector('{$backgroundSelector}')).backgroundColor);
        return Math.round(((Math.max(fg, bg) + 0.05) / (Math.min(fg, bg) + 0.05)) * 100) / 100;
    })()
JS;

test('the light page sits on bone, not cream', function (string $path) use ($goLight, $colorOf, $themeTransition) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);
    $page->script($goLight);
    $page->wait($themeTransition);

    expect($page->script($colorOf('.portfolio-page', 'backgroundColor')))->toBe('rgb(250, 249, 246)');
})->with(['/', '/about-me', '/projects', '/experience']);

test('section watermarks are sand in light mode, never the gold accent', function () use ($goLight, $colorOf, $themeTransition) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/')->resize(1440, 900);
    $page->script($goLight);
    $page->wait($themeTransition);

    // Gold at watermark size reads as a highlighter slab over the content it
    // sits behind; sand keeps it as background texture.
    expect($page->script($colorOf('.portfolio-page h2', 'color')))->toBe('rgb(230, 224, 208)')
        ->and($page->script($colorOf('.portfolio-page h2', 'webkitTextStrokeWidth')))->toBe('0px');
});

test('project year labels keep the accent they inherit the watermark style from', function () use ($goLight, $colorOf, $themeTransition) {
    $this->seed(\Database\Seeders\SettingSeeder::class);
    \App\Models\Project::factory()->create();

    $page = visit('/projects')->resize(1440, 900);
    $page->script($goLight);
    $page->wait($themeTransition);

    // Years are content, not texture — sand would make the timeline unreadable.
    // The year head is `color: transparent` with a `-webkit-text-stroke`, so
    // the fill color is meaningless here; the stroke color and width are what
    // carry the visible ink.
    expect($page->script($colorOf('.proj-yhead h2', 'webkitTextStrokeColor')))->toBe('rgb(164, 91, 11)')
        ->and($page->script($colorOf('.proj-yhead h2', 'webkitTextStrokeWidth')))->toBe('1px');
});

test('light-mode body and muted text clear WCAG AA on their own surface', function () use ($goLight, $contrastOf, $themeTransition) {
    $this->seed(\Database\Seeders\SettingSeeder::class);
    \App\Models\AboutCard::factory()->create();

    $page = visit('/about-me')->resize(1440, 900);
    $page->script($goLight);
    $page->wait($themeTransition);

    expect($page->script($contrastOf('.portfolio-page h3', '.portfolio-page')))->toBeGreaterThanOrEqual(4.5)
        ->and($page->script($contrastOf('.about-me-card p', '.about-me-card')))->toBeGreaterThanOrEqual(4.5);
});
