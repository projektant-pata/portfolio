<?php

use App\Support\ArticleMarkdown;

test('a plain paragraph renders untouched', function () {
    expect(ArticleMarkdown::render('Hello **world**.'))
        ->toContain('<p>Hello <strong>world</strong>.</p>');
});

test('tables are wrapped in a scroll container', function () {
    $html = ArticleMarkdown::render("| a | b |\n| --- | --- |\n| 1 | 2 |");

    expect($html)->toContain('<div class="blog-table">')
        ->and($html)->toContain('<table>')
        ->and($html)->toContain('</table></div>');
});

test('fenced code is wrapped with a language bar', function () {
    $html = ArticleMarkdown::render("```php\n\$x = 1;\n```");

    expect($html)->toContain('<div class="blog-code">')
        ->and($html)->toContain('<div class="blog-code-bar">')
        ->and($html)->toContain('php')
        ->and($html)->toContain('<pre><code');
});

test('fenced code without an info string renders without a bar', function () {
    $html = ArticleMarkdown::render("```\nplain\n```");

    expect($html)->toContain('<div class="blog-code">')
        ->and($html)->not->toContain('blog-code-bar');
});

test('a lone image becomes a figure with its alt as the caption', function () {
    $html = ArticleMarkdown::render('![The sensor, zip-tied to the rack](https://cdn.test/a.jpg)');

    expect($html)->toContain('<figure>')
        ->and($html)->toContain('<figcaption>The sensor, zip-tied to the rack</figcaption>')
        ->and($html)->not->toContain('<p><img');
});

test('an image with no alt text gets no caption', function () {
    $html = ArticleMarkdown::render('![](https://cdn.test/a.jpg)');

    expect($html)->toContain('<figure>')
        ->and($html)->not->toContain('<figcaption>');
});

test('an image inside a sentence stays inline', function () {
    $html = ArticleMarkdown::render('Look ![alt](https://cdn.test/a.jpg) here.');

    expect($html)->toContain('<p>')->and($html)->not->toContain('<figure>');
});

test('external links get rel=noopener and internal ones do not', function () {
    config()->set('app.url', 'https://projektant-pata.cz');

    $html = ArticleMarkdown::render('[out](https://example.com) [in](https://projektant-pata.cz/blog)');

    expect($html)->toContain('rel="noopener"')
        ->and(substr_count($html, 'rel="noopener"'))->toBe(1);
});
