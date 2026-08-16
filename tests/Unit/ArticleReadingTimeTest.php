<?php

use App\Models\Article;

// Models are constructed directly, not through the factory: the factory's
// user_id attribute would resolve a UserFactory and hit the database, and
// tests/Unit has no RefreshDatabase.
test('reading time rounds up from 200 words a minute', function () {
    $article = new Article(['content' => ['en' => str_repeat('word ', 401)]]);

    expect($article->readingTime('en'))->toBe(3);
});

test('reading time never returns zero', function () {
    $article = new Article(['content' => ['en' => 'One sentence.']]);

    expect($article->readingTime('en'))->toBe(1);
});

test('reading time counts czech words with diacritics', function () {
    $article = new Article(['content' => ['cs' => str_repeat('příliš žluťoučký kůň ', 100)]]);

    expect($article->readingTime('cs'))->toBe(2);
});
