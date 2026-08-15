<?php

/**
 * Horizontal overflow of the document, in px. The ghost wordmark is
 * `white-space: nowrap`, so a long word on a narrow viewport is a real
 * sideways-scroll risk, not a theoretical one.
 */
$overflowJs = <<<'JS'
    (() => document.documentElement.scrollWidth - document.documentElement.clientWidth)()
JS;

test('the home page never scrolls sideways at any width', function (int $width) use ($overflowJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/')->resize($width, 900);

    expect($page->script($overflowJs))->toBeLessThanOrEqual(0);
})->with([360, 760, 1100, 1440]);
