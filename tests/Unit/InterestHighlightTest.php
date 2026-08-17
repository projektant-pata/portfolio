<?php

use App\Models\Interest;

test('the highlighted phrase is wrapped in a span', function () {
    $interest = new Interest([
        'title' => ['en' => 'Chess since 2nd grade'],
        'highlight' => ['en' => '2nd grade'],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Chess since <span>2nd grade</span>');
});

test('a title is escaped before the highlight is wrapped', function () {
    $interest = new Interest([
        'title' => ['en' => '<script>alert(1)</script> chess'],
        'highlight' => ['en' => 'chess'],
    ]);

    expect($interest->highlightedTitle('en'))
        ->toBe('&lt;script&gt;alert(1)&lt;/script&gt; <span>chess</span>')
        ->not->toContain('<script>');
});

test('a highlight that is not in the title changes nothing', function () {
    $interest = new Interest([
        'title' => ['en' => 'Rock, loud'],
        'highlight' => ['en' => 'jazz'],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Rock, loud');
});

test('an empty highlight returns the escaped title', function () {
    $interest = new Interest([
        'title' => ['en' => 'Rock & roll'],
        'highlight' => [],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Rock &amp; roll');
});
