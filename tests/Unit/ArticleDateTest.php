<?php

use App\Support\ArticleDate;
use Carbon\CarbonImmutable;

$date = CarbonImmutable::parse('2026-03-18');

test('the header format spells the month out per locale', function () use ($date) {
    expect(ArticleDate::header($date, 'en'))->toBe('18 March 2026')
        ->and(ArticleDate::header($date, 'cs'))->toBe('18. března 2026');
});

test('the rail day carries a trailing dot in czech only', function () use ($date) {
    expect(ArticleDate::railDay($date, 'en'))->toBe('18')
        ->and(ArticleDate::railDay($date, 'cs'))->toBe('18.');
});

test('the rail month abbreviates in english and goes numeric in czech', function () use ($date) {
    expect(ArticleDate::railMonth($date, 'en'))->toBe('Mar 2026')
        ->and(ArticleDate::railMonth($date, 'cs'))->toBe('3. 2026');
});

test('the czech numeric month never keeps a leading zero', function () {
    expect(ArticleDate::railMonth(CarbonImmutable::parse('2025-06-30'), 'cs'))->toBe('6. 2025');
});

test('the month-year format spells the month out for the end-of-list line', function () {
    $june = CarbonImmutable::parse('2025-06-30');

    expect(ArticleDate::monthYear($june, 'en'))->toBe('June 2025')
        ->and(ArticleDate::monthYear($june, 'cs'))->toBe('června 2025');
});

test('the datetime attribute is always iso', function () use ($date) {
    expect(ArticleDate::iso($date))->toBe('2026-03-18');
});
