<?php

use App\Models\Project;
use App\Models\Review;

test('public pages render without javascript or console errors', function () {
    $pages = visit(['/', '/about-me', '/projects', '/experience']);

    $pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
});

test('the home page teases projects straight from the database', function () {
    Project::factory()->create(['sort_order' => 0, 'header' => ['en' => 'Featured Project']]);

    visit('/')->assertSee('Featured Project');
});

test('the home page compact project list draws no border above its first row', function () {
    Project::factory()->create(['sort_order' => 0, 'header' => ['en' => 'First Featured']]);
    Project::factory()->create(['sort_order' => 1, 'header' => ['en' => 'Second Featured']]);

    $page = visit('/')->resize(1440, 900);

    $borderTopWidth = <<<'JS'
        (i) => getComputedStyle(document.querySelectorAll('.proj-list--compact .proj-item')[i]).borderTopWidth
    JS;

    expect($page->script("({$borderTopWidth})(0)"))->toBe('0px')
        ->and($page->script("({$borderTopWidth})(1)"))->not->toBe('0px');
});

test('the projects page never shows both empty states when there are no projects at all', function () {
    $page = visit('/projects');

    expect($page->script("document.querySelector('.proj-none') !== null"))->toBeTrue()
        ->and($page->script("document.getElementById('proj-empty').hidden"))->toBeTrue();
});

test('reviews carousel auto-advances and is not user-scrollable', function () {
    Review::query()->delete();
    foreach (range(0, 4) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/')->resize(1280, 900);

    expect($page->script("getComputedStyle(document.querySelector('.reviews-carousel-viewport')).overflowX"))
        ->toBe('hidden');

    // Autoplay moves it off page 0 on its own, then wraps back around.
    $page->wait(6);

    expect($page->script("document.querySelector('.reviews-carousel-viewport').scrollLeft"))
        ->toBeGreaterThan(0);
});

test('every reviews carousel step shows only whole cards', function () {
    Review::query()->delete();
    // 7 is not a multiple of the 3 cards per view — the case that used to cut
    // the last card when the carousel paged by viewport width.
    foreach (range(0, 6) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/')->resize(1280, 900);

    $noPartialCard = <<<'JS'
        (() => {
            const box = document.querySelector('.reviews-carousel-viewport').getBoundingClientRect();
            return [...document.querySelectorAll('.reviews-row-card')].every((card) => {
                const r = card.getBoundingClientRect();
                const whollyInside = r.left >= box.left - 1 && r.right <= box.right + 1;
                const whollyOutside = r.right <= box.left + 1 || r.left >= box.right - 1;
                return whollyInside || whollyOutside;
            });
        })()
    JS;

    $stepCount = $page->script("document.querySelectorAll('.reviews-carousel-dot').length");

    expect($stepCount)->toBeGreaterThan(1);

    foreach (range(0, $stepCount - 1) as $step) {
        $page->script("document.querySelectorAll('.reviews-carousel-dot')[{$step}].click()");
        $page->wait(1);

        expect($page->script($noPartialCard))->toBeTrue();
    }
});

test('reviews carousel shows whole cards with a gap', function () {
    Review::query()->delete();
    foreach (range(0, 4) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/')->resize(1280, 900);

    // No arrows anymore.
    expect($page->script("document.querySelectorAll('.reviews-carousel-arrow').length"))->toBe(0);

    // Three whole cards fit the viewport, separated by the row gap.
    $metrics = $page->script(<<<'JS'
        (() => {
            const viewport = document.querySelector('.reviews-carousel-viewport');
            const cards = [...document.querySelectorAll('.reviews-row-card')];
            const gap = parseFloat(getComputedStyle(document.querySelector('.reviews-row')).columnGap);
            return {
                overflowing: viewport.scrollWidth > viewport.clientWidth + 1,
                thirdCardFits: Math.round(cards[2].getBoundingClientRect().right)
                    <= Math.round(viewport.getBoundingClientRect().right),
                gap,
                between: Math.round(cards[1].getBoundingClientRect().left - cards[0].getBoundingClientRect().right),
            };
        })()
    JS);

    expect($metrics['overflowing'])->toBeTrue()
        ->and($metrics['thirdCardFits'])->toBeTrue()
        ->and($metrics['between'])->toBe((int) round($metrics['gap']));
});

test('renders the reviews carousel without JS errors', function () {
    Review::query()->delete();
    Review::factory()->create(['name' => 'Ada Lovelace']);

    visit('/')->assertNoJavascriptErrors()->assertSee('Ada Lovelace');
});

test('footer renders as a card on desktop and a full-bleed band on mobile', function () {
    $page = visit('/')->resize(1280, 900);

    $desktopMarginLeft = $page->script(
        "parseFloat(getComputedStyle(document.querySelector('.portfolio-footer')).marginLeft)"
    );

    $page->resize(500, 900);

    $mobileMarginLeft = $page->script(
        "parseFloat(getComputedStyle(document.querySelector('.portfolio-footer')).marginLeft)"
    );

    expect($desktopMarginLeft)->toBeGreaterThan(0)
        ->and($mobileMarginLeft)->toEqual(0);
});
