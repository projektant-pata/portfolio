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

test('reviews carousel advances to the next page on arrow click', function () {
    Review::query()->delete();
    foreach (range(0, 4) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/')->resize(1280, 900);

    $before = $page->script("document.querySelector('.reviews-row').style.translate");

    $page->click('.reviews-carousel-arrow-next')->wait(0.5);

    $after = $page->script("document.querySelector('.reviews-row').style.translate");

    expect($after)->not->toBe($before);
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
